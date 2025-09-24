<?php

namespace Rafaelogic\CodeSnoutr\Services\Wizard;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Rafaelogic\CodeSnoutr\Contracts\Services\Wizard\ScanExecutionServiceContract;
use Rafaelogic\CodeSnoutr\Jobs\ScanCodebaseJob;
use Rafaelogic\CodeSnoutr\Models\Scan;

/**
 * Scan Execution Service
 * 
 * Handles scan job creation, execution, progress tracking, and lifecycle management.
 */
class ScanExecutionService implements ScanExecutionServiceContract
{
    /**
     * Start a new scan with given configuration
     */
    public function startScan(array $config): array
    {
        try {
            // Determine the actual scan path
            $scanPath = $config['scanType'] === 'codebase' 
                ? base_path() 
                : ($config['target'] ?? '');
            
            // Create scan record
            $scan = Scan::create([
                'type' => $config['scanType'],
                'target' => $config['scanType'], // Use scanType as target for consistency
                'path' => $scanPath,
                'categories' => $config['ruleCategories'] ?? [],
                'status' => 'pending',
                'started_at' => now()
            ]);

            Log::info('Scan created with ID: ' . $scan->id);
            
            // Dispatch the job
            ScanCodebaseJob::dispatch(
                $scan->id,
                $config['scanType'],
                $scanPath,
                $config['ruleCategories'] ?? [],
                []
            );

            return [
                'success' => true,
                'scanId' => $scan->id,
                'status' => 'running',
                'message' => 'Scan started successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to start scan: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to start scan'
            ];
        }
    }

    /**
     * Get scan progress information
     */
    public function getScanProgress(int $scanId): array
    {
        // Check progress from cache
        $progress = Cache::get("scan_progress_{$scanId}");
        
        if ($progress) {
            return [
                'percentage' => $progress['percentage'] ?? 0,
                'message' => $progress['message'] ?? '',
                'current_file' => $progress['current_file'] ?? null,
                'files_processed' => $progress['files_processed'] ?? 0,
                'issues_found' => $progress['issues_found_so_far'] ?? 0,
                'target_path' => $progress['target_path'] ?? null,
            ];
        }

        return [
            'percentage' => 0,
            'message' => 'No progress data available',
            'current_file' => null,
            'files_processed' => 0,
            'issues_found' => 0,
            'target_path' => null,
        ];
    }

    /**
     * Pause a running scan
     */
    public function pauseScan(int $scanId): bool
    {
        try {
            Cache::put("scan_control_{$scanId}", 'pause', 300);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to pause scan {$scanId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resume a paused scan
     */
    public function resumeScan(int $scanId): bool
    {
        try {
            Cache::put("scan_control_{$scanId}", 'resume', 300);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to resume scan {$scanId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel a running scan
     */
    public function cancelScan(int $scanId): bool
    {
        try {
            Cache::put("scan_control_{$scanId}", 'cancel', 300);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to cancel scan {$scanId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check scan status from database
     */
    public function checkScanStatus(int $scanId): array
    {
        try {
            $scan = Scan::find($scanId);
            
            if (!$scan) {
                return [
                    'found' => false,
                    'status' => 'not_found',
                    'message' => 'Scan not found'
                ];
            }

            $result = [
                'found' => true,
                'status' => $scan->status,
                'total_files' => $scan->total_files,
                'total_issues' => $scan->total_issues,
                'started_at' => $scan->started_at,
                'completed_at' => $scan->completed_at,
                'error_message' => $scan->error_message,
            ];

            // Calculate elapsed time
            if ($scan->started_at) {
                $endTime = $scan->completed_at ?? now();
                $elapsed = $scan->started_at->diffInSeconds($endTime);
                $result['elapsed_time'] = $this->calculateElapsedTime($scanId);
                $result['elapsed_seconds'] = $elapsed;
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("Failed to check scan status for {$scanId}: " . $e->getMessage());
            
            return [
                'found' => false,
                'status' => 'error',
                'message' => 'Error checking scan status',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate elapsed time for a scan
     */
    public function calculateElapsedTime(int $scanId): string
    {
        try {
            $scan = Scan::find($scanId);
            
            if (!$scan || !$scan->started_at) {
                return '0:00';
            }

            $endTime = $scan->completed_at ?? now();
            $elapsed = $scan->started_at->diffInSeconds($endTime);
            
            return sprintf('%d:%02d', intval($elapsed / 60), $elapsed % 60);
            
        } catch (\Exception $e) {
            return '0:00';
        }
    }

    /**
     * Get scan control commands from cache
     */
    public function getScanControl(int $scanId): ?string
    {
        return Cache::get("scan_control_{$scanId}");
    }

    /**
     * Update scan progress in cache
     */
    public function updateScanProgress(int $scanId, array $progress): void
    {
        try {
            Cache::put("scan_progress_{$scanId}", $progress, 300);
        } catch (\Exception $e) {
            Log::error("Failed to update scan progress for {$scanId}: " . $e->getMessage());
        }
    }

    /**
     * Clear scan progress data
     */
    public function clearScanProgress(int $scanId): void
    {
        try {
            Cache::forget("scan_progress_{$scanId}");
            Cache::forget("scan_control_{$scanId}");
        } catch (\Exception $e) {
            Log::error("Failed to clear scan progress for {$scanId}: " . $e->getMessage());
        }
    }
}