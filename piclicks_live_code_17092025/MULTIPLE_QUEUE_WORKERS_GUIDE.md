# Multiple Queue Workers Setup Guide

## Overview

This guide explains how to run multiple Laravel queue workers to handle concurrent print file generation for multiple users.

## Changes Made

### 1. Filename Includes `unique_id` and `tile_id`

**Before:**
- `tile_1734567890_abc123.png`
- `tile_r1_c1_1734567890_abc123.png`

**After:**
- `tile_{unique_id}_{tile_id}_1734567890_abc123.png`
- `tile_{unique_id}_{tile_id}_r1_c1_1734567890_abc123.png`

**Benefits:**
- ✅ Easy to identify which collage a file belongs to
- ✅ Files are organized by collage
- ✅ No risk of file mixing between collages
- ✅ Easier debugging and file management

### 2. Multiple Queue Workers

All workers use the **same code**, so any fixes to the renderer automatically affect all workers.

## How to Use Multiple Workers

### Option 1: Using PowerShell Scripts (Recommended)

#### Start Workers

```powershell
# Start 3 workers (default)
.\start_queue_workers.ps1

# Start 5 workers
.\start_queue_workers.ps1 -WorkerCount 5

# Start workers for a specific queue
.\start_queue_workers.ps1 -WorkerCount 3 -QueueName "high-priority"
```

#### Stop Workers

```powershell
.\stop_queue_workers.ps1
```

### Option 2: Manual Start (For Testing)

Open multiple PowerShell windows and run in each:

```powershell
cd "C:\Users\Sveta\Dropbox\avsvet-home\avsha projects\Uroko\Uroko 2024\Piclicks app\Avsha dev cursor\piclicks_live_code_17092025"
C:\xampp\php\php.exe artisan queue:work --tries=3 --timeout=600
```

### Option 3: Using Supervisor (Production - Linux)

For production servers, use Supervisor to manage workers:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --timeout=600
autostart=true
autorestart=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

## How It Works

### Queue Processing

1. **User saves collage** → Job added to queue
2. **Worker picks up job** → Processes print file generation
3. **Multiple workers** → Process multiple jobs simultaneously

### Example Flow

```
User 1 saves → Job 1 → Worker 1 processes
User 2 saves → Job 2 → Worker 2 processes  
User 3 saves → Job 3 → Worker 3 processes
User 4 saves → Job 4 → Queued (waits for available worker)
```

### File Safety

- ✅ Each collage has unique `unique_id`
- ✅ Files include `unique_id` in filename
- ✅ Database tracks file paths
- ✅ Old files are deleted before new ones are created
- ✅ No risk of file mixing or overwriting

## Configuration

### Worker Settings

- **Timeout**: 600 seconds (10 minutes) - enough for large collages
- **Tries**: 3 attempts - retries on failure
- **Queue**: `default` - can create priority queues if needed

### Recommended Worker Count

- **Low traffic** (< 10 users/hour): 2-3 workers
- **Medium traffic** (10-50 users/hour): 3-5 workers
- **High traffic** (> 50 users/hour): 5-10 workers

**Note**: Each worker uses memory (~100-200MB per worker). Monitor server resources.

## Monitoring

### Check Queue Status

```powershell
# Check if workers are running
Get-Process | Where-Object { $_.MainWindowTitle -like "*Queue Worker*" }

# Check queue length (if using database queue)
C:\xampp\php\php.exe artisan queue:monitor
```

### View Logs

```powershell
# Watch logs in real-time
Get-Content storage\logs\laravel.log -Wait -Tail 50
```

### Check for Errors

Look for these in logs:
- `GeneratePrintFilesJob failed` - Job processing error
- `Failed to save tile` - File save error
- `Skipping tile - image_edited not found` - Missing source image

## Troubleshooting

### Workers Not Processing

1. **Check if workers are running:**
   ```powershell
   Get-Process | Where-Object { $_.MainWindowTitle -like "*Queue Worker*" }
   ```

2. **Check queue connection:**
   - Verify `.env` has correct `QUEUE_CONNECTION` (usually `database` or `sync`)

3. **Check for errors:**
   ```powershell
   Get-Content storage\logs\laravel.log -Tail 100 | Select-String "error" -Context 2
   ```

### High Memory Usage

- Reduce worker count
- Increase PHP memory limit in `php.ini`
- Monitor with Task Manager

### Jobs Stuck in Queue

- Check for failed jobs: `C:\xampp\php\php.exe artisan queue:failed`
- Retry failed jobs: `C:\xampp\php\php.exe artisan queue:retry all`
- Clear failed jobs: `C:\xampp\php\php.exe artisan queue:flush`

## Production Recommendations

1. **Use Supervisor** (Linux) or **Windows Service** (Windows) to auto-restart workers
2. **Monitor queue length** - alert if queue grows too large
3. **Set up logging** - track job processing times
4. **Create priority queues** - separate urgent jobs from regular ones
5. **Set up alerts** - notify on job failures

## Files Modified

1. `app/Services/PrintFileService.php` - Updated `saveTile()` to include `unique_id` and `tile_id` in filename
2. `app/Services/CollageServices.php` - Added `unique_id` and `tile_id` to `blockConfig`
3. `app/Http/Controllers/Admin/OrderController.php` - Added `unique_id` and `tile_id` to `blockConfig`

## Testing

1. **Test filename format:**
   - Save a collage
   - Check generated files in `public/storage/designCollageImages/`
   - Verify filenames include `unique_id` and `tile_id`

2. **Test multiple workers:**
   - Start 3 workers
   - Save 5 collages quickly
   - Verify all process without waiting

3. **Test file safety:**
   - Save multiple collages simultaneously
   - Verify files don't mix or overwrite
   - Check database `image_with_bleed` paths are correct

