# Queue Worker Testing Guide

## The Problem

You ran `php artisan queue:work` from the **package directory**, but packages don't have their own Laravel application context. The queue worker needs to run from a **Laravel application** that has this package installed.

## Solution: Set Up a Test Laravel Application

### Step 1: Navigate to Your Laravel Application

This package needs to be installed in a Laravel application. You have two options:

#### Option A: Use an Existing Laravel App

```bash
# Navigate to your Laravel application root
cd /Users/rafaelogic/Desktop/projects/laravel/your-laravel-app

# Verify you're in the right place (should have artisan file)
ls artisan
```

#### Option B: Create a New Test Laravel App

```bash
# Navigate to projects directory
cd /Users/rafaelogic/Desktop/projects/laravel

# Create new Laravel app
composer create-project laravel/laravel codesnoutr-test
cd codesnoutr-test

# Link your local package
# Edit composer.json and add:
```

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../packages/codesnoutr"
        }
    ],
    "require": {
        "rafaelogic/codesnoutr": "dev-main"
    }
}
```

```bash
# Install the package
composer update rafaelogic/codesnoutr
```

---

## Step 2: Configure the Laravel Application

### 1. Set Up Environment

```bash
# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database (choose one):
```

**Option A: SQLite (Simplest)**
```env
# .env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

```bash
# Create database
touch database/database.sqlite
```

**Option B: MySQL**
```env
# .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=codesnoutr_test
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Configure Queue

```env
# .env
QUEUE_CONNECTION=database
```

### 3. Run Migrations

```bash
# Run Laravel migrations
php artisan migrate

# Run queue table migration
php artisan queue:table
php artisan migrate

# Run CodeSnoutr migrations (if using published migrations)
php artisan migrate
```

---

## Step 3: Verify Setup

### Check Database Tables

```bash
php artisan tinker
```

```php
// In tinker:
DB::table('jobs')->count(); // Should return 0
DB::table('failed_jobs')->count(); // Should return 0
exit
```

### Check CodeSnoutr is Installed

```bash
php artisan route:list | grep codesnoutr
# Should show CodeSnoutr routes

php artisan vendor:publish --tag=codesnoutr-config
# Should publish config file
```

---

## Step 4: Test Queue Functionality

### Method 1: Using the Fix All Feature

1. **Start Laravel development server**:
```bash
php artisan serve
```

2. **In a new terminal, start queue worker**:
```bash
cd /path/to/your/laravel/app
php artisan queue:work --tries=3 --timeout=300
```

3. **Access the application**:
   - Open browser: http://localhost:8000/codesnoutr
   - Click "Fix All" button
   - Watch the queue worker terminal for job processing

### Method 2: Dispatch Test Job Manually

Create a test script:

```bash
php artisan tinker
```

```php
// In tinker:
use Rafaelogic\CodeSnoutr\Jobs\FixAllIssuesJob;
use Rafaelogic\CodeSnoutr\Models\Issue;

// Create some test issues
$issues = Issue::factory()->count(5)->create(['fixed' => false]);

// Dispatch the job
$sessionId = uniqid('test_');
FixAllIssuesJob::dispatch($sessionId, $issues->pluck('id')->toArray())
    ->onQueue('default');

// Check if job was queued
DB::table('jobs')->count(); // Should be 1

exit
```

Now in your queue worker terminal, you should see the job being processed.

### Method 3: Use Sync Queue for Immediate Testing

If you want to test without running a queue worker:

```env
# .env
QUEUE_CONNECTION=sync
```

Now jobs will run immediately when dispatched (synchronously).

---

## Step 5: Monitor Queue Worker

### Terminal 1: Queue Worker
```bash
php artisan queue:work --verbose --tries=3
```

**What to look for**:
- `[YYYY-MM-DD HH:MM:SS][queue-id] Processing: App\Jobs\FixAllIssuesJob`
- `[YYYY-MM-DD HH:MM:SS][queue-id] Processed: App\Jobs\FixAllIssuesJob`

### Terminal 2: Queue Status
```bash
# Watch queue in real-time
watch -n 1 'php artisan queue:failed && echo "---" && php artisan tinker --execute="echo DB::table(\"jobs\")->count() . \" jobs pending\n\";"'
```

---

## Troubleshooting

### Issue: "Nothing happens" when running queue:work

**Diagnosis**:
```bash
# Check if jobs are in the queue
php artisan tinker --execute="DB::table('jobs')->count()"

# If 0: No jobs are being dispatched
# If >0: Jobs are queued but not processing
```

**Solutions**:

1. **No jobs in queue** → Jobs aren't being dispatched
   ```bash
   # Check if Fix All is actually dispatching jobs
   # Add logging to FixAllProgress.php startFixAll() method
   ```

2. **Jobs in queue but not processing** → Worker issue
   ```bash
   # Stop all queue workers
   pkill -f "queue:work"
   
   # Clear cache
   php artisan cache:clear
   php artisan config:clear
   
   # Restart worker with verbose output
   php artisan queue:work --verbose --tries=3
   ```

3. **Jobs failing silently** → Check failed jobs
   ```bash
   php artisan queue:failed
   
   # Retry failed jobs
   php artisan queue:retry all
   ```

### Issue: Jobs timeout

```bash
# Increase timeout
php artisan queue:work --timeout=600
```

### Issue: Memory leaks

```bash
# Process only 100 jobs then restart
php artisan queue:work --max-jobs=100

# Or use memory limit
php artisan queue:work --memory=512
```

---

## Production Setup

### Using Supervisor (Recommended)

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

# Check status
sudo supervisorctl status
```

---

## Quick Diagnostics Script

Save this as `check_queue.sh` in your Laravel app root:

```bash
#!/bin/bash

echo "=== Queue Diagnostics ==="
echo ""

echo "1. Queue Configuration:"
php artisan tinker --execute="echo 'Connection: ' . config('queue.default') . PHP_EOL;"

echo ""
echo "2. Pending Jobs:"
php artisan tinker --execute="echo 'Count: ' . DB::table('jobs')->count() . PHP_EOL;"

echo ""
echo "3. Failed Jobs:"
php artisan queue:failed

echo ""
echo "4. Recent Job Activity:"
php artisan tinker --execute="
\$recent = DB::table('jobs')->orderBy('created_at', 'desc')->limit(3)->get(['id', 'queue', 'attempts', 'created_at']);
foreach (\$recent as \$job) {
    echo 'Job #' . \$job->id . ' - Attempts: ' . \$job->attempts . ' - Created: ' . date('Y-m-d H:i:s', \$job->created_at) . PHP_EOL;
}
"

echo ""
echo "5. Queue Worker Processes:"
ps aux | grep "queue:work" | grep -v grep

echo ""
echo "=== Recommendations ==="
echo ""

if [ -z "$(ps aux | grep 'queue:work' | grep -v grep)" ]; then
    echo "⚠️  No queue worker running!"
    echo "   Start with: php artisan queue:work"
fi
```

```bash
# Make executable
chmod +x check_queue.sh

# Run it
./check_queue.sh
```

---

## Summary

**To test the CodeSnoutr queue functionality:**

1. ✅ Navigate to a Laravel application (not the package directory)
2. ✅ Ensure database is set up with migrations
3. ✅ Set `QUEUE_CONNECTION=database` in `.env`
4. ✅ Run `php artisan queue:table && php artisan migrate`
5. ✅ Start queue worker: `php artisan queue:work`
6. ✅ Test Fix All feature in browser
7. ✅ Watch queue worker terminal for job processing

**The key issue**: You were in `/packages/codesnoutr` (package directory) instead of a Laravel application root directory. Packages don't have `artisan` - only Laravel applications do.

**Quick Test**:
```bash
# Are you in the right place?
ls artisan  # Should exist in Laravel app
ls vendor/rafaelogic/codesnoutr  # Should exist if package is installed
```

If you don't have a Laravel app yet, create one with the instructions above!
