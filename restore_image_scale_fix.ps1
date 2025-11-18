# Restore script for image scaling fix
# Run this if the fix causes issues

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Restoring from Image Scale Fix Backup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$projectRoot = "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025"

# Check if backups exist
$printFileBackup = Join-Path $projectRoot "app\Services\PrintFileService.php.backup_image_scale_fix"
$collageBackup = Join-Path $projectRoot "app\Services\CollageServices.php.backup_image_scale_fix"

if (-not (Test-Path $printFileBackup)) {
    Write-Host "ERROR: PrintFileService backup not found!" -ForegroundColor Red
    Write-Host "Path: $printFileBackup" -ForegroundColor Yellow
    exit 1
}

if (-not (Test-Path $collageBackup)) {
    Write-Host "ERROR: CollageServices backup not found!" -ForegroundColor Red
    Write-Host "Path: $collageBackup" -ForegroundColor Yellow
    exit 1
}

# Restore files
Write-Host "Restoring PrintFileService.php..." -ForegroundColor Yellow
Copy-Item $printFileBackup -Destination (Join-Path $projectRoot "app\Services\PrintFileService.php") -Force

Write-Host "Restoring CollageServices.php..." -ForegroundColor Yellow
Copy-Item $collageBackup -Destination (Join-Path $projectRoot "app\Services\CollageServices.php") -Force

Write-Host ""
Write-Host "Files restored successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Clear caches: .\clear_caches_and_restart.ps1" -ForegroundColor White
Write-Host "2. Restart your development server" -ForegroundColor White
Write-Host ""











