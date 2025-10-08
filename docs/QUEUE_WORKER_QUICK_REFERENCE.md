# Queue Worker Protection - Quick Reference

## TL;DR

CodeSnoutr now **prevents Fix All jobs from being dispatched** when the queue worker is not running. This saves AI tokens and prevents user confusion.

## Quick Commands

### Check if Queue Worker is Running
```bash
# Run the test script
php test_queue_worker_detection.php

# Or manually check
ps aux | grep queue:work  # macOS/Linux
tasklist /FI "IMAGENAME eq php.exe"  # Windows
```

### Start Queue Worker
```bash
# Basic
php artisan queue:work

# With verbose output
php artisan queue:work --verbose

# With specific queue and timeout
php artisan queue:work --queue=default --timeout=300
```

### Restart Queue Worker
```bash
php artisan queue:restart
```

## What Users Will See

### ❌ Without Queue Worker
**Browser Alert:**
```
Error

Queue worker is not running! 
Start it with: php artisan queue:work

[Got it]
```

**UI Status:**
```
Status: failed
Message: Cannot start Fix All: Queue worker is not running. 
         Please start the queue worker with: php artisan queue:work
```

### ✅ With Queue Worker
```
Status: processing
Progress: 1/180
Current file: UserController.php
Fixed: 0 | Failed: 0
```

## When Check Happens

| Queue Mode | Check Performed? | Why |
|------------|------------------|-----|
| `sync` | ❌ No | Runs immediately, no worker needed |
| `database` | ✅ Yes | Requires worker for background processing |
| `redis` | ✅ Yes | Requires worker for background processing |
| `sqs` | ✅ Yes | Requires worker for background processing |
| Debug mode | ❌ No | Forces sync execution |

## Configuration

### .env Settings

**For Development (No Queue Needed):**
```env
QUEUE_CONNECTION=sync
```

**For Production (Queue Worker Required):**
```env
QUEUE_CONNECTION=database
# or
QUEUE_CONNECTION=redis
```

**Force Sync Mode for Testing:**
```env
CODESNOUTR_DEBUG_SYNC_JOBS=true
```

## Troubleshooting

### "Queue worker not detected" but it IS running

**Possible causes:**
1. Worker on different server
2. Process detection failing
3. Wrong queue name

**Solutions:**
```bash
# Check if actually running
ps aux | grep queue:work

# Switch to sync mode temporarily
echo "QUEUE_CONNECTION=sync" >> .env
php artisan config:clear

# Enable debug sync mode
echo "CODESNOUTR_DEBUG_SYNC_JOBS=true" >> .env
php artisan config:clear
```

### Queue worker keeps stopping

**Use Supervisor (Production):**
```ini
[program:laravel-worker]
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
```

**Or use systemd:**
```ini
[Unit]
Description=Laravel Queue Worker

[Service]
User=www-data
ExecStart=/usr/bin/php /path/to/artisan queue:work

[Install]
WantedBy=multi-user.target
```

### Jobs stuck in queue

```bash
# Check jobs in queue
php artisan tinker
>>> DB::table('jobs')->count();
>>> DB::table('jobs')->get();

# Clear stuck jobs (CAREFUL!)
>>> DB::table('jobs')->truncate();

# Restart worker
php artisan queue:restart
```

## Benefits Summary

| Benefit | Before | After |
|---------|--------|-------|
| **AI Tokens** | Wasted on unprocessed jobs | Saved, no dispatch without worker |
| **User Feedback** | Stuck at "processing" | Immediate error message |
| **Debugging** | Confusing, unclear issue | Clear logs and errors |
| **Queue Table** | Bloats with stuck jobs | Clean, only active jobs |
| **User Experience** | Frustrating, unclear | Clear, actionable instructions |

## Testing Procedure

### Test 1: Verify Detection Works
```bash
# 1. Stop queue worker
ps aux | grep queue:work
kill <PID>

# 2. Run test script
php test_queue_worker_detection.php
# Expected: "QUEUE WORKER NOT DETECTED"

# 3. Start queue worker
php artisan queue:work &

# 4. Run test again
php test_queue_worker_detection.php
# Expected: "QUEUE WORKER IS RUNNING"
```

### Test 2: Verify UI Blocking
```bash
# 1. Stop queue worker
killall queue:work  # or kill <PID>

# 2. In browser, try to start Fix All
# Expected: Error alert, status = failed

# 3. Start queue worker
php artisan queue:work --verbose

# 4. Try Fix All again
# Expected: Success, progress updates
```

## Logs to Monitor

```bash
# Watch for blocked dispatches
tail -f storage/logs/laravel.log | grep "Cannot dispatch job"

# Watch for queue worker checks
tail -f storage/logs/laravel.log | grep "Queue worker"

# Watch for progress updates
tail -f storage/logs/laravel.log | grep "Progress updated"
```

## Documentation Links

- **Full Documentation:** [QUEUE_WORKER_PROTECTION.md](QUEUE_WORKER_PROTECTION.md)
- **Implementation Summary:** [QUEUE_WORKER_PROTECTION_SUMMARY.md](QUEUE_WORKER_PROTECTION_SUMMARY.md)
- **Main README:** [README.md](README.md#queue-worker-issues)
- **Changelog:** [CHANGELOG.md](CHANGELOG.md)

## Support

If you encounter issues:

1. **Run test script:** `php test_queue_worker_detection.php`
2. **Check logs:** `tail -f storage/logs/laravel.log`
3. **Verify config:** `php artisan config:show queue`
4. **Test manually:** `php artisan queue:work --once`
5. **Check documentation:** See links above

---

**Version:** 1.0.3-dev  
**Last Updated:** October 5, 2025  
**Status:** ✅ Production Ready
