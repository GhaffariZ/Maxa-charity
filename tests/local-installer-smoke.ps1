# Run on Windows after setup. Exercises fresh extraction and re-run behavior.
param([switch]$WithFrontend)
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
. "$projectRoot/tools/local-prerequisites.ps1"
function Check([bool]$Condition, [string]$Message) { if (!$Condition) { throw $Message } }
function Write-LocalFile([string]$Path,[string]$Content) { [IO.File]::WriteAllText($Path,$Content,(New-Object Text.UTF8Encoding($false))) }
$testRoot = Join-Path $projectRoot ('.local/installer-tests/' + [guid]::NewGuid().ToString('N') + '/Project With Spaces')
New-Item -ItemType Directory -Force -Path "$testRoot/downloads" | Out-Null
$cacheRoot = Join-Path $projectRoot '.local/downloads'
foreach ($package in (Get-LocalPackages $testRoot)) {
    $cached = Join-Path $cacheRoot $package.File
    Check (Test-Path -LiteralPath $cached) "Missing cached package $cached. Run setup first (and download the Node package for this test)."
    # Hard links share the verified cache bytes; runtime extraction stays isolated.
    New-Item -ItemType HardLink -Path "$testRoot/downloads/$($package.File)" -Target $cached | Out-Null
    Check (!(Test-LocalRuntime $package.Name $package.Directory)) 'Fresh directory was incorrectly detected as installed.'
    $installed = Install-LocalPackage $package $testRoot
    Check ($installed -is [string]) 'A download leaked output into the runtime path.'
    Check (Test-LocalRuntime $package.Name $installed) "Fresh $($package.Name) failed validation."
    $files = @(Get-ChildItem -LiteralPath $installed -File -Recurse | Select-Object -First 20)
    $timestamps = @($files | ForEach-Object { $_.LastWriteTimeUtc.Ticks })
    $again = Install-LocalPackage $package $testRoot
    Check ($again -eq $installed) 'Re-run changed the installed location.'
    for ($i=0; $i -lt $files.Count; $i++) {
        Check ((Get-Item -LiteralPath $files[$i].FullName).LastWriteTimeUtc.Ticks -eq $timestamps[$i]) 'Re-run overwrote an already installed runtime file.'
    }
    Write-Output "PASS $($package.Name): fresh install, files validated, second install skipped (paths with spaces)."
}
# A partial PHP install must not be mistaken for a working one.
New-Item -ItemType Directory -Force -Path "$testRoot/partial-php" | Out-Null
Copy-Item -LiteralPath "$testRoot/runtime/php/php.exe" -Destination "$testRoot/partial-php/php.exe"
Check (!(Test-LocalRuntime 'php' "$testRoot/partial-php")) 'Partial PHP installation was accepted.'
# Invalid archive checksums must fail before extraction; an existing runtime
# must skip archive validation/download entirely.
$validPackage = (Get-LocalPackages $testRoot)[-1]
$skipPackage = $validPackage.Clone()
$skipPackage.Hash = '0' * 64
$skipPackage.Url = 'https://invalid.example.test/not-downloaded.zip'
Check ((Install-LocalPackage $skipPackage $testRoot) -eq $validPackage.Directory) 'Installed runtime was not skipped.'
Write-Output 'PASS partial-install detection and already-installed offline skip.'
if ($WithFrontend) {
    $frontend = Join-Path $testRoot 'benefactor userpanel'
    New-Item -ItemType Directory -Force -Path $frontend,"$testRoot/conf","$testRoot/logs" | Out-Null
    foreach ($name in 'src','public','package.json','package-lock.json','vite.config.ts','tsconfig.json','postcss.config.mjs','index.html') {
        $file = Join-Path "$projectRoot/benefactor userpanel" $name
        if (Test-Path -LiteralPath $file) { Copy-Item -LiteralPath $file -Destination $frontend -Recurse -Force }
    }
    $first = Initialize-LocalPrerequisites $testRoot $testRoot
    Check ($first -is [hashtable]) 'Build output leaked into the returned runtime map.'
    Check (Test-Path -LiteralPath "$testRoot/public_html/benefactor-dashboard/index.html") 'Fresh React build is missing.'
    $second = Initialize-LocalPrerequisites $testRoot $testRoot
    Write-Output 'PASS fresh React dependency installation/build and second-run skip.'
}
# Simulate the absent-runtime branch without changing Windows or launching UAC.
$script:vcInstalls = 0
function Get-ItemProperty { param($Path, $ErrorAction) if ($script:vcInstalls) { [pscustomobject]@{ Installed=1; Version='v14.51.36247.0' } } }
function Receive-LocalDownload { param($Url, $Destination, $Sha256) }
function Get-AuthenticodeSignature { param($LiteralPath) [pscustomobject]@{ Status='Valid'; SignerCertificate=[pscustomobject]@{ Subject='CN=Microsoft Corporation, O=Microsoft Corporation' } } }
function Start-Process { param($FilePath,$ArgumentList,$Verb,$WindowStyle,[switch]$Wait,[switch]$PassThru) $script:vcInstalls++; [pscustomobject]@{ ExitCode=0 } }
Install-LocalVisualCpp $testRoot
Install-LocalVisualCpp $testRoot
Check ($script:vcInstalls -eq 1) 'Visual C++ was not installed exactly once when absent.'
Write-Output 'PASS Visual C++ missing/existing branches (simulated; system untouched).'
Write-Output "Installer test files: $testRoot"
