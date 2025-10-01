<?php

namespace Rafaelogic\CodeSnoutr\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Rafaelogic\CodeSnoutr\ScanManager;
use Rafaelogic\CodeSnoutr\Models\Scan;

class ScanCodebaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 1;

    protected string $scanType;
    protected ?string $target;
    protected array $ruleCategories;
    protected array $scanOptions;
    protected int $scanId;

    public function __construct(int $scanId, string $scanType, ?string $target, array $ruleCategories, array $scanOptions)
    {
        $this->scanId = $scanId;
        $this->scanType = $scanType;
        $this->target = $target;
        $this->ruleCategories = $ruleCategories;
        $this->scanOptions = $scanOptions;
    }

    public function handle(ScanManager $scanManager): void
    {
        try {
            // Find the scan record
            $scan = Scan::findOrFail($this->scanId);
            
            // Update status to running
            $scan->update([
                'status' => 'running',
                'started_at' => now(),
            ]);

            $this->updateProgress(0, 'Initializing scan...', [
                'target_path' => $this->target,
                'scan_type' => $this->scanType,
                'rule_categories' => $this->ruleCategories
            ]);

            // Perform the actual scan
            $result = $scanManager->performBackgroundScan(
                $scan,
                $this->scanType,
                $this->target,
                $this->ruleCategories,
                $this->scanOptions,
                [$this, 'updateProgress']
            );

            $this->updateProgress(100, 'Scan completed successfully');

            // Update scan with final results
            $scan->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_files' => $result['files_scanned'] ?? 0,
                'total_issues' => $result['total_issues'] ?? 0,
                'scan_duration' => now()->diffInSeconds($scan->started_at),
            ]);

            // Clear progress cache after completion
            Cache::forget("scan_progress_{$this->scanId}");

            Log::info("Scan {$this->scanId} completed successfully");

        } catch (\Exception $e) {
            Log::error("Scan {$this->scanId} failed: " . $e->getMessage());

            // Update scan status to failed
            if (isset($scan)) {
                $scan->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $e->getMessage(),
                ]);
            }

            $this->updateProgress(0, 'Scan failed: ' . $e->getMessage());
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Scan job {$this->scanId} failed with exception: " . $exception->getMessage());
        
        try {
            $scan = Scan::find($this->scanId);
            if ($scan) {
                $scan->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $exception->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update scan status after job failure: " . $e->getMessage());
        }

        // Clear progress cache
        Cache::forget("scan_progress_{$this->scanId}");
    }

    public function updateProgress(int $percentage, string $message = '', array $extraData = []): void
    {
        $progressData = [
            'percentage' => $percentage,
            'message' => $message,
            'updated_at' => now()->timestamp,
            'scan_path' => $this->target ?? 'Unknown path',
        ];

        // Merge any extra data (like current file being scanned)
        $progressData = array_merge($progressData, $extraData);

        // Store progress in cache for 1 hour
        Cache::put("scan_progress_{$this->scanId}", $progressData, 3600);
    }
}
