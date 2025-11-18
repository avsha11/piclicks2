# Restore to point before text disappeared
# This restores from the IMAGE_SCALE_FIX backups

Write-Host "=== RESTORING TO BEFORE TEXT DISAPPEARED ===" -ForegroundColor Cyan
Write-Host ""

$projectRoot = "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025"

# Check if backups exist
$printFileBackup = "$projectRoot\app\Services\PrintFileService.php.backup_image_scale_fix"
$collageBackup = "$projectRoot\app\Services\CollageServices.php.backup_image_scale_fix"

if (-not (Test-Path $printFileBackup)) {
    Write-Host "ERROR: Backup file not found: $printFileBackup" -ForegroundColor Red
    Write-Host ""
    Write-Host "Cannot restore - backup files are missing." -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
}

if (-not (Test-Path $collageBackup)) {
    Write-Host "ERROR: Backup file not found: $collageBackup" -ForegroundColor Red
    Write-Host ""
    Write-Host "Cannot restore - backup files are missing." -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
}

Write-Host "Found backup files:" -ForegroundColor Green
Write-Host "  - PrintFileService.php.backup_image_scale_fix"
Write-Host "  - CollageServices.php.backup_image_scale_fix"
Write-Host ""

# Create backups of current files (before restoring)
Write-Host "Creating backup of CURRENT files..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
Copy-Item "$projectRoot\app\Services\PrintFileService.php" "$projectRoot\app\Services\PrintFileService.php.before_restore_$timestamp" -Force
Copy-Item "$projectRoot\app\Services\CollageServices.php" "$projectRoot\app\Services\CollageServices.php.before_restore_$timestamp" -Force
Write-Host "  - Created PrintFileService.php.before_restore_$timestamp" -ForegroundColor Gray
Write-Host "  - Created CollageServices.php.before_restore_$timestamp" -ForegroundColor Gray
Write-Host ""

# Restore from backups
Write-Host "Restoring from backups..." -ForegroundColor Yellow
Copy-Item $printFileBackup "$projectRoot\app\Services\PrintFileService.php" -Force
Copy-Item $collageBackup "$projectRoot\app\Services\CollageServices.php" -Force

Write-Host ""
Write-Host "=== RESTORATION COMPLETE ===" -ForegroundColor Green
Write-Host ""
Write-Host "Restored to IMAGE_SCALE_FIX state (before text disappeared)" -ForegroundColor Green
Write-Host ""
Write-Host "This version has:" -ForegroundColor Cyan
Write-Host "  - Image scaling fix (proportional from editor to print)" -ForegroundColor White
Write-Host "  - Text rendering with magenta debug markers" -ForegroundColor White
Write-Host "  - Extensive text debugging logs" -ForegroundColor White
Write-Host ""
Write-Host "IMPORTANT: You MUST clear caches and restart server:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Clear caches:" -ForegroundColor White
Write-Host "   cd `"$projectRoot`"" -ForegroundColor Gray
Write-Host "   C:\xampp\php\php.exe artisan cache:clear" -ForegroundColor Gray
Write-Host "   C:\xampp\php\php.exe artisan config:clear" -ForegroundColor Gray
Write-Host ""
Write-Host "2. If you have a server running, restart it:" -ForegroundColor White
Write-Host "   Press Ctrl+C in server terminal" -ForegroundColor Gray
Write-Host "   C:\xampp\php\php.exe artisan serve" -ForegroundColor Gray
Write-Host ""
Write-Host "3. Test with a new collage" -ForegroundColor White
Write-Host ""

Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")










