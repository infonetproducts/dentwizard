# DentWizard API Cleanup Script
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host "  DentWizard API Cleanup Script" -ForegroundColor Cyan
Write-Host "===================================================================" -ForegroundColor Cyan

$apiPath = "C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API"

# Production files to KEEP
$productionFiles = @(
    "$apiPath\cors.php",
    "$apiPath\v1\cart\cart.php",
    "$apiPath\v1\cart\apply-discount.php",
    "$apiPath\v1\cart\clear.php",
    "$apiPath\v1\products\list.php",
    "$apiPath\v1\products\detail.php",
    "$apiPath\v1\categories\list.php",
    "$apiPath\v1\orders\create.php",
    "$apiPath\v1\orders\detail.php",
    "$apiPath\v1\orders\my-orders.php",
    "$apiPath\v1\user\profile.php"
)

Write-Host "Production files to preserve:" -ForegroundColor Green
foreach ($file in $productionFiles) {
    Write-Host "  $($file.Replace($apiPath, ''))" -ForegroundColor Green
}

$confirmation = Read-Host "`nCreate backup and proceed? (yes/no)"
if ($confirmation -ne "yes") {
    Write-Host "Cancelled." -ForegroundColor Yellow
    exit
}

Write-Host "`nCreating backup..." -ForegroundColor Cyan
$timestamp = Get-Date -Format 'yyyy-MM-dd-HHmmss'
$backupPath = "C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API-BACKUP-$timestamp"
Copy-Item -Path $apiPath -Destination $backupPath -Recurse -Force
Write-Host "Backup created: $backupPath" -ForegroundColor Green

$deletedCount = 0
$preservedCount = 0

function Is-ProductionFile($filePath) {
    foreach ($prodFile in $productionFiles) {
        if ($filePath -eq $prodFile) {
            return $true
        }
    }
    return $false
}

function Remove-TestFiles($directory) {
    Write-Host "`nCleaning: $($directory.Replace($apiPath, ''))" -ForegroundColor Cyan
    $allFiles = Get-ChildItem -Path $directory -File
    foreach ($file in $allFiles) {
        if (Is-ProductionFile $file.FullName) {
            Write-Host "  KEEP: $($file.Name)" -ForegroundColor Green
            $script:preservedCount++
        } elseif ($file.Name -match '\.(md|gitignore|htaccess|json|env|txt)$') {
            Write-Host "  KEEP: $($file.Name)" -ForegroundColor DarkGray
            $script:preservedCount++
        } else {
            Write-Host "  DELETE: $($file.Name)" -ForegroundColor Red
            Remove-Item $file.FullName -Force
            $script:deletedCount++
        }
    }
}

Write-Host "`nPhase 1: Root API directory" -ForegroundColor Yellow
$testFiles = @("test-cors.php", "test-database.php", "test-endpoints.html", "test-environment.php", "test-php-compat.php", "test-security.php")
foreach ($file in $testFiles) {
    $fullPath = Join-Path $apiPath $file
    if (Test-Path $fullPath) {
        Write-Host "  DELETE: $file" -ForegroundColor Red
        Remove-Item $fullPath -Force
        $deletedCount++
    }
}

Write-Host "`nPhase 2: v1 root directory" -ForegroundColor Yellow
$v1Path = Join-Path $apiPath "v1"
$v1TestFiles = Get-ChildItem -Path $v1Path -File | Where-Object { $_.Name -match '^(test-|check-|database-info)' -or $_.Name -eq 'test.php' }
foreach ($file in $v1TestFiles) {
    Write-Host "  DELETE: $($file.Name)" -ForegroundColor Red
    Remove-Item $file.FullName -Force
    $deletedCount++
}

Write-Host "`nPhase 3: Subdirectories" -ForegroundColor Yellow
$directories = @("v1\cart", "v1\orders", "v1\products", "v1\categories", "v1\user")
foreach ($dir in $directories) {
    $fullPath = Join-Path $apiPath $dir
    if (Test-Path $fullPath) {
        Remove-TestFiles -directory $fullPath
    }
}

Write-Host "`n===================================================================" -ForegroundColor Cyan
Write-Host "  Cleanup Complete!" -ForegroundColor Green
Write-Host "===================================================================" -ForegroundColor Cyan
Write-Host "Deleted: $deletedCount files" -ForegroundColor Red
Write-Host "Preserved: $preservedCount files" -ForegroundColor Green
Write-Host "Backup: $backupPath" -ForegroundColor Yellow
