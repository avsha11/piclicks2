# Restore html2canvas - Undo script for html2canvas removal
# This script restores the files that were modified when html2canvas was removed
# 
# Usage: .\restore_html2canvas.ps1 [backup_directory]
# If backup_directory is not specified, it will look for the most recent backup

param(
    [string]$BackupDir = ""
)

# If no backup directory specified, find the most recent one
if ([string]::IsNullOrEmpty($BackupDir)) {
    $backups = Get-ChildItem -Directory -Filter "backup_html2canvas_removal_*" | Sort-Object LastWriteTime -Descending
    if ($backups.Count -eq 0) {
        Write-Host "ERROR: No backup directory found. Cannot restore." -ForegroundColor Red
        exit 1
    }
    $BackupDir = $backups[0].FullName
    Write-Host "Using backup directory: $BackupDir" -ForegroundColor Yellow
}

if (-not (Test-Path $BackupDir)) {
    Write-Host "ERROR: Backup directory does not exist: $BackupDir" -ForegroundColor Red
    exit 1
}

Write-Host "Restoring files from backup: $BackupDir" -ForegroundColor Green

# Files to restore
$filesToRestore = @(
    @{Source = "tool.js"; Dest = "piclicks_live_code_17092025\public\assets\js\tool.js"},
    @{Source = "CollageServices.php"; Dest = "piclicks_live_code_17092025\app\Services\CollageServices.php"},
    @{Source = "current-draft.blade.php"; Dest = "piclicks_live_code_17092025\resources\views\front\orders\current-draft.blade.php"},
    @{Source = "checkout.blade.php"; Dest = "piclicks_live_code_17092025\resources\views\front\checkout\checkout.blade.php"},
    @{Source = "design-collage.blade.php"; Dest = "piclicks_live_code_17092025\resources\views\front\design-collage.blade.php"}
)

$restoredCount = 0
$failedCount = 0

foreach ($file in $filesToRestore) {
    $sourcePath = Join-Path $BackupDir $file.Source
    $destPath = $file.Dest
    
    if (Test-Path $sourcePath) {
        try {
            Copy-Item -Path $sourcePath -Destination $destPath -Force
            Write-Host "  [OK] Restored: $destPath" -ForegroundColor Green
            $restoredCount++
        } catch {
            Write-Host "  [FAILED] Could not restore: $destPath - $($_.Exception.Message)" -ForegroundColor Red
            $failedCount++
        }
    } else {
        Write-Host "  [WARNING] Backup file not found: $sourcePath" -ForegroundColor Yellow
        $failedCount++
    }
}

Write-Host "`nRestore completed:" -ForegroundColor Cyan
Write-Host "  Restored: $restoredCount files" -ForegroundColor Green
Write-Host "  Failed: $failedCount files" -ForegroundColor $(if ($failedCount -eq 0) { "Green" } else { "Red" })

if ($failedCount -eq 0) {
    Write-Host "`nAll files restored successfully!" -ForegroundColor Green
    Write-Host "Note: You may need to clear browser cache or do a hard refresh (Ctrl+F5) to see changes." -ForegroundColor Yellow
} else {
    Write-Host "`nSome files could not be restored. Please check the errors above." -ForegroundColor Red
    exit 1
}

