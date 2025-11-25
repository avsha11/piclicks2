# PowerShell script to stop all Laravel queue workers

Write-Host "Stopping all queue workers..." -ForegroundColor Yellow

# Find all PowerShell processes with "Queue Worker" in the title
$workers = Get-Process | Where-Object { 
    $_.MainWindowTitle -like "*Queue Worker*" -or 
    $_.CommandLine -like "*queue:work*"
}

if ($workers.Count -eq 0) {
    Write-Host "No queue workers found running." -ForegroundColor Green
    exit 0
}

Write-Host "Found $($workers.Count) worker(s) to stop..." -ForegroundColor Cyan

foreach ($worker in $workers) {
    Write-Host "Stopping worker: $($worker.ProcessName) (PID: $($worker.Id))" -ForegroundColor Yellow
    Stop-Process -Id $worker.Id -Force
}

Write-Host ""
Write-Host "All workers stopped!" -ForegroundColor Green

