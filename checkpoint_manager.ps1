# PicLicks Checkpoint Manager
# PowerShell script for managing application checkpoints

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("create", "restore", "list", "backup")]
    [string]$Action,
    
    [Parameter(Mandatory=$false)]
    [string]$CheckpointNumber,
    
    [Parameter(Mandatory=$false)]
    [string]$Description
)

# Configuration
$RepositoryPath = "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor"
$XamppPath = "C:\xampp\htdocs\piclicks"
$RegistryFile = "CHECKPOINT_REGISTRY.md"

function Get-NextCheckpointNumber {
    $checkpoints = Get-ChildItem -Path $RepositoryPath -Directory -Name "checkpoint_*" | Sort-Object
    if ($checkpoints.Count -eq 0) {
        return "002"
    }
    $lastNumber = ($checkpoints[-1] -replace "checkpoint_", "").TrimStart("0")
    $nextNumber = [int]$lastNumber + 1
    return $nextNumber.ToString("000")
}

function Show-CheckpointList {
    Write-Host "`n📋 Available Checkpoints:" -ForegroundColor Cyan
    Write-Host "=========================" -ForegroundColor Cyan
    
    $checkpoints = Get-ChildItem -Path $RepositoryPath -Directory -Name "checkpoint_*" | Sort-Object
    $reference = Get-ChildItem -Path $RepositoryPath -Directory -Name "reference_broken_version*"
    $working = Get-ChildItem -Path $RepositoryPath -Directory -Name "working_piclicks_app*"
    
    if ($working) {
        Write-Host "✅ working_piclicks_app    - Current working version" -ForegroundColor Green
    }
    
    if ($reference) {
        Write-Host "📦 reference_broken_version - Original broken version" -ForegroundColor Yellow
    }
    
    foreach ($checkpoint in $checkpoints) {
        $number = $checkpoint -replace "checkpoint_", ""
        Write-Host "🔧 $checkpoint    - Checkpoint #$number" -ForegroundColor Blue
    }
    
    Write-Host "`n📊 Total Checkpoints: $($checkpoints.Count + ($working ? 1 : 0) + ($reference ? 1 : 0))" -ForegroundColor Magenta
}

function New-Checkpoint {
    param([string]$Number, [string]$Desc)
    
    $checkpointPath = Join-Path $RepositoryPath "checkpoint_$Number"
    
    Write-Host "`n🚀 Creating Checkpoint #$Number..." -ForegroundColor Green
    Write-Host "Description: $Desc" -ForegroundColor Yellow
    
    # Create checkpoint directory
    robocopy $XamppPath $checkpointPath /E /COPYALL /XD .git node_modules vendor storage/logs /R:0 /W:0
    
    if (Test-Path $checkpointPath) {
        Write-Host "✅ Checkpoint created successfully!" -ForegroundColor Green
        
        # Add to Git
        Set-Location $RepositoryPath
        git add "checkpoint_$Number/"
        git commit -m "CHECKPOINT #$Number`: $Desc"
        
        Write-Host "✅ Checkpoint committed to Git!" -ForegroundColor Green
        Write-Host "`n📍 Checkpoint Location: $checkpointPath" -ForegroundColor Cyan
    } else {
        Write-Host "❌ Failed to create checkpoint!" -ForegroundColor Red
    }
}

function Restore-Checkpoint {
    param([string]$CheckpointName)
    
    $sourcePath = Join-Path $RepositoryPath $CheckpointName
    $backupPath = Join-Path $RepositoryPath "backup_$(Get-Date -Format 'yyyyMMdd_HHmm')"
    
    Write-Host "`n🔄 Restoring Checkpoint: $CheckpointName" -ForegroundColor Green
    
    if (-not (Test-Path $sourcePath)) {
        Write-Host "❌ Checkpoint '$CheckpointName' not found!" -ForegroundColor Red
        return
    }
    
    # Backup current version first
    if (Test-Path $XamppPath) {
        Write-Host "📦 Backing up current version..." -ForegroundColor Yellow
        robocopy $XamppPath $backupPath /E /COPYALL /XD .git node_modules vendor storage/logs /R:0 /W:0
        Write-Host "✅ Backup created: $backupPath" -ForegroundColor Green
    }
    
    # Restore checkpoint
    Write-Host "🔄 Restoring checkpoint..." -ForegroundColor Yellow
    robocopy $sourcePath $XamppPath /E /COPYALL /R:0 /W:0
    
    Write-Host "✅ Checkpoint restored successfully!" -ForegroundColor Green
    Write-Host "`n🌐 Test your application at:" -ForegroundColor Cyan
    Write-Host "   Main: http://localhost/piclicks" -ForegroundColor White
    Write-Host "   Admin: http://localhost/piclicks/admin-panel" -ForegroundColor White
}

function Backup-Current {
    $backupPath = Join-Path $RepositoryPath "backup_$(Get-Date -Format 'yyyyMMdd_HHmm')"
    
    Write-Host "`n📦 Creating backup of current version..." -ForegroundColor Yellow
    
    if (Test-Path $XamppPath) {
        robocopy $XamppPath $backupPath /E /COPYALL /XD .git node_modules vendor storage/logs /R:0 /W:0
        Write-Host "✅ Backup created: $backupPath" -ForegroundColor Green
    } else {
        Write-Host "❌ No current version found at: $XamppPath" -ForegroundColor Red
    }
}

# Main execution
Set-Location $RepositoryPath

switch ($Action) {
    "list" {
        Show-CheckpointList
    }
    "create" {
        if (-not $CheckpointNumber) {
            $CheckpointNumber = Get-NextCheckpointNumber
        }
        if (-not $Description) {
            $Description = "Checkpoint #$CheckpointNumber created on $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
        }
        New-Checkpoint -Number $CheckpointNumber -Desc $Description
    }
    "restore" {
        if (-not $CheckpointNumber) {
            Show-CheckpointList
            Write-Host "`n❌ Please specify checkpoint number with -CheckpointNumber parameter" -ForegroundColor Red
            Write-Host "Example: .\checkpoint_manager.ps1 -Action restore -CheckpointNumber 'working_piclicks_app'" -ForegroundColor Yellow
            return
        }
        Restore-Checkpoint -CheckpointName $CheckpointNumber
    }
    "backup" {
        Backup-Current
    }
}

Write-Host "`n📚 For detailed instructions, see: CHECKPOINT_REGISTRY.md" -ForegroundColor Cyan
