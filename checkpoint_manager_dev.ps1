# PicLicks Development Checkpoint Manager
# PowerShell script for managing localhost:8000 checkpoints

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("create", "restore", "list", "backup", "snapshot")]
    [string]$Action,
    
    [Parameter(Mandatory=$false)]
    [string]$CheckpointName,
    
    [Parameter(Mandatory=$false)]
    [string]$Description
)

# Configuration
$RepositoryPath = "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor"
$DevPath = Join-Path $RepositoryPath "piclicks_live_code_17092025"
$CheckpointsDir = Join-Path $RepositoryPath "dev_checkpoints"

# Create checkpoints directory if it doesn't exist
if (-not (Test-Path $CheckpointsDir)) {
    New-Item -ItemType Directory -Path $CheckpointsDir | Out-Null
}

function Show-CheckpointList {
    Write-Host "`n📋 Development Checkpoints (localhost:8000):" -ForegroundColor Cyan
    Write-Host "===========================================" -ForegroundColor Cyan
    
    if (Test-Path $CheckpointsDir) {
        $checkpoints = Get-ChildItem -Path $CheckpointsDir -Directory | Sort-Object Name -Descending
        
        if ($checkpoints.Count -eq 0) {
            Write-Host "No checkpoints found." -ForegroundColor Yellow
        } else {
            foreach ($checkpoint in $checkpoints) {
                $date = $checkpoint.LastWriteTime.ToString("yyyy-MM-dd HH:mm:ss")
                Write-Host "📁 $($checkpoint.Name)" -ForegroundColor Green
                Write-Host "   Created: $date" -ForegroundColor Gray
                
                # Check if description file exists
                $descFile = Join-Path $checkpoint.FullName ".checkpoint_description.txt"
                if (Test-Path $descFile) {
                    $desc = Get-Content $descFile -Raw
                    Write-Host "   Description: $desc" -ForegroundColor Gray
                }
                Write-Host ""
            }
            Write-Host "📊 Total: $($checkpoints.Count) checkpoint(s)" -ForegroundColor Magenta
        }
    }
}

function New-DevCheckpoint {
    param([string]$Name, [string]$Desc)
    
    if (-not $Name) {
        $timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
        $Name = "checkpoint_$timestamp"
    }
    
    $checkpointPath = Join-Path $CheckpointsDir $Name
    
    Write-Host "`n🚀 Creating Development Checkpoint: $Name" -ForegroundColor Green
    Write-Host "Description: $Desc" -ForegroundColor Yellow
    Write-Host "Source: piclicks_live_code_17092025 (localhost:8000)" -ForegroundColor Cyan
    
    # Exclude directories that shouldn't be backed up
    $excludeDirs = @(
        "node_modules",
        "vendor",
        "storage\logs",
        "storage\framework\cache",
        "storage\framework\sessions",
        "storage\framework\views",
        ".git"
    )
    
    # Create checkpoint
    Write-Host "`n⏳ Copying files..." -ForegroundColor Yellow
    $robocopyArgs = @($DevPath, $checkpointPath, "/E", "/COPYALL", "/R:0", "/W:0", "/NDL", "/NFL")
    foreach ($dir in $excludeDirs) {
        $robocopyArgs += "/XD"
        $robocopyArgs += $dir
    }
    
    & robocopy @robocopyArgs | Out-Null
    
    if (Test-Path $checkpointPath) {
        # Save description
        if ($Desc) {
            $descFile = Join-Path $checkpointPath ".checkpoint_description.txt"
            Set-Content -Path $descFile -Value $Desc
        }
        
        Write-Host "✅ Checkpoint created successfully!" -ForegroundColor Green
        Write-Host "📍 Location: $checkpointPath" -ForegroundColor Cyan
        
        # Show size
        $size = (Get-ChildItem $checkpointPath -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB
        Write-Host "💾 Size: $([math]::Round($size, 2)) MB" -ForegroundColor Gray
    } else {
        Write-Host "❌ Failed to create checkpoint!" -ForegroundColor Red
    }
}

function Restore-DevCheckpoint {
    param([string]$Name)
    
    $sourcePath = Join-Path $CheckpointsDir $Name
    $backupPath = Join-Path $CheckpointsDir "backup_before_restore_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
    
    Write-Host "`n🔄 Restoring Development Checkpoint: $Name" -ForegroundColor Green
    
    if (-not (Test-Path $sourcePath)) {
        Write-Host "❌ Checkpoint '$Name' not found!" -ForegroundColor Red
        Show-CheckpointList
        return
    }
    
    # Backup current version first
    Write-Host "📦 Backing up current development version..." -ForegroundColor Yellow
    $excludeDirs = @("node_modules", "vendor", "storage\logs", "storage\framework\cache", "storage\framework\sessions", ".git")
    $robocopyArgs = @($DevPath, $backupPath, "/E", "/COPYALL", "/R:0", "/W:0", "/NDL", "/NFL")
    foreach ($dir in $excludeDirs) {
        $robocopyArgs += "/XD"
        $robocopyArgs += $dir
    }
    & robocopy @robocopyArgs | Out-Null
    Write-Host "✅ Backup created: $backupPath" -ForegroundColor Green
    
    # Restore checkpoint (exclude vendor/node_modules as they should be reinstalled)
    Write-Host "🔄 Restoring checkpoint files..." -ForegroundColor Yellow
    robocopy $sourcePath $DevPath /E /COPYALL /R:0 /W:0 /NDL /NFL /XD vendor node_modules | Out-Null
    
    Write-Host "✅ Checkpoint restored successfully!" -ForegroundColor Green
    Write-Host "`n⚠️  Remember to:" -ForegroundColor Yellow
    Write-Host "   1. Run: composer install" -ForegroundColor White
    Write-Host "   2. Run: npm install (if needed)" -ForegroundColor White
    Write-Host "   3. Check .env configuration" -ForegroundColor White
    Write-Host "`n🌐 Test at: http://localhost:8000" -ForegroundColor Cyan
}

function New-QuickSnapshot {
    $timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
    $snapshotPath = Join-Path $CheckpointsDir "snapshot_$timestamp"
    
    Write-Host "`n📸 Creating quick snapshot..." -ForegroundColor Yellow
    
    # Only backup critical directories
    $criticalDirs = @("app", "config", "database", "resources", "routes", "public")
    
    foreach ($dir in $criticalDirs) {
        $source = Join-Path $DevPath $dir
        $dest = Join-Path $snapshotPath $dir
        if (Test-Path $source) {
            robocopy $source $dest /E /COPYALL /R:0 /W:0 /NDL /NFL | Out-Null
        }
    }
    
    # Copy important files
    $files = @(".env", "composer.json", "package.json", "artisan")
    foreach ($file in $files) {
        $source = Join-Path $DevPath $file
        if (Test-Path $source) {
            Copy-Item $source -Destination $snapshotPath -Force
        }
    }
    
    Write-Host "✅ Quick snapshot created: snapshot_$timestamp" -ForegroundColor Green
    Write-Host "💾 This is a lightweight snapshot (code only, no dependencies)" -ForegroundColor Gray
}

# Main execution
Set-Location $RepositoryPath

switch ($Action) {
    "list" {
        Show-CheckpointList
    }
    "create" {
        if (-not $Description) {
            $Description = "Development checkpoint created on $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
        }
        New-DevCheckpoint -Name $CheckpointName -Desc $Description
    }
    "restore" {
        if (-not $CheckpointName) {
            Show-CheckpointList
            Write-Host "`n❌ Please specify checkpoint name with -CheckpointName parameter" -ForegroundColor Red
            Write-Host "Example: .\checkpoint_manager_dev.ps1 -Action restore -CheckpointName 'checkpoint_20250107_123456'" -ForegroundColor Yellow
            return
        }
        Restore-DevCheckpoint -Name $CheckpointName
    }
    "snapshot" {
        New-QuickSnapshot
    }
    "backup" {
        New-DevCheckpoint -Name "backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')" -Desc "Manual backup"
    }
}

Write-Host "`n💡 Tip: Use 'snapshot' for quick code-only backups" -ForegroundColor Cyan
Write-Host "📚 Use 'create' for full checkpoints with descriptions" -ForegroundColor Cyan