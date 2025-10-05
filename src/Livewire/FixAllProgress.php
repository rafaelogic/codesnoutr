<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Rafaelogic\CodeSnoutr\Jobs\FixAllIssuesJob;
use Rafaelogic\CodeSnoutr\Jobs\SimpleFixAllIssuesJob;
use Illuminate\Support\Facades\DB;
use Rafaelogic\CodeSnoutr\Models\Issue;

class FixAllProgress extends Component
{
    public $sessionId = '';
    public $progress = [];
    public $autoRefresh = true;
    
    // Progress tracking
    public $status = 'idle';
    public $currentStep = 0;
    public $totalSteps = 0;
    public $message = 'Click "Start Fix All Process" button to begin';
    public $currentFile = null;
    public $results = [];
    public $fixedCount = 0;
    public $failedCount = 0;
    public $startedAt = null;
    public $completedAt = null;
    


    public function mount($sessionId = null)
    {
        $this->sessionId = $sessionId ?: Str::uuid()->toString();
        
        // Ensure all properties are properly initialized
        $this->status = $this->status ?: 'idle';
        $this->currentStep = $this->currentStep ?: 0;
        $this->totalSteps = $this->totalSteps ?: 0;
        $this->message = $this->message ?: 'Click "Start Fix All Process" button to begin';
        $this->currentFile = $this->currentFile ?: null;
        $this->results = $this->results ?: [];
        $this->fixedCount = $this->fixedCount ?: 0;
        $this->failedCount = $this->failedCount ?: 0;
        $this->startedAt = $this->startedAt ?: null;
        $this->completedAt = $this->completedAt ?: null;
        $this->autoRefresh = $this->autoRefresh ?? true;
        
        $this->loadProgress();
    }

    public function render()
    {
        return view('codesnoutr::livewire.fix-all-progress');
    }

    public function startFixAll()
    {
        try {
            Log::info('FixAllProgress: startFixAll method called', ['session_id' => $this->sessionId]);
            
            // Check queue setup first
            $queueCheck = $this->checkQueueSetup();
            if (!$queueCheck['ready']) {
                $this->status = 'failed';
                $this->message = $queueCheck['message'];
                
                // Dispatch browser notification
                $this->dispatch('queue-setup-error', [
                    'message' => $queueCheck['message'],
                    'recommendations' => $queueCheck['recommendations'] ?? []
                ]);
                
                Log::error('FixAllProgress: Queue not ready', $queueCheck);
                return;
            }
            
            // Check if there are issues to fix
            $unfixedCount = Issue::where('fixed', false)->count();
            Log::info('FixAllProgress: Found unfixed issues', ['count' => $unfixedCount]);
            
            if ($unfixedCount === 0) {
                $this->message = 'No issues found that need fixing.';
                $this->status = 'completed';
                Log::info('FixAllProgress: No issues to fix, marking as completed');
                return;
            }

            // Check AI availability - make this optional for now
            try {
                $aiService = new \Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService();
                if (!$aiService->isAvailable()) {
                    Log::warning('FixAllProgress: AI service not available, but continuing anyway');
                    // Don't fail immediately, let the job handle this
                }
            } catch (\Exception $e) {
                Log::warning('FixAllProgress: Could not check AI availability', ['error' => $e->getMessage()]);
                // Continue anyway, let the job handle this
            }

            // Initialize progress
            $this->status = 'starting';
            $this->message = 'Starting Fix All process...';
            $this->startedAt = now()->toISOString();
            $this->totalSteps = $unfixedCount;
            
            Log::info('FixAllProgress: Initialized progress', [
                'status' => $this->status,
                'total_steps' => $this->totalSteps,
                'started_at' => $this->startedAt
            ]);
            
            // Clear any existing progress
            try {
                Cache::forget("fix_all_progress_{$this->sessionId}");
                Log::info('FixAllProgress: Cleared existing cache');
            } catch (\Exception $e) {
                Log::warning('FixAllProgress: Could not clear cache', ['error' => $e->getMessage()]);
                // Cache not available, continue anyway
            }
            
            // Save initial progress to cache
            try {
                $progressData = [
                    'status' => $this->status,
                    'current_step' => $this->currentStep,
                    'total_steps' => $this->totalSteps,
                    'message' => $this->message,
                    'current_file' => $this->currentFile,
                    'results' => $this->results,
                    'fixed_count' => $this->fixedCount,
                    'failed_count' => $this->failedCount,
                    'started_at' => $this->startedAt,
                    'completed_at' => $this->completedAt,
                ];
                
                Cache::put("fix_all_progress_{$this->sessionId}", $progressData, 3600); // 1 hour
                Log::info('FixAllProgress: Saved initial progress to cache');
            } catch (\Exception $e) {
                Log::warning('FixAllProgress: Could not save progress to cache', ['error' => $e->getMessage()]);
            }
            
            // Dispatch the job
            try {
                // Check queue configuration
                $queueConnection = config('queue.default');
                $isSyncQueue = $queueConnection === 'sync';
                $runSync = config('codesnoutr.debug_sync_jobs', false) || $isSyncQueue;
                
                Log::info('FixAllProgress: Queue analysis', [
                    'queue_connection' => $queueConnection,
                    'is_sync_queue' => $isSyncQueue,
                    'will_run_sync' => $runSync
                ]);
                
                // Check if queue worker is running for async jobs
                if (!$runSync && !$isSyncQueue) {
                    $queueWorkerRunning = $this->isQueueWorkerRunning();
                    
                    if (!$queueWorkerRunning) {
                        $this->status = 'failed';
                        $this->message = 'Cannot start Fix All: Queue worker is not running. Please start the queue worker with: php artisan queue:work';
                        
                        Log::error('FixAllProgress: Cannot dispatch job - queue worker not running', [
                            'queue_connection' => $queueConnection,
                            'session_id' => $this->sessionId
                        ]);
                        
                        // Show error notification
                        $this->dispatch('show-notification', [
                            'type' => 'error',
                            'message' => 'Queue worker is not running! Start it with: php artisan queue:work'
                        ]);
                        
                        return;
                    }
                    
                    Log::info('FixAllProgress: Queue worker is running, proceeding with dispatch');
                }
                
                if ($runSync && $isSyncQueue) {
                    // For sync queue, limit issues to avoid timeout but use REAL AI fixes
                    $limitedCount = min(5, $unfixedCount); // Process max 5 issues for sync mode
                    Log::info('FixAllProgress: Running sync with limited issues to avoid timeout', [
                        'limited_count' => $limitedCount,
                        'total_unfixed' => $unfixedCount
                    ]);
                    FixAllIssuesJob::dispatchSync($this->sessionId, $limitedCount);
                } else if ($runSync) {
                    Log::info('FixAllProgress: Running job synchronously for debugging');
                    // Use production FixAllIssuesJob for real AI fixes
                    FixAllIssuesJob::dispatchSync($this->sessionId, $unfixedCount);
                } else {
                    Log::info('FixAllProgress: Dispatching job to queue');
                    // Use production FixAllIssuesJob for real AI fixes
                    FixAllIssuesJob::dispatch($this->sessionId, $unfixedCount);
                }
                
                Log::info('FixAllProgress: Job processing initiated successfully', ['sync' => $runSync]);
                
                // For sync jobs, load the final status from cache (job already completed)
                // For async jobs, update status to processing
                if ($runSync || $isSyncQueue) {
                    // Job ran synchronously and is already complete
                    // Load the final progress from cache
                    $this->loadProgress();
                    
                    Log::info('FixAllProgress: Loaded final progress after sync job', [
                        'status' => $this->status,
                        'fixed_count' => $this->fixedCount,
                        'failed_count' => $this->failedCount
                    ]);
                    
                    // Dispatch completion event
                    $this->dispatch('status-changed', $this->status);
                    
                    // Disable auto-refresh since job is done
                    $this->autoRefresh = false;
                } else {
                    // Async job - update status to processing
                    $this->status = 'processing';
                    $this->message = 'Fix All job has been queued and will start shortly...';
                    
                    // Save the updated progress to cache
                    $progressData = [
                        'status' => $this->status,
                        'current_step' => $this->currentStep,
                        'total_steps' => $this->totalSteps,
                        'message' => $this->message,
                        'current_file' => $this->currentFile,
                        'results' => $this->results,
                        'fixed_count' => $this->fixedCount,
                        'failed_count' => $this->failedCount,
                        'started_at' => $this->startedAt,
                        'completed_at' => $this->completedAt,
                        'updated_at' => now()->toISOString()
                    ];
                    
                    Cache::put("fix_all_progress_{$this->sessionId}", $progressData, 3600);
                    
                    // Start auto-refresh for async jobs
                    $this->autoRefresh = true;
                    
                    // Force a component re-render
                    $this->dispatch('status-changed', $this->status);
                }
                
            } catch (\Exception $e) {
                Log::error('FixAllProgress: Failed to dispatch job', ['error' => $e->getMessage()]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('FixAllProgress: Exception in startFixAll', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->status = 'failed';
            $this->message = 'Failed to start Fix All process: ' . $e->getMessage();
        }
    }
    
    // Simple method to test if the component state updates work
    public function testStatusUpdate()
    {
        $this->status = 'processing';
        $this->message = 'Test status update working...';
        $this->currentStep = 1;
        $this->totalSteps = 10;
        
        // Save to cache
        $progressData = [
            'status' => $this->status,
            'current_step' => $this->currentStep,
            'total_steps' => $this->totalSteps,
            'message' => $this->message,
            'current_file' => null,
            'results' => [],
            'fixed_count' => 0,
            'failed_count' => 0,
            'started_at' => now()->toISOString(),
            'completed_at' => null,
            'updated_at' => now()->toISOString()
        ];
        
        Cache::put("fix_all_progress_{$this->sessionId}", $progressData, 3600);
        
        $this->dispatch('status-changed', $this->status);
    }
    
    // Method to run job synchronously for debugging
    public function startFixAllSync()
    {
        try {
            Log::info('FixAllProgress: Running job synchronously for debugging');
            
            $this->status = 'processing';
            $this->message = 'Running job synchronously (debug mode)...';
            $this->startedAt = now()->toISOString();
            
            // Run the job immediately
            $job = new SimpleFixAllIssuesJob($this->sessionId);
            $job->handle();
            
            // Reload progress after job completion
            $this->loadProgress();
            
        } catch (\Exception $e) {
            Log::error('FixAllProgress: Sync job failed', ['error' => $e->getMessage()]);
            $this->status = 'failed';
            $this->message = 'Sync job failed: ' . $e->getMessage();
        }
    }
    
    // Method to check queue configuration
    public function checkQueueConfig()
    {
        $defaultConnection = config('queue.default');
        $queueConnection = config('codesnoutr.queue.connection', $defaultConnection);
        
        // Count jobs in queue
        $jobsCount = 0;
        try {
            $jobsCount = DB::table('jobs')->count();
        } catch (\Exception $e) {
            // Jobs table might not exist
        }
        
        $config = [
            'default_connection' => $defaultConnection,
            'codesnoutr_connection' => $queueConnection,
            'codesnoutr_queue_enabled' => config('codesnoutr.queue.enabled'),
            'codesnoutr_queue_name' => config('codesnoutr.queue.name'),
            'is_sync_queue' => $queueConnection === 'sync' || $defaultConnection === 'sync',
            'jobs_in_queue' => $jobsCount,
            'cache_driver' => config('cache.default'),
            'app_env' => config('app.env'),
            'recommendation' => $queueConnection === 'sync' ? 'Queue is set to sync - jobs run immediately but may timeout' : 'Queue is set to async - ensure queue worker is running'
        ];
        
        Log::info('Queue Configuration Check', $config);
        
        $this->dispatch('console-log', [
            'message' => 'Queue configuration checked - see Laravel logs',
            'data' => $config
        ]);
        
        // Update UI with queue info
        $message = "Queue: {$queueConnection}, Jobs: {$jobsCount}, Cache: " . config('cache.default');
        if ($config['is_sync_queue'] && $jobsCount > 0) {
            $message .= " ⚠️ SYNC QUEUE WITH PENDING JOBS";
            session()->flash('message', "Warning: {$jobsCount} jobs found in queue but using 'sync' driver. Jobs may timeout. Consider running 'php artisan queue:work --once' or switching to database/redis queue driver.");
        }
        
        $this->message = $message;
    }

    public function loadProgress()
    {
        try {
            $cacheKey = "fix_all_progress_{$this->sessionId}";
            $progress = Cache::get($cacheKey, []);
            
            Log::debug('FixAllProgress: loadProgress reading from cache', [
                'session_id' => $this->sessionId,
                'cache_key' => $cacheKey,
                'cache_exists' => !empty($progress),
                'cache_data_keys' => !empty($progress) ? array_keys($progress) : [],
            ]);
            
            if (!empty($progress)) {
                $this->status = $progress['status'] ?? $this->status ?? 'idle';
                $this->currentStep = $progress['current_step'] ?? $this->currentStep ?? 0;
                $this->totalSteps = $progress['total_steps'] ?? $this->totalSteps ?? 0;
                $this->message = $progress['message'] ?? $this->message ?? 'Loading...';
                $this->currentFile = $progress['current_file'] ?? $this->currentFile ?? null;
                $this->results = $progress['results'] ?? $this->results ?? [];
                $this->fixedCount = $progress['fixed_count'] ?? $this->fixedCount ?? 0;
                $this->failedCount = $progress['failed_count'] ?? $this->failedCount ?? 0;
                $this->startedAt = $progress['started_at'] ?? $this->startedAt ?? null;
                $this->completedAt = $progress['completed_at'] ?? $this->completedAt ?? null;
                
                Log::debug('FixAllProgress: Progress loaded successfully', [
                    'current_step' => $this->currentStep,
                    'total_steps' => $this->totalSteps,
                    'fixed_count' => $this->fixedCount,
                    'failed_count' => $this->failedCount,
                    'status' => $this->status,
                ]);
            } else {
                Log::warning('FixAllProgress: Cache is empty, no progress data found', [
                    'session_id' => $this->sessionId,
                    'cache_key' => $cacheKey,
                ]);
            }
        } catch (\Exception $e) {
            // If cache is not available, use default values
            $this->status = $this->status ?? 'idle';
            $this->message = $this->message ?? 'Click "Start Fix All Process" button to begin';
            
            Log::error('FixAllProgress: Failed to load progress from cache', [
                'session_id' => $this->sessionId,
                'error' => $e->getMessage(),
            ]);
        }
    }
    


    public function refreshProgress()
    {
        $oldStatus = $this->status;
        $oldStep = $this->currentStep;
        $oldTotalSteps = $this->totalSteps;
        $oldFixedCount = $this->fixedCount;
        $oldFailedCount = $this->failedCount;
        
        $this->loadProgress();
        
        // Log wire:poll activity with detailed change tracking
        $hasChanges = (
            $oldStatus !== $this->status ||
            $oldStep !== $this->currentStep ||
            $oldTotalSteps !== $this->totalSteps ||
            $oldFixedCount !== $this->fixedCount ||
            $oldFailedCount !== $this->failedCount
        );
        
        Log::info('FixAllProgress: wire:poll refreshProgress called', [
            'session_id' => $this->sessionId,
            'polling_active' => true,
            'has_changes' => $hasChanges,
            'old_values' => [
                'status' => $oldStatus,
                'current_step' => $oldStep,
                'total_steps' => $oldTotalSteps,
                'fixed_count' => $oldFixedCount,
                'failed_count' => $oldFailedCount,
            ],
            'new_values' => [
                'status' => $this->status,
                'current_step' => $this->currentStep,
                'total_steps' => $this->totalSteps,
                'fixed_count' => $this->fixedCount,
                'failed_count' => $this->failedCount,
            ],
            'changes_detected' => [
                'status_changed' => $oldStatus !== $this->status,
                'step_changed' => $oldStep !== $this->currentStep,
                'total_steps_changed' => $oldTotalSteps !== $this->totalSteps,
                'fixed_count_changed' => $oldFixedCount !== $this->fixedCount,
                'failed_count_changed' => $oldFailedCount !== $this->failedCount,
            ],
        ]);
        
        // Dispatch browser events when status changes
        if ($oldStatus !== $this->status) {
            $this->dispatch('status-changed', $this->status);
        }
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function clearResults()
    {
        try {
            Cache::forget("fix_all_progress_{$this->sessionId}");
        } catch (\Exception $e) {
            // Cache not available, continue anyway
        }
        
        $this->reset(['progress', 'status', 'currentStep', 'totalSteps', 'message', 'currentFile', 'results', 'fixedCount', 'failedCount', 'startedAt', 'completedAt']);
        $this->status = 'idle';
        $this->message = 'Click "Start Fix All Process" button to begin';
    }



    public function stopFixAll()
    {
        try {
            Log::info('FixAllProgress: Stop requested', ['session_id' => $this->sessionId]);
            
            // Set stop flag in cache
            Cache::put("fix_all_stop_{$this->sessionId}", true, 3600);
            
            // Update status
            $this->status = 'stopping';
            $this->message = 'Stopping Fix All process... Current issue will complete, then stop.';
            
            // Save progress
            $this->saveProgress();
            
            // Dispatch event
            $this->dispatch('fix-all-stopping');
            
            Log::info('FixAllProgress: Stop flag set', ['session_id' => $this->sessionId]);
            
        } catch (\Exception $e) {
            Log::error('FixAllProgress: Failed to stop', ['error' => $e->getMessage()]);
            $this->message = 'Failed to stop process: ' . $e->getMessage();
        }
    }

    public function goToDashboard()
    {
        return redirect()->route('codesnoutr.dashboard');
    }

    public function downloadResults()
    {
        if (empty($this->results)) {
            return;
        }

        $filename = 'fix-all-results-' . date('Y-m-d-H-i-s') . '.json';
        $content = json_encode([
            'session_id' => $this->sessionId,
            'status' => $this->status,
            'summary' => [
                'total_processed' => $this->totalSteps,
                'fixed_count' => $this->fixedCount,
                'failed_count' => $this->failedCount,
                'started_at' => $this->startedAt,
                'completed_at' => $this->completedAt,
                'elapsed_time' => $this->startedAt ? Carbon::parse($this->startedAt)->diff($this->completedAt ? Carbon::parse($this->completedAt) : now())->format('%H:%I:%S') : null
            ],
            'results' => $this->results
        ], JSON_PRETTY_PRINT);

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }



    /**
     * Check if queue is properly set up
     * This checks the HOST APPLICATION's queue, not the package's
     */
    private function checkQueueSetup()
    {
        $queueConnection = config('queue.default');
        $isSyncQueue = $queueConnection === 'sync';
        
        // Log the application context to verify we're checking the right place
        Log::info('FixAllProgress: Checking queue in host application', [
            'app_path' => base_path(),
            'queue_connection' => $queueConnection,
            'database_connection' => config('database.default')
        ]);
        
        // Check 1: Try to detect if queue worker process is running (system-level check)
        if (!$isSyncQueue) {
            $workerRunning = $this->isQueueWorkerRunning();
            
            if (!$workerRunning) {
                Log::warning('FixAllProgress: No queue worker process detected on system');
                return [
                    'ready' => false,
                    'message' => '⚠️ Queue worker process not detected. Please start a queue worker.',
                    'recommendations' => [
                        '1. Open a new terminal window',
                        '2. Navigate to: ' . base_path(),
                        '3. Run: php artisan queue:work --queue=default',
                        '4. Keep that terminal open',
                        '5. Then try Fix All again',
                        '',
                        'Alternative: Switch to sync mode (QUEUE_CONNECTION=sync in .env)'
                    ]
                ];
            } else {
                Log::info('FixAllProgress: Queue worker process detected running');
            }
        }
        
        // Check 2: If using database queue, verify tables exist in HOST application database
        if ($queueConnection === 'database') {
            try {
                // Use the default database connection from the host application
                $connection = config('database.default');
                $database = config("database.connections.{$connection}.database");
                
                Log::info('FixAllProgress: Checking database queue', [
                    'connection' => $connection,
                    'database' => $database
                ]);
                
                // Check if jobs table exists in HOST application database
                $tableExists = false;
                try {
                    // This will use the host application's database connection
                    DB::connection($connection)->table('jobs')->limit(1)->get();
                    $tableExists = true;
                } catch (\Exception $e) {
                    Log::warning('FixAllProgress: Jobs table not found', [
                        'connection' => $connection,
                        'error' => $e->getMessage()
                    ]);
                }
                
                if (!$tableExists) {
                    return [
                        'ready' => false,
                        'message' => '⚠️ Database queue tables not found in host application.',
                        'recommendations' => [
                            'Run: php artisan queue:table',
                            'Run: php artisan migrate',
                            'Or switch to sync mode: QUEUE_CONNECTION=sync in .env',
                            '',
                            'Note: Run these commands in: ' . base_path()
                        ]
                    ];
                }
                
                // Check 3: Check for stale jobs in HOST application database
                $pendingJobs = DB::connection($connection)->table('jobs')->count();
                $oldJobs = DB::connection($connection)
                    ->table('jobs')
                    ->where('available_at', '<', now()->subMinutes(2)->timestamp)
                    ->count();
                
                Log::info('FixAllProgress: Queue stats from host application', [
                    'pending_jobs' => $pendingJobs,
                    'old_jobs' => $oldJobs,
                    'database' => $database
                ]);
                
                // If there are old jobs (>2 minutes), worker is likely stuck or not processing
                if ($oldJobs > 0) {
                    Log::warning('FixAllProgress: Old jobs detected - worker may not be running or stuck', ['count' => $oldJobs]);
                    return [
                        'ready' => false,
                        'message' => "⚠️ Queue worker not processing jobs. Found {$oldJobs} stale job(s) (>2 min old).",
                        'recommendations' => [
                            '1. Check if queue worker is running: ps aux | grep "queue:work"',
                            '2. If not running, start it: php artisan queue:work --queue=default',
                            '3. If running but stuck, restart it: php artisan queue:restart',
                            '4. Then try Fix All again',
                            '',
                            'Commands must be run in: ' . base_path()
                        ]
                    ];
                }
                
                // Warning: Even if no old jobs, log for debugging
                if ($pendingJobs > 20) {
                    Log::warning('FixAllProgress: Many pending jobs', ['count' => $pendingJobs]);
                }
                
            } catch (\Exception $e) {
                Log::warning('FixAllProgress: Could not check database queue in host application', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't block user if we can't check - they'll see it doesn't work anyway
                return [
                    'ready' => true,
                    'message' => 'Queue check skipped (database error)',
                    'warning' => 'Could not verify queue worker status: ' . $e->getMessage()
                ];
            }
        }
        
        // Check 3: If using Redis, verify connection
        if ($queueConnection === 'redis') {
            try {
                $redis = \Illuminate\Support\Facades\Redis::connection();
                $redis->ping();
            } catch (\Exception $e) {
                return [
                    'ready' => false,
                    'message' => 'Redis connection failed: ' . $e->getMessage(),
                    'recommendations' => [
                        'Verify Redis is running: redis-cli ping',
                        'Check Redis configuration in config/database.php',
                        'Or switch to database queue: QUEUE_CONNECTION=database'
                    ]
                ];
            }
        }
        
        // Check 4: Warn if using sync queue for many issues
        if ($isSyncQueue) {
            $unfixedCount = Issue::where('fixed', false)->count();
            if ($unfixedCount > 10) {
                Log::warning('FixAllProgress: Using sync queue with many issues', ['count' => $unfixedCount]);
                // Don't fail, just warn in the logs
                // The sync mode will limit to 5 issues anyway
            }
        }
        
        return [
            'ready' => true,
            'message' => 'Queue is ready',
            'queue_connection' => $queueConnection,
            'is_sync' => $isSyncQueue
        ];
    }

    /**
     * Save current progress to cache
     */
    private function saveProgress()
    {
        try {
            $progressData = [
                'status' => $this->status,
                'current_step' => $this->currentStep,
                'total_steps' => $this->totalSteps,
                'message' => $this->message,
                'current_file' => $this->currentFile,
                'results' => $this->results,
                'fixed_count' => $this->fixedCount,
                'failed_count' => $this->failedCount,
                'started_at' => $this->startedAt,
                'completed_at' => $this->completedAt,
                'updated_at' => now()->toISOString()
            ];
            
            Cache::put("fix_all_progress_{$this->sessionId}", $progressData, 3600);
            
        } catch (\Exception $e) {
            Log::warning('FixAllProgress: Could not save progress to cache', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check if a queue worker process is actually running on the system
     * Works on Unix-like systems (Linux, macOS)
     */
    private function isQueueWorkerRunning(): bool
    {
        try {
            // Check if we're on a Unix-like system
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows detection - check for php.exe running artisan queue:work
                exec('tasklist /FI "IMAGENAME eq php.exe" 2>NUL', $output);
                $running = count($output) > 0;
                Log::info('FixAllProgress: Windows queue worker check', ['running' => $running]);
                return $running; // On Windows, we can't easily check if it's specifically queue:work
            }
            
            // Unix/Linux/macOS - check for queue:work process
            $command = "ps aux | grep -E '[q]ueue:work' | grep -v grep";
            exec($command, $output, $returnCode);
            
            $workerRunning = !empty($output);
            
            Log::info('FixAllProgress: Queue worker process check', [
                'os' => PHP_OS_FAMILY,
                'command' => $command,
                'processes_found' => count($output),
                'worker_running' => $workerRunning,
                'output' => $output
            ]);
            
            return $workerRunning;
            
        } catch (\Exception $e) {
            Log::warning('FixAllProgress: Could not check for queue worker process', [
                'error' => $e->getMessage()
            ]);
            // If we can't check, assume it might be running (don't block the user)
            return true;
        }
    }
}