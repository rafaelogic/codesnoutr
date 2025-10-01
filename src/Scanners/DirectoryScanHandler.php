<?php

namespace Rafaelogic\CodeSnoutr\Scanners;

use Illuminate\Foundation\Application;
use Symfony\Component\Finder\Finder;

class DirectoryScanHandler extends AbstractScanner
{
    /**
     * Scan a directory with progress tracking
     */
    public function scanWithProgress(?string $path, array $categories, array $options = [], ?callable $progressCallback = null): array
    {
        if (!$path || !is_dir($path)) {
            throw new \InvalidArgumentException("Directory path is required and must exist: {$path}");
        }

        // Validate and filter categories
        $categories = $this->validateCategories($categories);
        if (empty($categories)) {
            throw new \InvalidArgumentException("At least one valid category must be specified");
        }

        $this->updateStats('scan_type', 'directory');
        $this->updateStats('target_path', $path);
        $this->updateStats('started_at', now());

        if ($progressCallback) {
            $progressCallback(5, "Discovering files in directory: " . basename($path), [
                'current_directory' => $path
            ]);
        }

        try {
            // Get files to scan
            $finder = $this->getFilesToScan($path);
            $files = iterator_to_array($finder);
            $totalFiles = count($files);
            
            $this->updateStats('total_files_found', $totalFiles);
            
            if ($progressCallback) {
                $progressCallback(15, "Found {$totalFiles} files to scan", [
                    'total_files' => $totalFiles,
                    'current_directory' => $path
                ]);
            }
            
            $allIssues = [];
            $filesScanned = 0;
            $pathsScanned = [];
            $totalSize = 0;
            $totalLines = 0;

            // Process each file
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                
                if ($this->shouldSkipFile($filePath)) {
                    continue;
                }

                try {
                    $filesScanned++;
                    $progress = 15 + (($filesScanned / $totalFiles) * 70); // 15% to 85%
                    
                    if ($progressCallback) {
                        $progressCallback(
                            (int)$progress, 
                            "Scanning file {$filesScanned}/{$totalFiles}: " . basename($filePath),
                            [
                                'current_file' => $filePath,
                                'files_processed' => $filesScanned,
                                'total_files' => $totalFiles,
                                'issues_found_so_far' => count($allIssues)
                            ]
                        );
                    }

                    $result = $this->scanFile($filePath, $categories);
                    
                    if (!empty($result['issues'])) {
                        $allIssues = array_merge($allIssues, $result['issues']);
                    }
                    
                    $pathsScanned[] = $filePath;
                    $totalSize += $result['file_size'] ?? 0;
                    $totalLines += $result['line_count'] ?? 0;

                } catch (\Exception $e) {
                    // Log error but continue scanning
                    $this->updateStats('errors', $this->getStats()['errors'] + 1);
                    error_log("Error scanning file {$filePath}: " . $e->getMessage());
                }
            }

            $this->updateStats('files_scanned', $filesScanned);
            $this->updateStats('total_issues', count($allIssues));
            $this->updateStats('completed_at', now());

            if ($progressCallback) {
                $progressCallback(95, "Scan completed. Found " . count($allIssues) . " issues in {$filesScanned} files", [
                    'total_issues' => count($allIssues),
                    'files_scanned' => $filesScanned
                ]);
            }

            return [
                'issues' => $allIssues,
                'summary' => [
                    'scan_type' => 'directory',
                    'target_path' => $path,
                    'total_files_found' => $totalFiles,
                    'total_files_scanned' => $filesScanned,
                    'total_issues_found' => count($allIssues),
                    'total_size_bytes' => $totalSize,
                    'total_lines' => $totalLines,
                    'scan_duration_seconds' => $this->getStats()['completed_at']->diffInSeconds($this->getStats()['started_at']),
                ],
                'paths_scanned' => $pathsScanned,
                'stats' => $this->getStats()
            ];

        } catch (\Exception $e) {
            $this->updateStats('completed_at', now());
            $this->updateStats('error', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Scan a directory recursively
     */
    public function scan(?string $path, array $categories, array $options = []): array
    {
        if (!$path || !is_dir($path)) {
            throw new \InvalidArgumentException("Directory path is required and must exist: {$path}");
        }

        // Validate and filter categories
        $categories = $this->validateCategories($categories);
        if (empty($categories)) {
            throw new \InvalidArgumentException("At least one valid category must be specified");
        }

        $this->updateStats('scan_type', 'directory');
        $this->updateStats('target_path', $path);
        $this->updateStats('started_at', now());

        try {
            // Get files to scan
            $finder = $this->getFilesToScan($path);
            $files = iterator_to_array($finder);
            
            $this->updateStats('total_files_found', count($files));
            
            $allIssues = [];
            $filesScanned = 0;
            $pathsScanned = [];
            $totalSize = 0;
            $totalLines = 0;

            // Process each file
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                
                if ($this->shouldSkipFile($filePath)) {
                    continue;
                }

                try {
                    $result = $this->scanFile($filePath, $categories);
                    
                    $allIssues = array_merge($allIssues, $result['issues']);
                    $pathsScanned[] = $filePath;
                    $totalSize += $result['file_size'];
                    $totalLines += $result['line_count'];
                    $filesScanned++;

                } catch (\Exception $e) {
                    // Log individual file errors but continue scanning
                    $allIssues[] = [
                        'file_path' => $filePath,
                        'line_number' => 1,
                        'category' => 'quality',
                        'severity' => 'warning',
                        'rule_name' => 'scan_error',
                        'rule_id' => 'quality.scan_error',
                        'title' => 'File Scan Error',
                        'description' => "Failed to scan file: {$e->getMessage()}",
                        'suggestion' => 'Check file permissions and syntax.',
                        'context' => [
                            'error' => $e->getMessage(),
                            'file_path' => $filePath,
                        ],
                    ];
                }
            }

            // Update final statistics
            $this->updateStats('files_scanned', $filesScanned);
            $this->updateStats('total_issues', count($allIssues));
            $this->updateStats('issues_by_severity', $this->groupIssuesBySeverity($allIssues));
            $this->updateStats('issues_by_category', $this->groupIssuesByCategory($allIssues));
            $this->updateStats('total_size_bytes', $totalSize);
            $this->updateStats('total_lines', $totalLines);
            $this->updateStats('completed_at', now());

            return [
                'issues' => $allIssues,
                'stats' => $this->getStats(),
                'paths_scanned' => $pathsScanned,
                'summary' => [
                    'scan_type' => 'directory',
                    'target_path' => $path,
                    'categories_scanned' => $categories,
                    'total_files_found' => count($files),
                    'total_files_scanned' => $filesScanned,
                    'total_issues' => count($allIssues),
                    'total_size_bytes' => $totalSize,
                    'total_lines' => $totalLines,
                    'average_issues_per_file' => $filesScanned > 0 ? round(count($allIssues) / $filesScanned, 2) : 0,
                ],
            ];

        } catch (\Exception $e) {
            $this->updateStats('error', $e->getMessage());
            $this->updateStats('completed_at', now());
            
            throw new \RuntimeException("Failed to scan directory {$path}: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Group issues by severity
     */
    protected function groupIssuesBySeverity(array $issues): array
    {
        $grouped = ['critical' => 0, 'warning' => 0, 'info' => 0];
        
        foreach ($issues as $issue) {
            $severity = $issue['severity'] ?? 'info';
            if (isset($grouped[$severity])) {
                $grouped[$severity]++;
            }
        }
        
        return $grouped;
    }

    /**
     * Group issues by category
     */
    protected function groupIssuesByCategory(array $issues): array
    {
        $grouped = [];
        
        foreach ($issues as $issue) {
            $category = $issue['category'] ?? 'unknown';
            $grouped[$category] = ($grouped[$category] ?? 0) + 1;
        }
        
        return $grouped;
    }

    /**
     * Get directory scan progress
     */
    public function getProgress(): array
    {
        return [
            'total_files_found' => $this->stats['total_files_found'] ?? 0,
            'files_scanned' => $this->stats['files_scanned'] ?? 0,
            'current_file' => $this->stats['current_file'] ?? null,
            'percentage' => $this->calculateProgressPercentage(),
        ];
    }

    /**
     * Calculate scan progress percentage
     */
    protected function calculateProgressPercentage(): float
    {
        $total = $this->stats['total_files_found'] ?? 0;
        $scanned = $this->stats['files_scanned'] ?? 0;
        
        return $total > 0 ? round(($scanned / $total) * 100, 2) : 0;
    }
}
