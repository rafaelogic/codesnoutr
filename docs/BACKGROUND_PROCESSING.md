# Fix All Background Processing

This document explains the background processing system implemented for the "Fix All" functionality to prevent 30-second timeout errors.

## Overview

The Fix All feature has been moved to a background job processing system that prevents timeout errors during long-running operations. When users click "Fix All", instead of processing issues synchronously, the system:

1. **Creates a background job** with a unique session ID
2. **Redirects to a progress page** that shows real-time updates
3. **Processes issues asynchronously** in the background
4. **Tracks progress in cache** for real-time monitoring

## System Architecture

### Files Created/Modified

#### 1. Background Job (`src/Jobs/FixAllIssuesJob.php`)
- **Purpose**: Processes up to 50 issues in the background
- **Features**: 
  - Session-based progress tracking
  - Real-time cache updates
  - AI usage tracking integration
  - Comprehensive error handling
  - Detailed results logging

#### 2. Progress Livewire Component (`src/Livewire/FixAllProgress.php`)
- **Purpose**: Real-time progress monitoring interface
- **Features**:
  - Auto-refresh every 3 seconds
  - Progress percentage calculation
  - Current file display
  - Results list with status indicators
  - Error handling and display

#### 3. Progress View Template (`resources/views/livewire/fix-all-progress.blade.php`)
- **Purpose**: User interface for progress monitoring
- **Features**:
  - Progress bars with percentage
  - Current file processing indicator
  - Real-time results list
  - Error display
  - Status indicators with colors

#### 4. Route Configuration (`routes/web.php`)
- **Added**: `/codesnoutr/fix-all/{sessionId}` route
- **Purpose**: Serves the progress monitoring page

#### 5. Controller Method (`src/Http/Controllers/DashboardController.php`)
- **Added**: `fixAllProgress()` method
- **Purpose**: Renders the progress page for specific session

#### 6. Dashboard Component (`src/Livewire/Dashboard.php`)
- **Modified**: `fixAllIssues()` method
- **Change**: Now redirects to progress page instead of inline processing

#### 7. Testing Commands
- **`TestFixAllCommand`**: Test the background job functionality
- **`MonitorFixAllCommand`**: Monitor job progress from CLI

## How It Works

### 1. User Interaction Flow
```
User clicks "Fix All" 
    ↓
Dashboard validates selection
    ↓
Generates unique session ID
    ↓
Dispatches FixAllIssuesJob with session ID
    ↓
Redirects to progress page (/codesnoutr/fix-all/{sessionId})
    ↓
Progress page auto-refreshes showing real-time updates
```

### 2. Background Processing Flow
```
FixAllIssuesJob starts
    ↓
Updates cache: status = "processing"
    ↓
For each issue (up to 50):
    ├── Update current_file in cache
    ├── Process issue through IssueActionInvoker
    ├── Track AI API usage and costs
    ├── Add result to cache
    └── Update processed_issues count
    ↓
Updates cache: status = "completed"
```

### 3. Progress Tracking System
- **Cache Key**: `fix_all_progress_{sessionId}`
- **Cache Duration**: 2 hours
- **Update Frequency**: Every issue processed
- **UI Refresh**: Every 3 seconds via Livewire polling

### 4. Progress Data Structure
```php
[
    'status' => 'processing|completed|error',
    'total_issues' => int,
    'processed_issues' => int,
    'current_file' => string|null,
    'results' => [
        [
            'issue_id' => int,
            'success' => bool,
            'message' => string,
            'ai_cost' => float|null
        ]
    ],
    'errors' => string[],
    'started_at' => ISO8601,
    'completed_at' => ISO8601|null,
]
```

## Configuration

### Queue Settings (config/codesnoutr.php)
```php
'queue' => [
    'enabled' => env('CODESNOUTR_QUEUE_ENABLED', true),
    'name' => env('CODESNOUTR_QUEUE_NAME', 'default'),
    'connection' => env('CODESNOUTR_QUEUE_CONNECTION', config('queue.default')),
    'timeout' => env('CODESNOUTR_QUEUE_TIMEOUT', 300),
    'memory' => env('CODESNOUTR_QUEUE_MEMORY', 512),
    'tries' => env('CODESNOUTR_QUEUE_TRIES', 3),
],
```

### Environment Variables
```env
# Enable queue processing
CODESNOUTR_QUEUE_ENABLED=true

# Queue configuration
QUEUE_CONNECTION=database
CODESNOUTR_QUEUE_CONNECTION=database
CODESNOUTR_QUEUE_TIMEOUT=300
```

## Testing

### 1. Command Line Testing
```bash
# Test the Fix All job functionality
php artisan codesnoutr:test-fix-all --session-id=test-123

# Monitor progress
php artisan codesnoutr:monitor-fix-all test-123

# Watch progress in real-time
php artisan codesnoutr:monitor-fix-all test-123 --watch
```

### 2. Manual Testing
1. Ensure you have unfixed issues in the database
2. Go to CodeSnoutr Dashboard
3. Select multiple issues 
4. Click "Fix All"
5. You should be redirected to `/codesnoutr/fix-all/{sessionId}`
6. Monitor the progress page for real-time updates

## Queue Requirements

### Laravel Application Setup
In your Laravel application that uses this package:

1. **Configure Queue Driver**:
```env
QUEUE_CONNECTION=database
```

2. **Run Queue Migration**:
```bash
php artisan queue:table
php artisan migrate
```

3. **Start Queue Worker**:
```bash
php artisan queue:work
```

### Production Deployment
For production environments:

1. **Use Supervisor for Queue Workers**:
```ini
[program:codesnoutr-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/path/to/your/app
autostart=true
autorestart=true
numprocs=2
```

2. **Monitor Queue Status**:
```bash
php artisan queue:work --verbose
php artisan horizon:status  # if using Horizon
```

## Error Handling

### Common Issues and Solutions

1. **Jobs Not Processing**
   - Check queue worker is running: `php artisan queue:work`
   - Verify queue configuration
   - Check failed jobs: `php artisan queue:failed`

2. **Progress Not Updating**
   - Verify cache is working
   - Check Livewire polling is active
   - Ensure session ID matches

3. **Timeout Errors Still Occurring**
   - Increase job timeout in config
   - Reduce batch size (currently 50 issues max)
   - Check memory limits

## Monitoring and Debugging

### Cache Inspection
```php
// Check progress data
$progress = Cache::get("fix_all_progress_{$sessionId}");
dd($progress);
```

### Log Files
The system logs progress and errors to Laravel's default log channel:
- Job start/completion
- Individual issue processing
- Error conditions
- AI API usage and costs

### Queue Dashboard
Consider using Laravel Horizon for advanced queue monitoring in production environments.

## Performance Considerations

- **Batch Size**: Currently limited to 50 issues per job
- **Memory Usage**: Configured for 512MB per job
- **Timeout**: 5 minutes per job (300 seconds)
- **Cache TTL**: 2 hours for progress data
- **UI Polling**: Every 3 seconds for real-time updates

## Future Enhancements

1. **Chunked Processing**: Split large jobs into smaller chunks
2. **Resume Capability**: Allow resuming failed/interrupted jobs
3. **Priority Queue**: Process critical issues first
4. **Notification System**: Email/Slack notifications on completion
5. **Progress History**: Store completed job results for later review