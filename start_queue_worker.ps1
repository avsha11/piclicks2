# Start Laravel Queue Worker
# Keep this running while using the application

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Laravel Queue Worker - Print Files" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "This worker processes print file generation in the background." -ForegroundColor Yellow
Write-Host "Keep this window open while using the application." -ForegroundColor Yellow
Write-Host ""
Write-Host "Press Ctrl+C to stop the worker." -ForegroundColor Gray
Write-Host ""
Write-Host "Starting worker..." -ForegroundColor Green
Write-Host ""

$projectRoot = "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025"

Set-Location $projectRoot

# Start the queue worker
# --tries=3: Retry failed jobs up to 3 times
# --timeout=600: Allow up to 10 minutes per job
C:\xampp\php\php.exe artisan queue:work --tries=3 --timeout=600 --verbose









