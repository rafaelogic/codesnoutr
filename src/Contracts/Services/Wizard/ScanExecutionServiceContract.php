<?php

namespace Rafaelogic\CodeSnoutr\Contracts\Services\Wizard;

/**
 * Scan Execution Service Contract
 * 
 * Handles scan job creation, execution, progress tracking,
 * and scan lifecycle management.
 */
interface ScanExecutionServiceContract
{
    /**
     * Start a new scan with given configuration
     */
    public function startScan(array $config): array;

    /**
     * Get scan progress information
     */
    public function getScanProgress(int $scanId): array;

    /**
     * Pause a running scan
     */
    public function pauseScan(int $scanId): bool;

    /**
     * Resume a paused scan
     */
    public function resumeScan(int $scanId): bool;

    /**
     * Cancel a running scan
     */
    public function cancelScan(int $scanId): bool;

    /**
     * Check scan status from database
     */
    public function checkScanStatus(int $scanId): array;

    /**
     * Calculate elapsed time for a scan
     */
    public function calculateElapsedTime(int $scanId): string;

    /**
     * Get scan control commands from cache
     */
    public function getScanControl(int $scanId): ?string;

    /**
     * Update scan progress in cache
     */
    public function updateScanProgress(int $scanId, array $progress): void;
}