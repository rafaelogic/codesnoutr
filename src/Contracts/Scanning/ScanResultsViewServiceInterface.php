<?php

namespace Rafaelogic\CodeSnoutr\Contracts\Scanning;

use Rafaelogic\CodeSnoutr\Models\Scan;
use Illuminate\Support\Collection;

interface ScanResultsViewServiceInterface
{
    /**
     * Load directory tree with issue statistics
     */
    public function loadDirectoryTree(Scan $scan, array $filters): array;

    /**
     * Load issues for a specific file
     */
    public function loadFileIssues(Scan $scan, string $filePath, array $filters, int $page, int $perPage): array;

    /**
     * Get directory statistics
     */
    public function getDirectoryStats(Scan $scan, string $directoryPath, array $filters): array;
}