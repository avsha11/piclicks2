# Clear Laravel Caches and Instructions for Server Restart

$projectRoot = "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025"

Write-Host "=== Clearing Laravel Caches ===" -ForegroundColor Cyan
Write-Host ""

cd $projectRoot

Write-Host "Clearing configuration cache..." -ForegroundColor Yellow
C:\xampp\php\php.exe artisan config:clear

Write-Host "Clearing route cache..." -ForegroundColor Yellow
C:\xampp\php\php.exe artisan route:clear

Write-Host "Clearing view cache..." -ForegroundColor Yellow
C:\xampp\php\php.exe artisan view:clear

Write-Host ""
Write-Host "All caches cleared!" -ForegroundColor Green
Write-Host ""
Write-Host "=== Server Restart Instructions ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "To restart your development server:" -ForegroundColor White
Write-Host "1. Press Ctrl+C in the terminal where php artisan serve is running" -ForegroundColor Gray
Write-Host "2. Wait for it to stop completely" -ForegroundColor Gray
Write-Host "3. Run: C:\xampp\php\php.exe artisan serve" -ForegroundColor Gray
Write-Host ""
Write-Host "After restarting, test print file generation with a new collage." -ForegroundColor Yellow
