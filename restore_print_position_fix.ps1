# Restore Print File Positioning Fix
# This script restores the most recent backup of CollageServices.php and PrintFileService.php

$projectRoot = "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025"
$servicesPath = "$projectRoot\app\Services"

Write-Host "=== Restore Print File Positioning Fix ===" -ForegroundColor Cyan
Write-Host ""

# Find most recent backups
$collageBackups = Get-ChildItem "$servicesPath\CollageServices.php.backup_*" | Sort-Object LastWriteTime -Descending
$printFileBackups = Get-ChildItem "$servicesPath\PrintFileService.php.backup_*" | Sort-Object LastWriteTime -Descending

if ($collageBackups.Count -eq 0 -or $printFileBackups.Count -eq 0) {
    Write-Host "ERROR: Backup files not found!" -ForegroundColor Red
    Write-Host "Please ensure backup files exist in $servicesPath" -ForegroundColor Yellow
    exit 1
}

$collageBackup = $collageBackups[0]
$printFileBackup = $printFileBackups[0]

Write-Host "Found backups:" -ForegroundColor Green
Write-Host "  CollageServices: $($collageBackup.Name)" -ForegroundColor Gray
Write-Host "  PrintFileService: $($printFileBackup.Name)" -ForegroundColor Gray
Write-Host ""

# Confirm restore
$confirm = Read-Host "Restore these backups? (y/n)"
if ($confirm -ne 'y') {
    Write-Host "Restore cancelled." -ForegroundColor Yellow
    exit 0
}

# Restore files
try {
    Copy-Item $collageBackup.FullName "$servicesPath\CollageServices.php" -Force
    Write-Host "✓ Restored CollageServices.php" -ForegroundColor Green
    
    Copy-Item $printFileBackup.FullName "$servicesPath\PrintFileService.php" -Force
    Write-Host "✓ Restored PrintFileService.php" -ForegroundColor Green
    
    Write-Host ""
    Write-Host "Files restored successfully!" -ForegroundColor Green
    Write-Host "Remember to clear Laravel caches and restart the server." -ForegroundColor Yellow
} catch {
    Write-Host "ERROR: Failed to restore files" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}









