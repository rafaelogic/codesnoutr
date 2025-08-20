<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Services\QueueService;

class QueueStatus extends Component
{
    public array $queueStatus = [];
    public array $queueStats = [];
    public bool $showDetails = false;
    public string $lastChecked = '';

    public function mount()
    {
        $this->refreshQueueStatus();
    }

    public function render()
    {
        return view('codesnoutr::livewire.queue-status');
    }

    public function refreshQueueStatus()
    {
        try {
            $queueService = app(QueueService::class);
            $this->queueStatus = $queueService->isQueueRunning();
            $this->queueStats = $queueService->getQueueStats();
            $this->lastChecked = now()->format('H:i:s');
            
            $this->dispatch('queue-status-refreshed', [
                'status' => $this->queueStatus,
                'stats' => $this->queueStats,
            ]);
            
        } catch (\Exception $e) {
            $this->queueStatus = [
                'is_running' => false,
                'error' => $e->getMessage(),
                'checked_at' => now(),
            ];
            
            $this->dispatch('queue-status-error', [
                'message' => $e->getMessage()
            ]);
        }
    }

    public function startQueue()
    {
        try {
            $queueService = app(QueueService::class);
            $result = $queueService->ensureQueueIsRunning();
            
            if ($result['success']) {
                $this->dispatch('queue-started', [
                    'message' => $result['message'],
                    'action_taken' => $result['action_taken'],
                ]);
                
                // Refresh status after starting
                sleep(2);
                $this->refreshQueueStatus();
            } else {
                $this->dispatch('queue-start-failed', [
                    'message' => $result['message']
                ]);
            }
            
        } catch (\Exception $e) {
            $this->dispatch('queue-start-error', [
                'message' => $e->getMessage()
            ]);
        }
    }

    public function stopQueue()
    {
        try {
            $queueService = app(QueueService::class);
            $result = $queueService->stopQueueWorkers();
            
            if ($result['success']) {
                $this->dispatch('queue-stopped', [
                    'message' => $result['message'],
                    'stopped_processes' => $result['stopped_processes'],
                ]);
                
                // Refresh status after stopping
                sleep(1);
                $this->refreshQueueStatus();
            } else {
                $this->dispatch('queue-stop-failed', [
                    'message' => $result['message']
                ]);
            }
            
        } catch (\Exception $e) {
            $this->dispatch('queue-stop-error', [
                'message' => $e->getMessage()
            ]);
        }
    }

    public function toggleDetails()
    {
        $this->showDetails = !$this->showDetails;
    }

    public function getStatusBadge(): array
    {
        if (!empty($this->queueStatus['error'])) {
            return [
                'class' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                'text' => 'Error',
                'icon' => 'x-circle'
            ];
        }

        if ($this->queueStatus['is_running'] ?? false) {
            return [
                'class' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                'text' => 'Running',
                'icon' => 'check-circle'
            ];
        }

        return [
            'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
            'text' => 'Stopped',
            'icon' => 'pause-circle'
        ];
    }

    public function getDriverInfo(): array
    {
        $driver = $this->queueStatus['driver'] ?? $this->queueStats['driver'] ?? 'unknown';
        
        return match($driver) {
            'sync' => [
                'name' => 'Synchronous',
                'description' => 'Jobs run immediately (no background processing)',
                'color' => 'gray'
            ],
            'database' => [
                'name' => 'Database',
                'description' => 'Jobs stored in database tables',
                'color' => 'blue'
            ],
            'redis' => [
                'name' => 'Redis',
                'description' => 'Jobs stored in Redis cache',
                'color' => 'red'
            ],
            'sqs' => [
                'name' => 'Amazon SQS',
                'description' => 'Jobs managed by AWS Simple Queue Service',
                'color' => 'orange'
            ],
            default => [
                'name' => ucfirst($driver),
                'description' => 'Unknown driver type',
                'color' => 'gray'
            ]
        };
    }

    public function clearStatusCache()
    {
        try {
            $queueService = app(QueueService::class);
            $queueService->clearQueueStatusCache();
            $this->refreshQueueStatus();
            
            $this->dispatch('queue-cache-cleared');
            
        } catch (\Exception $e) {
            $this->dispatch('queue-cache-clear-error', [
                'message' => $e->getMessage()
            ]);
        }
    }
}
