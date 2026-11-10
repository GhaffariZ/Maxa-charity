# Windows PowerShell 5.1; no winget, curl, Git, Node, or Python required to bootstrap.
function Receive-LocalDownload([string]$Url, [string]$Destination, [string]$Sha256 = '') {
    if (Test-Path -LiteralPath $Destination) {
        if (!$Sha256 -or (Get-FileHash -LiteralPath $Destination -Algorithm SHA256).Hash.ToLower() -eq $Sha256) { return }
    }
    $curl = Get-Command curl.exe -CommandType Application -ErrorAction SilentlyContinue
    if (!$curl) { Add-Type -AssemblyName System.Net.Http }
    [Net.ServicePointManager]::SecurityProtocol = [Net.ServicePointManager]::SecurityProtocol -bor [Net.SecurityProtocolType]::Tls12
    $partial = "$Destination.partial"
    for ($attempt = 1; $attempt -le 3; $attempt++) {
        $handler = $null
        $client = $null
        try {
            if ($curl) {
                # Prefer Windows' bundled downloader; slow/stalled transfers are bounded.
                & $curl.Source --fail --location --silent --show-error --proto '=https' --proto-redir '=https' --connect-timeout 30 --max-time 600 --speed-limit 1024 --speed-time 60 --output $partial $Url
                if ($LASTEXITCODE -ne 0) { throw "Download failed (curl exit $LASTEXITCODE)." }
            } else {
                $handler = New-Object Net.Http.HttpClientHandler
                $handler.AllowAutoRedirect = $false
                $client = New-Object Net.Http.HttpClient($handler)
                $client.Timeout = [TimeSpan]::FromMinutes(5)
                $client.DefaultRequestHeaders.UserAgent.ParseAdd('Macsa-Local-Setup/1.0')
                $uri = [Uri]$Url
                $downloaded = $false
                for ($redirect = 0; $redirect -lt 10; $redirect++) {
                    if ($uri.Scheme -ne 'https') { throw 'Downloads must remain on HTTPS.' }
                    # Buffer within GetAsync so HttpClient.Timeout covers the entire transfer.
                    $response = $client.GetAsync($uri).GetAwaiter().GetResult()
                    if ([int]$response.StatusCode -in 301,302,303,307,308) {
                        $next = New-Object Uri($uri, $response.Headers.Location)
                        $response.Dispose()
                        $uri = $next
                        continue
                    }
                    $response.EnsureSuccessStatusCode() | Out-Null
                    $stream = [IO.File]::Create($partial)
                    try { $response.Content.CopyToAsync($stream).GetAwaiter().GetResult() | Out-Null } finally { $stream.Dispose(); $response.Dispose() }
                    $downloaded = $true
                    break
                }
                if (!$downloaded) { throw 'Too many download redirects.' }
            }
            if ($Sha256 -and (Get-FileHash -LiteralPath $partial -Algorithm SHA256).Hash.ToLower() -ne $Sha256) { throw 'Downloaded file failed SHA256 verification.' }
            Move-Item -LiteralPath $partial -Destination $Destination -Force
            return
        } catch {
            if ($attempt -eq 3) { throw }
            Write-Host "Download attempt $attempt failed; retrying."
        } finally {
            if ($client) { $client.Dispose() }
            if ($handler) { $handler.Dispose() }
        }
    }
}

function Get-LocalPackages([string]$LocalRoot) {
    @(
        @{ Name='php'; File='php.zip'; Url='https://downloads.php.net/~windows/releases/php-8.4.26-Win32-vs17-x64.zip'; Hash='6e56f0e932e92bfdce208d3a7e6068f6e2f7a19fc2922a5f856fb085f61673f3'; Target="$LocalRoot/runtime/php"; Directory="$LocalRoot/runtime/php" },
        @{ Name='apache'; File='apache.zip'; Url='https://www.apachelounge.com/download/VS18/binaries/httpd-2.4.69-261002-Win64-VS18.zip'; Hash='ca272e0b75abad78b1e4029a0753b20ff2232d2d857e8eebeea5d469c5776977'; Target="$LocalRoot/runtime/apache"; Directory="$LocalRoot/runtime/apache/Apache24" },
        @{ Name='maria'; File='mariadb.zip'; Url='https://dlm.mariadb.com/4801422/MariaDB/mariadb-11.4.13/winx64-packages/mariadb-11.4.13-winx64.zip'; Hash='d62986d433eeebfde218560b276103831604a61e929e87f1a17f5aebd80257e2'; Target="$LocalRoot/runtime"; Directory="$LocalRoot/runtime/mariadb-11.4.13-winx64" },
        @{ Name='mailpit'; File='mailpit.zip'; Url='https://github.com/axllent/mailpit/releases/download/v1.31.6/mailpit-windows-amd64.zip'; Hash='5d5ad094bfb3bac9004f9d1105d2335fea9f8952c09951df73ae0a280e34d3b1'; Target="$LocalRoot/runtime/mailpit"; Directory="$LocalRoot/runtime/mailpit" },
        @{ Name='node'; File='node.zip'; Url='https://nodejs.org/dist/v24.22.0/node-v24.22.0-win-x64.zip'; Hash='f9683a19877e12a9b167305c8db71040d812876359de21f0d2e97c3f321f80c3'; Target="$LocalRoot/runtime"; Directory="$LocalRoot/runtime/node-v24.22.0-win-x64" }
    )
}

function Test-LocalRuntime([string]$Name, [string]$Directory) {
    if (!$Directory -or !(Test-Path -LiteralPath $Directory -PathType Container)) { return $false }
    $required = switch ($Name) {
        php { @('php.exe','php8apache2_4.dll','ext/php_pdo_mysql.dll','ext/php_mysqli.dll','ext/php_mbstring.dll','ext/php_openssl.dll','ext/php_curl.dll','ext/php_fileinfo.dll','ext/php_gd.dll','ext/php_intl.dll','ext/php_zip.dll') }
        apache {
            @('bin/httpd.exe','bin/openssl.exe','conf/mime.types')
            'authz_core','authz_host','authn_core','access_compat','alias','dir','mime','log_config','env','headers','setenvif','rewrite','filter','deflate','expires' | ForEach-Object { "modules/mod_$_.so" }
        }
        maria { @('bin/mariadbd.exe','bin/mariadb-install-db.exe','bin/mariadb-admin.exe') }
        mailpit { @('mailpit.exe') }
        node { @('node.exe','npm.cmd','node_modules/npm/bin/npm-cli.js') }
        default { return $false }
    }
    foreach ($file in $required) { if (!(Test-Path -LiteralPath (Join-Path $Directory $file) -PathType Leaf)) { return $false } }
    try {
        $text = switch ($Name) {
            php { & "$Directory/php.exe" -n -r 'echo PHP_VERSION,chr(32),PHP_ZTS,chr(32),PHP_INT_SIZE;' 2>$null }
            apache { & "$Directory/bin/httpd.exe" -v 2>$null }
            maria { & "$Directory/bin/mariadbd.exe" --version 2>$null }
            mailpit { & "$Directory/mailpit.exe" version 2>$null }
            node { & "$Directory/node.exe" -p 'process.versions.node.concat(String.fromCharCode(32),process.arch)' 2>$null }
        }
        if ($LASTEXITCODE -ne 0) { return $false }
        $text = $text -join "`n"
        switch ($Name) {
            php {
                if ($text -notmatch '^8\.4\.\d+ 1 8$') { return $false }
                $extensions = 'pdo_mysql','mysqli','mbstring','openssl','curl','fileinfo','gd','intl','zip'
                $probe = @('-n','-d',"extension_dir=$Directory/ext")
                foreach ($extension in $extensions) { $probe += @('-d',"extension=$extension") }
                $loaded = & "$Directory/php.exe" @probe -m 2>$null
                if ($LASTEXITCODE -ne 0) { return $false }
                foreach ($extension in $extensions) { if ($loaded -notcontains $extension) { return $false } }
                return $true
            }
            apache { return $text -match 'Apache/2\.4\.' -and $text -match 'Win64' }
            maria { return $text -match 'MariaDB' -and $text -match '(\d+)\.(\d+)\.\d+-MariaDB' -and ([version]($Matches[1]+'.'+$Matches[2]) -ge [version]'11.4') }
            mailpit { return $text -match '(\d+\.\d+\.\d+)' -and [version]$Matches[1] -ge [version]'1.26.0' }
            node { return $text -match '^(\d+\.\d+\.\d+) x64$' -and [version]$Matches[1] -ge [version]'20.19.0' }
        }
    } catch { return $false }
}

function Install-LocalPackage($Package, [string]$LocalRoot) {
    if (Test-LocalRuntime $Package.Name $Package.Directory) {
        Write-Host "Already installed: $($Package.Name) ($($Package.Directory))"
        return $Package.Directory
    }
    $zip = "$LocalRoot/downloads/$($Package.File)"
    Write-Host "Installing missing runtime: $($Package.Name)"
    Receive-LocalDownload $Package.Url $zip $Package.Hash
    Expand-Archive -LiteralPath $zip -DestinationPath $Package.Target -Force
    if (!(Test-LocalRuntime $Package.Name $Package.Directory)) { throw "Runtime validation failed: $($Package.Name). Check the Visual C++ runtime and downloaded files." }
    return $Package.Directory
}

function Install-LocalVisualCpp([string]$LocalRoot) {
    # Known working v14 runtimes (including the current laptop) are reused.
    foreach ($key in 'HKLM:/SOFTWARE/Microsoft/VisualStudio/14.0/VC/Runtimes/x64','HKLM:/SOFTWARE/WOW6432Node/Microsoft/VisualStudio/14.0/VC/Runtimes/x64') {
        $vc = Get-ItemProperty $key -ErrorAction SilentlyContinue
        if ($vc -and $vc.Installed -eq 1 -and [version]$vc.Version.TrimStart('v') -ge [version]'14.50') {
            Write-Host "Visual C++ x64 already installed: $($vc.Version)"
            return
        }
    }
    $installer = "$LocalRoot/downloads/vc_redist.x64.exe"
    Receive-LocalDownload 'https://aka.ms/vc14/vc_redist.x64.exe' $installer
    $signature = Get-AuthenticodeSignature -LiteralPath $installer
    if ($signature.Status -ne 'Valid' -or $signature.SignerCertificate.Subject -notmatch 'O=Microsoft Corporation') { throw 'Visual C++ installer must have a valid Microsoft signature.' }
    Write-Host 'Installing required Visual C++ x64. Windows may ask for administrator approval.'
    $process = Start-Process -FilePath $installer -ArgumentList '/install /quiet /norestart' -Verb RunAs -WindowStyle Hidden -Wait -PassThru
    if ($process.ExitCode -in 3010,1641) { throw 'Visual C++ requires a Windows restart. Restart Windows and run Install-Local.cmd again.' }
    if ($process.ExitCode -ne 0) { throw "Visual C++ installation failed: $($process.ExitCode)" }
}

function Get-InstalledRuntimeCandidates([string]$Name, $Saved) {
    if ($Saved -and $Saved.PSObject.Properties[$Name]) { $Saved.$Name }
    $command = switch ($Name) { php {'php.exe'} apache {'httpd.exe'} maria {'mariadbd.exe'} mailpit {'mailpit.exe'} node {'node.exe'} }
    $found = Get-Command $command -CommandType Application -ErrorAction SilentlyContinue
    foreach ($item in $found) {
        $dir = Split-Path $item.Source -Parent
        if ($Name -in 'apache','maria') { $dir = Split-Path $dir -Parent }
        $dir
    }
    switch ($Name) {
        php { 'C:/xampp/php'; 'C:/php'; Get-ChildItem 'C:/laragon/bin/php' -Directory -ErrorAction SilentlyContinue | Select-Object -ExpandProperty FullName }
        apache { 'C:/xampp/apache'; 'C:/Apache24'; Get-ChildItem 'C:/laragon/bin/apache' -Directory -ErrorAction SilentlyContinue | Select-Object -ExpandProperty FullName }
        maria { 'C:/xampp/mysql'; Get-ChildItem 'C:/Program Files' -Directory -Filter 'MariaDB*' -ErrorAction SilentlyContinue | Select-Object -ExpandProperty FullName }
        node {
            'C:/Program Files/nodejs'
            if ($env:LOCALAPPDATA) { "$env:LOCALAPPDATA/Programs/nodejs" }
            if ($env:NVM_HOME) { Get-ChildItem -LiteralPath $env:NVM_HOME -Directory -ErrorAction SilentlyContinue | Select-Object -ExpandProperty FullName }
        }
    }
}

function Initialize-LocalPrerequisites([string]$ProjectRoot, [string]$LocalRoot) {
    if (![Environment]::Is64BitOperatingSystem -or [Environment]::OSVersion.Version.Major -lt 10) { throw 'This installer requires Windows 10/11 x64.' }
    Install-LocalVisualCpp $LocalRoot
    $savedFile = "$LocalRoot/conf/runtimes.json"
    $saved = if (Test-Path -LiteralPath $savedFile) { Get-Content -LiteralPath $savedFile -Raw | ConvertFrom-Json } else { $null }
    $resolved = @{}
    foreach ($package in (Get-LocalPackages $LocalRoot)) {
        $candidates = @($package.Directory) + @(Get-InstalledRuntimeCandidates $package.Name $saved)
        foreach ($candidate in ($candidates | Select-Object -Unique)) {
            if (Test-LocalRuntime $package.Name $candidate) { $resolved[$package.Name] = [IO.Path]::GetFullPath($candidate); break }
        }
        if ($resolved.ContainsKey($package.Name)) { Write-Host "Reusing $($package.Name): $($resolved[$package.Name])" }
        else { $resolved[$package.Name] = Install-LocalPackage $package $LocalRoot }
    }
    Write-LocalFile $savedFile ($resolved | ConvertTo-Json)
    $env:PATH = "$($resolved.php);$($resolved.node);$($resolved.apache)/bin;$env:PATH"
    $frontend = Join-Path $ProjectRoot 'benefactor userpanel'
    $npm = "$($resolved.node)/node.exe"
    $npmCli = "$($resolved.node)/node_modules/npm/bin/npm-cli.js"
    $check = Start-Process -FilePath $npm -ArgumentList "`"$npmCli`" ls --all --json --include=dev --include=optional" -WorkingDirectory $frontend -WindowStyle Hidden -Wait -PassThru -RedirectStandardOutput "$LocalRoot/logs/npm-check.json" -RedirectStandardError "$LocalRoot/logs/npm-check.log"
    if ($check.ExitCode -eq 0) { Write-Host 'React dependencies already installed; skipping installation.' }
    else {
        # npm install repairs missing dependencies without deleting the existing tree.
        Write-Host 'Installing missing React dependencies (see .local/logs/npm-install.log).'
        $npmAction = if (Test-Path -LiteralPath "$frontend/node_modules") { 'install' } else { 'ci' }
        $install = Start-Process -FilePath $npm -ArgumentList "`"$npmCli`" $npmAction --include=dev --include=optional --ignore-scripts --no-audit --no-fund" -WorkingDirectory $frontend -WindowStyle Hidden -Wait -PassThru -RedirectStandardOutput "$LocalRoot/logs/npm-install.log" -RedirectStandardError "$LocalRoot/logs/npm-install-errors.log"
        if ($install.ExitCode -ne 0) { throw 'React dependencies could not be installed. Check .local/logs/npm-install-errors.log, then run the installer again.' }
    }
    # The tracked React build is normally complete; rebuild only if assets are absent.
    $index = "$ProjectRoot/public_html/benefactor-dashboard/index.html"
    $buildMissing = !(Test-Path -LiteralPath $index)
    if (!$buildMissing) {
        foreach ($asset in [regex]::Matches((Get-Content -LiteralPath $index -Raw), '(?:src|href)="(/benefactor-dashboard/assets/[^"]+)"')) {
            if (!(Test-Path -LiteralPath (Join-Path "$ProjectRoot/public_html" $asset.Groups[1].Value.TrimStart('/')))) { $buildMissing = $true }
        }
    }
    if ($buildMissing) {
        Write-Host 'Building missing React assets (see .local/logs/frontend-build.log).'
        $build = Start-Process -FilePath $npm -ArgumentList "`"$npmCli`" run build" -WorkingDirectory $frontend -WindowStyle Hidden -Wait -PassThru -RedirectStandardOutput "$LocalRoot/logs/frontend-build.log" -RedirectStandardError "$LocalRoot/logs/frontend-build-errors.log"
        if ($build.ExitCode -ne 0) { throw 'React build failed. Check .local/logs/frontend-build-errors.log.' }
        New-Item -ItemType Directory -Force -Path "$ProjectRoot/public_html/benefactor-dashboard" | Out-Null
        Copy-Item -Path "$frontend/dist/*" -Destination "$ProjectRoot/public_html/benefactor-dashboard" -Recurse -Force
    }
    return $resolved
}
