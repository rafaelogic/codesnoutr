<?php

namespace Rafaelogic\CodeSnoutr;

use Illuminate\Foundation\Application;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Scanners\FileScanHandler;
use Rafaelogic\CodeSnoutr\Scanners\DirectoryScanHandler;
use Rafaelogic\CodeSnoutr\Scanners\CodebaseScanHandler;
use Symfony\Component\Finder\Finder;

class ScanManager
{
    protected Application $app;
    protected array $scanners = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->initializeScanners();
    }

    /**
     * Initialize available scanners
     */
    protected function initializeScanners(): void
    {
        $this->scanners = [
            'file' => FileScanHandler::class,
            'directory' => DirectoryScanHandler::class,
            'codebase' => CodebaseScanHandler::class,
        ];
    }

    /**
     * Start a new scan
     */
    public function scan(string $type, ?string $path = null, array $categories = [], array $options = []): Scan
    {
        // Validate scan type
        if (!isset($this->scanners[$type])) {
            throw new \InvalidArgumentException("Invalid scan type: {$type}");
        }

        // Prepare the target path
        $targetPath = $this->prepareTargetPath($type, $path);

        // Create scan record
        $scan = Scan::create([
            'type' => $type,
            'target' => $path, // Store the original input path
            'status' => 'pending',
            'scan_options' => [
                'path' => $targetPath,
                'categories' => $categories,
                'options' => $options,
            ],
            'started_at' => now(),
        ]);

        try {
            // Mark as started
            $scan->markAsStarted();

            // Get appropriate scanner
            $scannerClass = $this->scanners[$type];
            $scanner = new $scannerClass($this->app);

            // Perform scan
            $results = $scanner->scan($targetPath, $categories, $options);

            // Process results
            $this->processResults($scan, $results);

            // Mark as completed
            $scan->markAsCompleted();
            $scan->updateStatistics();

        } catch (\Exception $e) {
            $scan->markAsFailed($e->getMessage());
            throw $e;
        }

        return $scan;
    }

    /**
     * Perform background scan with progress callback
     */
    public function performBackgroundScan(
        Scan $scan,
        string $type,
        ?string $path = null,
        array $categories = [],
        array $options = [],
        ?callable $progressCallback = null
    ): array {
        // Validate scan type
        if (!isset($this->scanners[$type])) {
            throw new \InvalidArgumentException("Invalid scan type: {$type}");
        }

        // Prepare the target path
        $targetPath = $this->prepareTargetPath($type, $path);

        if ($progressCallback) {
            $progressCallback(10, 'Initializing scanner...');
        }

        // Get appropriate scanner
        $scannerClass = $this->scanners[$type];
        $scanner = new $scannerClass($this->app);

        if ($progressCallback) {
            $progressCallback(20, 'Starting scan...');
        }

        // Perform scan with progress tracking
        $results = $scanner->scanWithProgress($targetPath, $categories, $options, $progressCallback);

        if ($progressCallback) {
            $progressCallback(80, 'Processing results...');
        }

        // Process results
        $this->processResults($scan, $results);

        if ($progressCallback) {
            $progressCallback(95, 'Finalizing scan...');
        }

        return $results;
    }

    /**
     * Prepare the target path for scanning
     */
    protected function prepareTargetPath(string $type, ?string $path): string
    {
        $basePath = base_path();

        switch ($type) {
            case 'codebase':
                return $basePath;
                
            case 'file':
            case 'directory':
                if (!$path) {
                    throw new \InvalidArgumentException("Path is required for {$type} scan");
                }
                
                // If path is absolute, use it as is
                if (str_starts_with($path, '/')) {
                    return $path;
                }
                
                // Otherwise, make it relative to base path
                return $basePath . '/' . ltrim($path, '/');
                
            default:
                throw new \InvalidArgumentException("Unknown scan type: {$type}");
        }
    }

    /**
     * Process scan results and store issues
     */
    protected function processResults(Scan $scan, array $results): void
    {
        foreach ($results['issues'] as $issueData) {
            Issue::create([
                'scan_id' => $scan->id,
                'file_path' => $issueData['file_path'],
                'line_number' => $issueData['line_number'],
                'column_number' => $issueData['column_number'] ?? null,
                'category' => $issueData['category'],
                'severity' => $issueData['severity'],
                'rule_name' => $issueData['rule_name'],
                'rule_id' => $issueData['rule_id'],
                'title' => $issueData['title'],
                'description' => $issueData['description'],
                'suggestion' => $issueData['suggestion'],
                'context' => $issueData['context'] ?? [],
                'metadata' => $issueData['metadata'] ?? [],
            ]);
        }

        // Update scan statistics
        $scan->update([
            'total_files' => $results['summary']['total_files_scanned'] ?? $results['stats']['files_scanned'] ?? 0,
            'paths_scanned' => $results['paths_scanned'] ?? [],
            'summary' => $results['summary'] ?? [],
        ]);
    }

    /**
     * Get available scan types
     */
    public function getAvailableScanTypes(): array
    {
        return array_keys($this->scanners);
    }

    /**
     * Get available categories
     */
    public function getAvailableCategories(): array
    {
        return [
            'security' => [
                'name' => 'Security',
                'description' => 'Security vulnerabilities and risks',
                'icon' => 'shield-exclamation',
            ],
            'performance' => [
                'name' => 'Performance',
                'description' => 'Performance issues and optimization opportunities',
                'icon' => 'bolt',
            ],
            'quality' => [
                'name' => 'Code Quality',
                'description' => 'Code quality and maintainability issues',
                'icon' => 'code',
            ],
            'laravel' => [
                'name' => 'Laravel Best Practices',
                'description' => 'Laravel-specific conventions and best practices',
                'icon' => 'cube',
            ],
        ];
    }

    /**
     * Get scan statistics
     */
    public function getStatistics(): array
    {
        $totalScans = Scan::count();
        $completedScans = Scan::completed()->count();
        $failedScans = Scan::failed()->count();
        $totalIssues = Issue::count();
        
        $issuesByCategory = Issue::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $issuesBySeverity = Issue::selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        return [
            'scans' => [
                'total' => $totalScans,
                'completed' => $completedScans,
                'failed' => $failedScans,
                'success_rate' => $totalScans > 0 ? round(($completedScans / $totalScans) * 100, 2) : 0,
            ],
            'issues' => [
                'total' => $totalIssues,
                'by_category' => $issuesByCategory,
                'by_severity' => $issuesBySeverity,
            ],
        ];
    }

    /**
     * Get recent scans
     */
    public function getRecentScans(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Scan::with(['issues'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get issues for a specific file
     */
    public function getFileIssues(string $filePath): \Illuminate\Database\Eloquent\Collection
    {
        return Issue::with(['scan'])
            ->where('file_path', $filePath)
            ->orderBySeverity()
            ->get();
    }

    /**
     * Get project health score
     */
    public function getHealthScore(): int
    {
        $criticalIssues = Issue::critical()->count();
        $warningIssues = Issue::warning()->count();
        $totalFiles = $this->getTotalProjectFiles();

        if ($totalFiles === 0) {
            return 100;
        }

        // Calculate score based on issues per file
        $criticalWeight = 10;
        $warningWeight = 3;
        
        $totalWeight = ($criticalIssues * $criticalWeight) + ($warningIssues * $warningWeight);
        $maxPossibleWeight = $totalFiles * $criticalWeight; // Assume worst case

        $score = max(0, 100 - (($totalWeight / $maxPossibleWeight) * 100));

        return (int) round($score);
    }

    /**
     * Get total project files count
     */
    protected function getTotalProjectFiles(): int
    {
        $finder = new Finder();
        $paths = config('codesnoutr.scan.paths', ['app']);
        $extensions = config('codesnoutr.scan.file_extensions', ['php']);

        try {
            $finder->files();
            
            foreach ($paths as $path) {
                $fullPath = base_path($path);
                if (is_dir($fullPath)) {
                    $finder->in($fullPath);
                }
            }

            foreach ($extensions as $extension) {
                $finder->name("*.{$extension}");
            }

            return iterator_count($finder);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Clean up old scans and issues
     */
    public function cleanup(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        
        $deletedScans = Scan::where('created_at', '<', $cutoffDate)->count();
        
        // Delete old scans (issues will be deleted via cascade)
        Scan::where('created_at', '<', $cutoffDate)->delete();
        
        return $deletedScans;
    }

    /**
     * Export scan results
     */
    public function exportScan(Scan $scan, string $format = 'json'): string
    {
        $issues = $scan->issues()->orderBySeverity()->get();
        
        $data = [
            'scan' => [
                'id' => $scan->id,
                'type' => $scan->type,
                'status' => $scan->status,
                'started_at' => $scan->started_at,
                'completed_at' => $scan->completed_at,
                'duration_seconds' => $scan->duration_seconds,
                'total_files' => $scan->total_files,
                'total_issues' => $scan->total_issues,
                'statistics' => $scan->severityDistribution(),
            ],
            'issues' => $issues->map(function ($issue) {
                return [
                    'file_path' => $issue->file_path,
                    'line_number' => $issue->line_number,
                    'category' => $issue->category,
                    'severity' => $issue->severity,
                    'rule_name' => $issue->rule_name,
                    'title' => $issue->title,
                    'description' => $issue->description,
                    'suggestion' => $issue->suggestion,
                ];
            })->toArray(),
        ];

        return match($format) {
            'json' => json_encode($data, JSON_PRETTY_PRINT),
            'csv' => $this->convertToCsv($data['issues']),
            default => json_encode($data, JSON_PRETTY_PRINT),
        };
    }

    /**
     * Convert issues to CSV format
     */
    protected function convertToCsv(array $issues): string
    {
        if (empty($issues)) {
            return '';
        }

        $csv = "File,Line,Category,Severity,Rule,Title,Description,Suggestion\n";
        
        foreach ($issues as $issue) {
            $csv .= sprintf(
                '"%s",%d,"%s","%s","%s","%s","%s","%s"' . "\n",
                $issue['file_path'],
                $issue['line_number'],
                $issue['category'],
                $issue['severity'],
                $issue['rule_name'],
                str_replace('"', '""', $issue['title']),
                str_replace('"', '""', $issue['description']),
                str_replace('"', '""', $issue['suggestion'])
            );
        }

        return $csv;
    }
}
