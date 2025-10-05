<?php

namespace Rafaelogic\CodeSnoutr\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Services\Issues\IssueActionInvoker;

class FixAllIssuesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 minutes
    public $tries = 1;
    public $maxExceptions = 1;

    protected $sessionId;
    protected $maxIssues;

    public function __construct(string $sessionId, int $maxIssues = 50)
    {
        $this->sessionId = $sessionId;
        $this->maxIssues = $maxIssues;
        
        // Set the queue using the trait method
        $this->onQueue('default');
    }

    public function handle()
    {
        // Increase execution time for this job
        if (function_exists('set_time_limit')) {
            set_time_limit(1800); // 30 minutes
        }
        
        // Increase memory limit
        if (function_exists('ini_set')) {
            ini_set('memory_limit', '512M');
        }
        
        try {
            Log::info('Starting Fix All Issues Job', [
                'session_id' => $this->sessionId,
                'max_issues' => $this->maxIssues
            ]);

            // Initialize progress
            $this->updateProgress([
                'status' => 'processing',  // Changed from 'initializing'
                'current_step' => 0,
                'total_steps' => 0,
                'message' => 'Preparing to fix issues...',
                'current_file' => null,
                'results' => [],
                'started_at' => now()->toISOString()
            ]);

            // Get unfixed issues count first
            $totalUnfixed = Issue::where('fixed', false)->count();
            
            if ($totalUnfixed === 0) {
                $this->updateProgress([
                    'status' => 'completed',
                    'message' => 'No issues found that need fixing.',
                    'completed_at' => now()->toISOString()
                ]);
                return;
            }
            
            // Process issues in smaller chunks to avoid memory/timeout issues
            $chunkSize = min(10, $this->maxIssues); // Process max 10 at a time
            $processedCount = 0;
            $maxToProcess = min($this->maxIssues, $totalUnfixed);
            
            // Update progress with total count
            $this->updateProgress([
                'status' => 'processing',
                'total_steps' => $maxToProcess,
                'message' => "Found {$maxToProcess} issues to fix. Starting processing..."
            ]);
            
            Log::info('Processing issues in chunks', [
                'total_unfixed' => $totalUnfixed,
                'max_to_process' => $maxToProcess,
                'chunk_size' => $chunkSize
            ]);

            // Check AI availability
            $aiService = new \Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService();
            if (!$aiService->isAvailable()) {
                $this->updateProgress([
                    'status' => 'failed',
                    'message' => 'AI service is not available. Please configure your OpenAI API key in settings.',
                    'completed_at' => now()->toISOString()
                ]);
                return;
            }

            $fixedCount = 0;
            $failedCount = 0;
            $results = [];
            
            // Get action invoker for processing issues
            $actionInvoker = app(IssueActionInvoker::class);

            // Get all issues to process (simpler approach without chunk callback)
            $issues = Issue::where('fixed', false)
                ->limit($maxToProcess)
                ->get();

            foreach ($issues as $index => $issue) {
                // Check for stop flag before processing each issue
                if (Cache::get("fix_all_stop_{$this->sessionId}", false)) {
                    Log::info('FixAllIssuesJob: Stop flag detected, halting process', [
                        'session_id' => $this->sessionId,
                        'processed' => $processedCount,
                        'total' => $maxToProcess
                    ]);
                    
                    $this->updateProgress([
                        'status' => 'stopped',
                        'message' => "Process stopped by user after {$processedCount} of {$maxToProcess} issues",
                        'current_step' => $processedCount,
                        'fixed_count' => $fixedCount,
                        'failed_count' => $failedCount,
                        'results' => $results,
                        'completed_at' => now()->toISOString(),
                        'stopped' => true
                    ]);
                    
                    // Clear stop flag
                    Cache::forget("fix_all_stop_{$this->sessionId}");
                    
                    return; // Exit the job
                }
                
                $processedCount++;
                $currentStep = $processedCount;
        
                // Update current progress
                $this->updateProgress([
                    'current_step' => $currentStep,
                    'message' => "Fixing issue {$currentStep} of {$maxToProcess}",
                    'current_file' => $issue->file_path ? basename($issue->file_path) : 'Unknown file'
                ]);

                Log::info('FixAllIssuesJob: Processing issue', [
                    'issue_id' => $issue->id,
                    'step' => "{$currentStep}/{$maxToProcess}",
                    'file' => $issue->file_path
                ]);

                try {
                    // Generate AI fix if it doesn't exist
                    if (empty($issue->ai_fix)) {
                        Log::info('FixAllIssuesJob: Generating AI fix', ['issue_id' => $issue->id]);
                        $generateResult = $actionInvoker->executeAction('generate_ai_fix', $issue);
                        if (!$generateResult['success']) {
                            $failedCount++;
                            $results[] = [
                                'issue_id' => $issue->id,
                                'title' => $issue->title ?? 'Unknown Issue',
                                'file' => $issue->file_path ? basename($issue->file_path) : 'Unknown',
                                'full_path' => $issue->file_path,
                                'line' => $issue->line_number ?? 0,
                                'status' => 'failed',
                                'step' => 'generate',
                                'message' => 'Failed to generate AI fix: ' . ($generateResult['message'] ?? 'Unknown error'),
                                'timestamp' => now()->toISOString()
                            ];
                            
                            Log::warning('FixAllIssuesJob: Failed to generate AI fix', [
                                'issue_id' => $issue->id,
                                'error' => $generateResult['message'] ?? 'Unknown'
                            ]);
                            continue;
                        }
                        
                        $issue->refresh();
                        Log::info('FixAllIssuesJob: AI fix generated successfully', ['issue_id' => $issue->id]);
                    }
                    
                    // Apply the AI fix
                    Log::info('FixAllIssuesJob: Applying AI fix', ['issue_id' => $issue->id]);
                    $applyResult = $actionInvoker->executeAction('apply_ai_fix', $issue);
                    
                    if ($applyResult['success']) {
                        $fixedCount++;
                        $results[] = [
                            'issue_id' => $issue->id,
                            'title' => $issue->title ?? 'Unknown Issue',
                            'file' => $issue->file_path ? basename($issue->file_path) : 'Unknown',
                            'full_path' => $issue->file_path,
                            'line' => $issue->line_number ?? 0,
                            'status' => 'success',
                            'step' => 'applied',
                            'message' => 'Successfully applied AI fix',
                            'timestamp' => now()->toISOString()
                        ];
                        
                        Log::info('FixAllIssuesJob: AI fix applied successfully', ['issue_id' => $issue->id]);
                    } else {
                        $failedCount++;
                        $results[] = [
                            'issue_id' => $issue->id,
                            'title' => $issue->title ?? 'Unknown Issue',
                            'file' => $issue->file_path ? basename($issue->file_path) : 'Unknown',
                            'full_path' => $issue->file_path,
                            'line' => $issue->line_number ?? 0,
                            'status' => 'failed',
                            'step' => 'apply',
                            'message' => 'Failed to apply fix: ' . ($applyResult['message'] ?? 'Unknown error'),
                            'timestamp' => now()->toISOString()
                        ];
                        
                        Log::warning('FixAllIssuesJob: Failed to apply AI fix', [
                            'issue_id' => $issue->id,
                            'error' => $applyResult['message'] ?? 'Unknown'
                        ]);
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $results[] = [
                        'issue_id' => $issue->id,
                        'title' => $issue->title ?? 'Unknown Issue',
                        'file' => $issue->file_path ? basename($issue->file_path) : 'Unknown',
                        'full_path' => $issue->file_path,
                        'line' => $issue->line_number ?? 0,
                        'status' => 'failed',
                        'step' => 'exception',
                        'message' => 'Exception: ' . $e->getMessage(),
                        'timestamp' => now()->toISOString()
                    ];
                    
                    Log::error('FixAllIssuesJob: Exception during issue processing', [
                        'issue_id' => $issue->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                // Update progress with current results
                $this->updateProgress([
                    'current_step' => $currentStep,
                    'results' => $results,
                    'fixed_count' => $fixedCount,
                    'failed_count' => $failedCount
                ]);

                // Small delay to prevent overwhelming the system and AI API
                usleep(200000); // 0.2 seconds
            }

            // Final completion update
            $this->updateProgress([
                'status' => 'completed',
                'current_step' => $processedCount,
                'current_file' => null,
                'message' => "Fix All completed! Fixed: {$fixedCount} issues, Failed: {$failedCount} issues",
                'fixed_count' => $fixedCount,
                'failed_count' => $failedCount,
                'results' => $results,
                'completed_at' => now()->toISOString()
            ]);

            Log::info('FixAllIssuesJob: Completed successfully', [
                'session_id' => $this->sessionId,
                'fixed_count' => $fixedCount,
                'failed_count' => $failedCount,
                'total_processed' => $processedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Fix All Issues Job failed', [
                'session_id' => $this->sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->updateProgress([
                'status' => 'failed',
                'message' => 'Job failed: ' . $e->getMessage(),
                'completed_at' => now()->toISOString()
            ]);
        }
    }

    protected function updateProgress(array $data)
    {
        try {
            $cacheKey = "fix_all_progress_{$this->sessionId}";
            $currentProgress = Cache::get($cacheKey, []);
            
            // Merge new data with existing progress
            $updatedProgress = array_merge($currentProgress, $data);
            $updatedProgress['updated_at'] = now()->toISOString();
            
            // Ensure critical fields are never lost during partial updates
            if (!isset($data['status']) && isset($currentProgress['status'])) {
                $updatedProgress['status'] = $currentProgress['status'];
            }
            if (!isset($data['total_steps']) && isset($currentProgress['total_steps'])) {
                $updatedProgress['total_steps'] = $currentProgress['total_steps'];
            }
            if (!isset($data['started_at']) && isset($currentProgress['started_at'])) {
                $updatedProgress['started_at'] = $currentProgress['started_at'];
            }
            
            // Store progress for 1 hour
            Cache::put($cacheKey, $updatedProgress, 3600);
            
            // Verify the write was successful
            $verifyRead = Cache::get($cacheKey);
            $writeSuccess = !empty($verifyRead);
            
            Log::info('FixAllIssuesJob: Progress updated', [
                'session_id' => $this->sessionId,
                'cache_key' => $cacheKey,
                'status' => $updatedProgress['status'] ?? 'unknown',
                'current_step' => $updatedProgress['current_step'] ?? 0,
                'total_steps' => $updatedProgress['total_steps'] ?? 0,
                'fixed_count' => $updatedProgress['fixed_count'] ?? 0,
                'failed_count' => $updatedProgress['failed_count'] ?? 0,
                'cache_write_verified' => $writeSuccess,
                'cache_driver' => config('cache.default')
            ]);
            
            // Also broadcast to any listening components (if using websockets)
            // event(new FixAllProgressUpdated($this->sessionId, $updatedProgress));
        } catch (\Exception $e) {
            Log::error('FixAllIssuesJob: Failed to update progress', [
                'session_id' => $this->sessionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('FixAllIssuesJob: Job failed catastrophically', [
            'session_id' => $this->sessionId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        try {
            $this->updateProgress([
                'status' => 'failed',
                'message' => 'Job failed catastrophically: ' . $exception->getMessage(),
                'completed_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('FixAllIssuesJob: Failed to update progress after failure', [
                'error' => $e->getMessage()
            ]);
        }
    }
}