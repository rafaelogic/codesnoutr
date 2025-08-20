# Queue Management

CodeSnoutr includes intelligent queue management functionality that automatically checks if Laravel queues are running and starts them if needed when performing scans.

## Features

### Automatic Queue Detection
- Checks if queue workers are running before starting a scan
- Supports different queue drivers (sync, database, redis, sqs)
- Displays real-time queue status in the UI

### Auto-Start Functionality
- Automatically starts queue workers if none are running
- Configurable auto-start behavior
- Graceful fallback for different environments

### Real-Time Monitoring
- Live queue status updates
- Process monitoring and statistics
- Queue performance metrics

## Configuration

Add these settings to your `config/codesnoutr.php`:

```php
'queue' => [
    'enabled' => env('CODESNOUTR_QUEUE_ENABLED', true),
    'name' => env('CODESNOUTR_QUEUE_NAME', 'default'),
    'connection' => env('CODESNOUTR_QUEUE_CONNECTION', config('queue.default')),
    'timeout' => env('CODESNOUTR_QUEUE_TIMEOUT', 300),
    'memory' => env('CODESNOUTR_QUEUE_MEMORY', 512),
    'sleep' => env('CODESNOUTR_QUEUE_SLEEP', 3),
    'tries' => env('CODESNOUTR_QUEUE_TRIES', 3),
    'auto_start' => env('CODESNOUTR_QUEUE_AUTO_START', true),
    'monitor_interval' => env('CODESNOUTR_QUEUE_MONITOR_INTERVAL', 30), // seconds
],
```

## Environment Variables

```bash
# Enable/disable queue management
CODESNOUTR_QUEUE_ENABLED=true

# Queue configuration
CODESNOUTR_QUEUE_NAME=default
CODESNOUTR_QUEUE_CONNECTION=database
CODESNOUTR_QUEUE_TIMEOUT=300
CODESNOUTR_QUEUE_MEMORY=512
CODESNOUTR_QUEUE_SLEEP=3
CODESNOUTR_QUEUE_TRIES=3

# Auto-start behavior
CODESNOUTR_QUEUE_AUTO_START=true

# Monitoring interval (seconds)
CODESNOUTR_QUEUE_MONITOR_INTERVAL=30
```

## How It Works

### 1. Queue Check Process

When you start a scan, CodeSnoutr:

1. **Checks Queue Status**: Verifies if queue workers are running using `ps aux | grep 'queue:work'`
2. **Driver Detection**: Identifies the current queue driver (sync, database, redis, etc.)
3. **Auto-Start Decision**: If no workers are found and auto-start is enabled, starts a new worker
4. **Progress Display**: Shows the queue checking process with real-time status updates

### 2. Queue Status Display

The scan form shows different states:

- **Checking Queue**: Blue progress bar while verifying queue status
- **Queue Ready**: Green confirmation when queue is operational
- **Queue Error**: Red alert if queue setup fails
- **Queue Skipped**: Gray status when auto-start is disabled

### 3. Background Processing

Once the queue is confirmed running:

1. Scan job is dispatched to the queue
2. Progress is tracked via cache
3. Real-time updates are pushed to the UI
4. Results are processed when complete

## UI Components

### Scan Form Queue Status

The scan form includes a queue status section that displays:

```blade
@if($isCheckingQueue)
<!-- Queue Status Check -->
<div class="queue-status-check">
    <h3>Preparing Scan</h3>
    <p>{{ $queueMessage }}</p>
    <span class="status-badge {{ $badge['class'] }}">
        {{ $badge['text'] }}
    </span>
</div>
@endif
```

### Dashboard Queue Widget

Include the queue status widget in your dashboard:

```blade
<livewire:queue-status />
```

This provides:
- Real-time queue status
- Worker process monitoring
- Queue statistics
- Start/stop controls
- Configuration display

## API Usage

### QueueService Methods

```php
use Rafaelogic\CodeSnoutr\Services\QueueService;

$queueService = app(QueueService::class);

// Check if queue is running
$status = $queueService->isQueueRunning();
// Returns: ['is_running' => bool, 'process_count' => int, 'driver' => string, ...]

// Ensure queue is running (auto-start if needed)
$result = $queueService->ensureQueueIsRunning();
// Returns: ['success' => bool, 'message' => string, 'action_taken' => string]

// Get queue statistics
$stats = $queueService->getQueueStats();
// Returns: ['driver' => string, 'pending_jobs' => int, 'failed_jobs' => int, ...]

// Get monitoring data for UI
$monitoring = $queueService->getMonitoringData();
// Returns: comprehensive status data for dashboard display

// Stop all queue workers
$result = $queueService->stopQueueWorkers();
// Returns: ['success' => bool, 'stopped_processes' => int, 'message' => string]
```

### Livewire Integration

```php
// In your Livewire component
public function checkAndStartQueue()
{
    $this->isCheckingQueue = true;
    $this->queueStatus = 'checking';
    $this->queueMessage = 'Checking if queue is running...';

    $queueService = app(QueueService::class);
    $result = $queueService->ensureQueueIsRunning();
    
    if ($result['success']) {
        $this->queueStatus = 'ready';
        $this->queueMessage = $result['message'];
        $this->dispatch('queue-ready');
    } else {
        $this->queueStatus = 'error';
        $this->queueMessage = $result['message'];
        $this->addError('queue', $result['message']);
    }
    
    $this->isCheckingQueue = false;
}
```

## Error Handling

### Common Issues

1. **Permission Errors**: Ensure the web server has permission to execute `ps` and `kill` commands
2. **Process Detection**: Some systems may require different process detection methods
3. **Auto-Start Failures**: Check if the application has permission to start background processes

### Error Messages

- `"Failed to check queue status"`: Process detection failed
- `"Failed to start queue worker"`: Auto-start command failed
- `"Queue setup failed"`: General queue management error

### Debugging

Enable debug logging by adding to your `.env`:

```bash
LOG_LEVEL=debug
```

Queue management operations are logged to help diagnose issues.

## Performance Considerations

### Caching

Queue status is cached for 30 seconds to reduce system overhead:

```php
// Status is cached automatically
$status = $queueService->getCachedQueueStatus();

// Clear cache if needed
$queueService->clearQueueStatusCache();
```

### Monitoring Interval

Configure the monitoring interval based on your needs:

- **High-frequency scans**: 15-30 seconds
- **Normal usage**: 30-60 seconds  
- **Low-frequency scans**: 60+ seconds

## Security Considerations

### Process Management

- Queue workers run with the same permissions as the web server
- Process detection uses standard Unix commands (`ps`, `grep`)
- Auto-start functionality requires process creation permissions

### Resource Limits

Configure appropriate limits:

```php
'queue' => [
    'timeout' => 300,        // 5 minutes max per job
    'memory' => 512,         // 512MB memory limit
    'tries' => 3,           // Maximum retry attempts
    'sleep' => 3,           // Sleep between job checks
],
```

## Testing

Run the queue management test:

```bash
php tests/queue-test.php
```

This verifies:
- ✅ QueueService class loading
- ✅ Process detection functionality
- ✅ Configuration access
- ✅ Basic queue operations

## Deployment

### Production Setup

1. **Configure Queue Driver**: Use `database` or `redis` instead of `sync`
2. **Process Monitoring**: Consider using Supervisor for robust queue worker management
3. **Auto-Start Settings**: May want to disable auto-start in production environments
4. **Resource Limits**: Set appropriate memory and timeout limits

### Docker Environments

In containerized environments:

```dockerfile
# Ensure ps command is available
RUN apt-get update && apt-get install -y procps

# Set appropriate permissions
RUN chown -R www-data:www-data /var/www/html
```

### Example Production Config

```bash
# Production .env settings
CODESNOUTR_QUEUE_ENABLED=true
CODESNOUTR_QUEUE_CONNECTION=redis
CODESNOUTR_QUEUE_AUTO_START=false  # Use Supervisor instead
CODESNOUTR_QUEUE_TIMEOUT=600
CODESNOUTR_QUEUE_MEMORY=1024
```

This ensures reliable queue management in production environments while maintaining the convenience of auto-start in development.
