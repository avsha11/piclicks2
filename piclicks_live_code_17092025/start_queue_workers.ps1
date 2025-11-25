# PowerShell script to start multiple Laravel queue workers
# This allows processing multiple print file generation jobs simultaneously

param(
    [int]$WorkerCount = 3,  # Number of workers to start (default: 3)
    [string]$QueueName = "default"  # Queue name (default: "default")
)

$ProjectPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$PhpPath = "C:\xampp\php\php.exe"
$ArtisanPath = Join-Path $ProjectPath "artisan"

Write-Host "Starting $WorkerCount queue workers..." -ForegroundColor Green
Write-Host "Project: $ProjectPath" -ForegroundColor Cyan
Write-Host "Queue: $QueueName" -ForegroundColor Cyan
Write-Host ""

# Start workers in separate windows
for ($i = 1; $i -le $WorkerCount; $i++) {
    $WorkerTitle = "Queue Worker $i/$WorkerCount"
    Write-Host "Starting worker $i..." -ForegroundColor Yellow
    
    # Start a new PowerShell window for each worker
    Start-Process powershell -ArgumentList @(
        "-NoExit",
        "-Command",
        "cd '$ProjectPath'; Write-Host 'Worker $i - Processing queue: $QueueName' -ForegroundColor Green; `$Host.UI.RawUI.WindowTitle = '$WorkerTitle'; $PhpPath artisan queue:work --queue=$QueueName --tries=3 --timeout=600"
    )
    
    # Small delay to avoid race conditions
    Start-Sleep -Milliseconds 500
}

Write-Host ""
Write-Host "All workers started!" -ForegroundColor Green
Write-Host "Each worker runs in a separate window." -ForegroundColor Cyan
Write-Host "Close individual windows to stop specific workers." -ForegroundColor Yellow
Write-Host ""
Write-Host "To stop all workers, close all PowerShell windows or use:" -ForegroundColor Yellow
Write-Host "  Get-Process | Where-Object {`$_.MainWindowTitle -like '*Queue Worker*'} | Stop-Process" -ForegroundColor Gray

