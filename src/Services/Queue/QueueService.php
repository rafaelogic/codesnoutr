<?php

namespace Rafaelogic\CodeSnoutr\Services\Queue;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueueService
{
    protected string $queueDriver;
    protected string $queueConnection;
    protected string $queueName;

    public function __construct()
    {
        $this->queueDriver = config('queue.default', 'sync');
        $this->queueConnection = config('queue.default', 'sync');
        $this->queueName = config('codesnoutr.queue.name', 'default');
    }

    /**
     * Check if queue worker is running
     */
    public function isQueueRunning(): array
    {
        $status = [
            'is_running' => false,
            'process_count' => 0,
            'driver' => $this->queueDriver,
            'processes' => [],
            'checked_at' => now(),
        ];

        try {
            // Check for Laravel queue worker processes
            $result = Process::run("ps aux | grep 'queue:work' | grep -v grep");
            
            if ($result->successful() && !empty(trim($result->output()))) {
                $processes = array_filter(explode("\n", trim($result->output())));
                $status['is_running'] = true;
                $status['process_count'] = count($processes);
                $status['processes'] = $this->parseProcesses($processes);
            }

            // For sync driver, always consider it "running"
            if ($this->queueDriver === 'sync') {
                $status['is_running'] = true;
                $status['process_count'] = 1;
                $status['processes'] = [['type' => 'sync', 'status' => 'active']];
            }

        } catch (\Exception $e) {
            Log::warning('Failed to check queue status: ' . $e->getMessage());
            $status['error'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * Start queue worker if not running
     */
    public function ensureQueueIsRunning(): array
    {
        $status = $this->isQueueRunning();
        
        // If sync driver or already running, return current status
        if ($this->queueDriver === 'sync' || $status['is_running']) {
            return [
                'success' => true,
                'message' => $status['is_running'] ? 'Queue is already running' : 'Using sync driver',
                'status' => $status,
                'action_taken' => 'none',
            ];
        }

        try {
            // Start queue worker in background
            $result = $this->startQueueWorker();
            
            if ($result['success']) {
                // Wait a moment and check again
                sleep(2);
                $newStatus = $this->isQueueRunning();
                
                return [
                    'success' => true,
                    'message' => 'Queue worker started successfully',
                    'status' => $newStatus,
                    'action_taken' => 'started_worker',
                    'command' => $result['command'],
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to start queue worker: ' . $result['error'],
                    'status' => $status,
                    'action_taken' => 'start_failed',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Failed to start queue worker: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Exception while starting queue worker: ' . $e->getMessage(),
                'status' => $status,
                'action_taken' => 'exception',
            ];
        }
    }

    /**
     * Start queue worker process
     */
    protected function startQueueWorker(): array
    {
        $timeout = config('codesnoutr.queue.timeout', 300);
        $memory = config('codesnoutr.queue.memory', 512);
        $sleep = config('codesnoutr.queue.sleep', 3);
        $tries = config('codesnoutr.queue.tries', 3);

        // Build the artisan queue:work command
        $command = sprintf(
            'php %s queue:work %s --queue=%s --timeout=%d --memory=%d --sleep=%d --tries=%d --daemon > /dev/null 2>&1 &',
            base_path('artisan'),
            $this->queueConnection,
            $this->queueName,
            $timeout,
            $memory,
            $sleep,
            $tries
        );

        try {
            $result = Process::run($command);
            
            return [
                'success' => true,
                'command' => $command,
                'output' => $result->output(),
                'error_output' => $result->errorOutput(),
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'command' => $command,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse process information from ps output
     */
    protected function parseProcesses(array $processes): array
    {
        $parsed = [];
        
        foreach ($processes as $process) {
            if (empty(trim($process))) {
                continue;
            }
            
            $parts = preg_split('/\s+/', trim($process), 11);
            if (count($parts) >= 11) {
                $parsed[] = [
                    'user' => $parts[0],
                    'pid' => $parts[1],
                    'cpu' => $parts[2],
                    'memory' => $parts[3],
                    'start_time' => $parts[8],
                    'command' => $parts[10],
                    'type' => 'worker',
                    'status' => 'active',
                ];
            }
        }
        
        return $parsed;
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats(): array
    {
        $stats = [
            'driver' => $this->queueDriver,
            'connection' => $this->queueConnection,
            'queue_name' => $this->queueName,
            'pending_jobs' => 0,
            'failed_jobs' => 0,
            'processed_jobs' => 0,
        ];

        try {
            // Get pending jobs count (this varies by driver)
            if (in_array($this->queueDriver, ['database', 'redis'])) {
                if ($this->queueDriver === 'database') {
                    $stats['pending_jobs'] = DB::table('jobs')->where('queue', $this->queueName)->count();
                }
                // For Redis, we'd need to check Redis directly
            }

            // Get failed jobs count
            $stats['failed_jobs'] = DB::table('failed_jobs')->count();

        } catch (\Exception $e) {
            Log::warning('Failed to get queue stats: ' . $e->getMessage());
            $stats['error'] = $e->getMessage();
        }

        return $stats;
    }

    /**
     * Stop all queue workers
     */
    public function stopQueueWorkers(): array
    {
        try {
            // Find all queue worker processes
            $result = Process::run("ps aux | grep 'queue:work' | grep -v grep | awk '{print $2}'");
            
            if ($result->successful() && !empty(trim($result->output()))) {
                $pids = array_filter(explode("\n", trim($result->output())));
                
                foreach ($pids as $pid) {
                    if (is_numeric($pid)) {
                        Process::run("kill -TERM {$pid}");
                    }
                }
                
                return [
                    'success' => true,
                    'message' => 'Stopped ' . count($pids) . ' queue workers',
                    'stopped_processes' => count($pids),
                ];
            }
            
            return [
                'success' => true,
                'message' => 'No queue workers were running',
                'stopped_processes' => 0,
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to stop queue workers: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to stop queue workers: ' . $e->getMessage(),
                'stopped_processes' => 0,
            ];
        }
    }

    /**
     * Get queue monitoring data for UI display
     */
    public function getMonitoringData(): array
    {
        $queueStatus = $this->isQueueRunning();
        $queueStats = $this->getQueueStats();
        
        return [
            'queue_running' => $queueStatus['is_running'],
            'process_count' => $queueStatus['process_count'],
            'driver' => $this->queueDriver,
            'connection' => $this->queueConnection,
            'queue_name' => $this->queueName,
            'pending_jobs' => $queueStats['pending_jobs'],
            'failed_jobs' => $queueStats['failed_jobs'],
            'last_checked' => $queueStatus['checked_at'],
            'processes' => $queueStatus['processes'] ?? [],
        ];
    }

    /**
     * Cache queue status for performance
     */
    public function getCachedQueueStatus(): array
    {
        return Cache::remember('codesnoutr_queue_status', 30, function () {
            return $this->isQueueRunning();
        });
    }

    /**
     * Clear queue status cache
     */
    public function clearQueueStatusCache(): void
    {
        Cache::forget('codesnoutr_queue_status');
    }
}
