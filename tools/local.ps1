param(
    [ValidateSet('start', 'stop', 'status', 'setup')][string]$Action = 'start',
    [string]$Dump
)
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
$localRoot = Join-Path $projectRoot '.local'
$apache = Join-Path $localRoot 'runtime/apache/Apache24'
$php = Join-Path $localRoot 'runtime/php'
$maria = Join-Path $localRoot 'runtime/mariadb-11.4.13-winx64'
$utf8 = New-Object System.Text.UTF8Encoding($false)
function Write-LocalFile([string]$Path, [string]$Content) {
    [IO.File]::WriteAllText($Path, $Content, $utf8)
}
function Wait-LocalPort([int]$Port) {
    for ($i = 0; $i -lt 50; $i++) {
        $tcp = New-Object Net.Sockets.TcpClient
        try { $tcp.Connect('127.0.0.1', $Port); return } catch { Start-Sleep -Milliseconds 200 } finally { $tcp.Dispose() }
    }
    throw "Port $Port did not start. Check .local/logs."
}
function Get-LocalProcess([string]$Name) {
    Get-CimInstance Win32_Process -Filter "Name='$Name'" | Where-Object {
        $_.ExecutablePath -and $_.ExecutablePath.StartsWith($localRoot + '\', [StringComparison]::OrdinalIgnoreCase)
    }
}
function Assert-FreePort([int]$Port) {
    $tcp = New-Object Net.Sockets.TcpClient
    try { $tcp.Connect('127.0.0.1', $Port); throw "Port $Port is already used by another server." }
    catch [Net.Sockets.SocketException] { }
    finally { $tcp.Dispose() }
}
if ($Action -eq 'stop') {
    if (Get-LocalProcess 'httpd.exe') {
        # Console-mode Apache is deliberately not installed as a Windows service.
        Get-LocalProcess 'httpd.exe' | ForEach-Object { Stop-Process -Id $_.ProcessId -ErrorAction SilentlyContinue }
    }
    if (Get-LocalProcess 'mariadbd.exe') {
        & "$maria/bin/mariadb-admin.exe" "--defaults-extra-file=$localRoot/conf/client.ini" shutdown
        if ($LASTEXITCODE -ne 0) { throw 'Database shutdown failed.' }
    }
    Get-LocalProcess 'mailpit.exe' | ForEach-Object { Stop-Process -Id $_.ProcessId }
    Write-Output 'Local site stopped. Database contents were preserved.'
    exit
}
if ($Action -eq 'status') {
    foreach ($name in 'httpd.exe', 'mariadbd.exe', 'mailpit.exe') {
        $running = @(Get-LocalProcess $name).Count -gt 0
        Write-Output "$name running: $running"
    }
    exit
}
foreach ($folder in 'downloads', 'runtime', 'conf', 'logs', 'sessions') {
    New-Item -ItemType Directory -Force -Path "$localRoot/$folder" | Out-Null
}
if ($Action -eq 'setup') {
    if (!$Dump -or !(Test-Path -LiteralPath $Dump -PathType Leaf)) { throw 'Pass -Dump with the path to your SQL export.' }
    $packages = @(
        @{ File='php.zip'; Url='https://downloads.php.net/~windows/releases/php-8.4.26-Win32-vs17-x64.zip'; Hash='6e56f0e932e92bfdce208d3a7e6068f6e2f7a19fc2922a5f856fb085f61673f3'; Target=$php; Check="$php/php.exe" },
        @{ File='apache.zip'; Url='https://www.apachelounge.com/download/VS18/binaries/httpd-2.4.69-261002-Win64-VS18.zip'; Hash='ca272e0b75abad78b1e4029a0753b20ff2232d2d857e8eebeea5d469c5776977'; Target="$localRoot/runtime/apache"; Check="$apache/bin/httpd.exe" },
        @{ File='mariadb.zip'; Url='https://dlm.mariadb.com/4801422/MariaDB/mariadb-11.4.13/winx64-packages/mariadb-11.4.13-winx64.zip'; Hash='d62986d433eeebfde218560b276103831604a61e929e87f1a17f5aebd80257e2'; Target="$localRoot/runtime"; Check="$maria/bin/mariadbd.exe" },
        @{ File='mailpit.zip'; Url='https://github.com/axllent/mailpit/releases/download/v1.31.6/mailpit-windows-amd64.zip'; Hash='5d5ad094bfb3bac9004f9d1105d2335fea9f8952c09951df73ae0a280e34d3b1'; Target="$localRoot/runtime/mailpit"; Check="$localRoot/runtime/mailpit/mailpit.exe" }
    )
    foreach ($package in $packages) {
        if (Test-Path -LiteralPath $package.Check) { continue }
        $zip = "$localRoot/downloads/$($package.File)"
        if (!(Test-Path -LiteralPath $zip)) {
            & curl.exe -fLsS --proto '=https' --proto-redir '=https' $package.Url -o $zip
            if ($LASTEXITCODE -ne 0) { throw "Download failed: $($package.File)" }
        }
        if ((Get-FileHash -LiteralPath $zip -Algorithm SHA256).Hash.ToLower() -ne $package.Hash) { throw "Checksum mismatch: $zip" }
        Expand-Archive -LiteralPath $zip -DestinationPath $package.Target -Force
    }
}
if (!(Test-Path -LiteralPath "$php/php8apache2_4.dll") -or !(Test-Path -LiteralPath "$maria/bin/mariadbd.exe")) {
    throw 'Run tools/local.ps1 -Action setup -Dump path/to/export.sql first.'
}
$secretFile = "$localRoot/conf/secrets.json"
if (!(Test-Path -LiteralPath $secretFile)) {
    if ($Action -ne 'setup') { throw 'Local environment has not been initialized.' }
    $secrets = @{}
    foreach ($key in 'rootPassword', 'dbPassword', 'jwtSecret', 'loginPassword') { $secrets[$key] = [guid]::NewGuid().ToString('N') + [guid]::NewGuid().ToString('N') }
    Write-LocalFile $secretFile ($secrets | ConvertTo-Json)
}
$secrets = Get-Content -LiteralPath $secretFile -Raw | ConvertFrom-Json
$env:PATH = "$php;$apache/bin;$env:PATH"
$r = $localRoot.Replace('\', '/')
$p = $php.Replace('\', '/')
$a = $apache.Replace('\', '/')
$web = (Join-Path $projectRoot 'public_html').Replace('\', '/')
if (!(Test-Path -LiteralPath "$localRoot/conf/mail-cert.pem")) {
    Write-LocalFile "$localRoot/conf/openssl.cnf" "[req]`ndistinguished_name=dn`nx509_extensions=v3`n[dn]`nCN=localhost`n[v3]`nbasicConstraints=critical,CA:TRUE`nsubjectAltName=DNS:localhost,IP:127.0.0.1`n"
    $certArgs = "req -x509 -newkey rsa:2048 -nodes -sha256 -days 3650 -subj /CN=localhost -keyout `"$localRoot/conf/mail-key.pem`" -out `"$localRoot/conf/mail-cert.pem`" -config `"$localRoot/conf/openssl.cnf`""
    $cert = Start-Process -FilePath "$apache/bin/openssl.exe" -ArgumentList $certArgs -WindowStyle Hidden -RedirectStandardError "$localRoot/logs/certificate.log" -Wait -PassThru
    if ($cert.ExitCode -ne 0) { throw 'Local mail certificate generation failed.' }
}
if (!(Test-Path -LiteralPath "$localRoot/conf/public-ca.pem")) {
    & curl.exe -fLsS --proto '=https' --proto-redir '=https' 'https://curl.se/ca/cacert.pem' -o "$localRoot/conf/public-ca.pem"
    if ($LASTEXITCODE -ne 0) { throw 'CA bundle download failed.' }
}
Write-LocalFile "$localRoot/conf/ca.pem" ((Get-Content "$localRoot/conf/public-ca.pem" -Raw) + "`n" + (Get-Content "$localRoot/conf/mail-cert.pem" -Raw))
New-Item -ItemType Directory -Force -Path "$projectRoot/config" | Out-Null
if (!(Test-Path -LiteralPath "$projectRoot/config/database.php")) {
    Write-LocalFile "$projectRoot/config/database.php" "<?php require_once dirname(__DIR__) . '/public_html/core/database.php';"
}
Write-LocalFile "$localRoot/conf/php.ini" @"
[PHP]
extension_dir="$p/ext"
extension=pdo_mysql
extension=mysqli
extension=mbstring
extension=openssl
extension=curl
extension=fileinfo
extension=gd
extension=intl
extension=zip
openssl.cafile="$r/conf/ca.pem"
curl.cainfo="$r/conf/ca.pem"
date.timezone=Asia/Tehran
memory_limit=512M
upload_max_filesize=32M
post_max_size=40M
max_execution_time=120
log_errors=On
display_errors=Off
error_reporting=E_ALL & ~E_DEPRECATED & ~E_STRICT
error_log="$r/logs/php.log"
session.save_path="$r/sessions"
session.cookie_httponly=1
auto_prepend_file="$r/conf/preview.php"
"@
Write-LocalFile "$localRoot/conf/my.ini" @"
[mysqld]
basedir=$($maria.Replace('\','/'))
datadir=$r/data
port=3306
bind-address=127.0.0.1
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
max_allowed_packet=64M
innodb_buffer_pool_size=128M
log_error=$r/logs/mariadb.log
"@
Write-LocalFile "$localRoot/conf/client.ini" @"
[client]
host=127.0.0.1
port=3306
user=root
password=$($secrets.rootPassword)
"@
# Load the real routing/protection rules as directory config. HTTPS redirects
# are omitted only in this loopback-only HTTP server; deployment files stay intact.
$directories = ''
Get-ChildItem -LiteralPath "$projectRoot/public_html" -Filter '.htaccess' -Recurse -Force | ForEach-Object {
    $rules = Get-Content -LiteralPath $_.FullName -Raw
    $rules = [regex]::Replace($rules, '(?m)^\s*RewriteCond %\{HTTPS\} !=on\r?\n\s*RewriteCond %\{HTTP:X-Forwarded-Proto\} !=https\r?\n\s*RewriteRule \^ https://[^\r\n]+', '')
    $directory = $_.DirectoryName.Replace('\', '/')
    $directories += "`n<Directory `"$directory`">`n$rules`n</Directory>`n"
}
$modules = 'authz_core','authz_host','authn_core','access_compat','alias','dir','mime','log_config','env','headers','setenvif','rewrite','filter','deflate','expires'
$loads = ($modules | ForEach-Object { "LoadModule $($_)_module modules/mod_$_.so" }) -join "`n"
Write-LocalFile "$localRoot/conf/httpd.conf" @"
ServerRoot "$a"
Listen 127.0.0.1:8080
ServerName localhost:8080
ServerTokens Prod
PidFile "$r/httpd.pid"
ErrorLog "$r/logs/apache.log"
LogLevel warn
$loads
LoadModule php_module "$p/php8apache2_4.dll"
PHPIniDir "$r/conf"
TypesConfig "$a/conf/mime.types"
DirectoryIndex index.php index.html
DocumentRoot "$web"
<Directory />
    Require all denied
</Directory>
<Directory "$web">
    Require local
    AllowOverride None
    Options FollowSymLinks
</Directory>
<FilesMatch "\.php$">
    SetHandler application/x-httpd-php
</FilesMatch>
<FilesMatch "^\.">
    Require all denied
</FilesMatch>
<Directory "$web/core">
    Require all denied
</Directory>
<Directory "$web/config">
    Require all denied
</Directory>
SetEnv DB_HOST 127.0.0.1
SetEnv DB_PORT 3306
SetEnv DB_NAME macsa_local
SetEnv DB_USER macsa_local
SetEnv DB_PASS $($secrets.dbPassword)
SetEnv MAKSA_ENV_PATH "$r/conf/api.env"
$directories
"@
Write-LocalFile "$localRoot/conf/api.env" @"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080/benefactor-dashboard
LOG_PATH=$r/logs/api.log
JWT_SECRET=$($secrets.jwtSecret)
PAYMENT_GATEWAY=stub
COOKIE_DOMAIN=
CORS_ALLOWED_ORIGINS=http://localhost:8080
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_ENCRYPTION=starttls
MAIL_USERNAME=local
MAIL_PASSWORD=local
MAIL_FROM_ADDRESS=preview@example.test
"@
Copy-Item -LiteralPath "$PSScriptRoot/local-preview.php" -Destination "$localRoot/conf/preview.php" -Force
if (!(Test-Path -LiteralPath "$localRoot/data/mysql")) {
    if ($Action -ne 'setup') { throw 'Run setup with a SQL dump first.' }
    & "$maria/bin/mariadb-install-db.exe" "--datadir=$localRoot/data" "--password=$($secrets.rootPassword)" --port=3306 --silent
    if ($LASTEXITCODE -ne 0) { throw 'Database initialization failed.' }
}
if (!(Get-LocalProcess 'mariadbd.exe')) {
    Assert-FreePort 3306
    Start-Process -FilePath "$maria/bin/mariadbd.exe" -ArgumentList "--defaults-file=`"$localRoot/conf/my.ini`"" -WorkingDirectory $maria -WindowStyle Hidden
    Wait-LocalPort 3306
}
if (!(Get-LocalProcess 'mailpit.exe')) {
    Assert-FreePort 1025
    Assert-FreePort 8025
    $mailArgs = "--listen 127.0.0.1:8025 --smtp 127.0.0.1:1025 --database `"$localRoot/mailpit.db`" --smtp-tls-cert `"$localRoot/conf/mail-cert.pem`" --smtp-tls-key `"$localRoot/conf/mail-key.pem`" --smtp-auth-accept-any --smtp-require-starttls --disable-version-check"
    Start-Process -FilePath "$localRoot/runtime/mailpit/mailpit.exe" -ArgumentList $mailArgs -WindowStyle Hidden -RedirectStandardError "$localRoot/logs/mailpit.log"
    Wait-LocalPort 1025
}
if ($Action -eq 'setup') {
    & "$php/php.exe" -c "$localRoot/conf/php.ini" "$PSScriptRoot/local-db.php" $Dump
    if ($LASTEXITCODE -ne 0) { throw 'Local database import failed; inspect .local/logs/php.log.' }
}
& "$apache/bin/httpd.exe" -f "$localRoot/conf/httpd.conf" -t
if ($LASTEXITCODE -ne 0) { throw 'Apache configuration check failed.' }
if (!(Get-LocalProcess 'httpd.exe')) {
    Assert-FreePort 8080
    Start-Process -FilePath "$apache/bin/httpd.exe" -ArgumentList "-f `"$localRoot/conf/httpd.conf`"" -WorkingDirectory $apache -WindowStyle Hidden
    Wait-LocalPort 8080
}
Write-Output 'Site: http://localhost:8080/home'
Write-Output 'Admin: http://localhost:8080/dashboard/login.php'
Write-Output 'Benefactor: http://localhost:8080/benefactor-dashboard/'
Write-Output 'Test email inbox: http://localhost:8025/'
Write-Output 'Local demo sign-in details: .local/access.txt'
