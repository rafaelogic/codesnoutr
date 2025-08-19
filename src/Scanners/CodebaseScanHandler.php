<?php

namespace Rafaelogic\CodeSnoutr\Scanners;

use Illuminate\Foundation\Application;

class CodebaseScanHandler extends AbstractScanner
{
    /**
     * Scan the entire codebase with progress tracking
     */
    public function scanWithProgress(?string $path, array $categories, array $options = [], ?callable $progressCallback = null): array
    {
        // Use configured scan paths if no specific path provided
        $scanPaths = $path ? [$path] : config('codesnoutr.scan.paths', ['app']);
        
        // Validate and filter categories
        $categories = $this->validateCategories($categories);
        if (empty($categories)) {
            throw new \InvalidArgumentException("At least one valid category must be specified");
        }

        $this->updateStats('scan_type', 'codebase');
        $this->updateStats('scan_paths', $scanPaths);
        $this->updateStats('started_at', now());

        if ($progressCallback) {
            $progressCallback(35, 'Analyzing codebase structure...', [
                'target_path' => $path ?? implode(', ', $scanPaths)
            ]);
        }

        try {
            $allIssues = [];
            $allPathsScanned = [];
            $totalFilesFound = 0;
            $totalFilesScanned = 0;
            $totalSize = 0;
            $totalLines = 0;
            $pathResults = [];

            // First, count total files for progress calculation
            $totalFiles = 0;
            foreach ($scanPaths as $scanPath) {
                $fullPath = base_path($scanPath);
                if (is_dir($fullPath)) {
                    $totalFiles += $this->countPhpFiles($fullPath);
                }
            }

            $filesProcessed = 0;

            // Process each configured path
            foreach ($scanPaths as $pathIndex => $scanPath) {
                $fullPath = base_path($scanPath);
                
                if (!is_dir($fullPath)) {
                    continue; // Skip if path doesn't exist
                }

                if ($progressCallback) {
                    $pathProgress = 35 + (($pathIndex / count($scanPaths)) * 40);
                    $progressCallback($pathProgress, "Scanning path: {$scanPath}...", [
                        'current_path' => $scanPath,
                        'files_processed' => $filesProcessed,
                        'total_files' => $totalFiles
                    ]);
                }

                // Use DirectoryScanHandler for each path with progress tracking
                $directoryScanner = new DirectoryScanHandler($this->app);
                $pathResult = $directoryScanner->scanWithProgress($fullPath, $categories, $options, function($percentage, $message, $extraData = []) use ($progressCallback, $pathIndex, $scanPaths, &$filesProcessed, $totalFiles) {
                    if ($progressCallback) {
                        $baseProgress = 35 + (($pathIndex / count($scanPaths)) * 40);
                        $currentPathProgress = ($percentage / 100) * (40 / count($scanPaths));
                        $totalProgress = min(75, $baseProgress + $currentPathProgress);
                        
                        if (isset($extraData['current_file'])) {
                            $filesProcessed++;
                        }
                        
                        $progressCallback($totalProgress, $message, array_merge($extraData, [
                            'files_processed' => $filesProcessed,
                            'total_files' => $totalFiles
                        ]));
                    }
                });

                // Merge results
                $allIssues = array_merge($allIssues, $pathResult['issues']);
                $allPathsScanned = array_merge($allPathsScanned, $pathResult['paths_scanned']);
                
                $totalFilesFound += $pathResult['files_found'];
                $totalFilesScanned += $pathResult['files_scanned'];
                $totalSize += $pathResult['total_size'];
                $totalLines += $pathResult['total_lines'];
                $pathResults[$scanPath] = $pathResult;
            }

            if ($progressCallback) {
                $progressCallback(80, 'Processing and categorizing issues...', [
                    'issues_found_so_far' => count($allIssues),
                    'files_processed' => $filesProcessed
                ]);
            }

            // Group issues by file and severity
            $groupedIssues = $this->groupIssues($allIssues);
            
            if ($progressCallback) {
                $progressCallback(90, 'Finalizing scan results...', [
                    'issues_found_so_far' => count($allIssues),
                    'files_processed' => $totalFilesScanned
                ]);
            }

            $this->updateStats('completed_at', now());
            $this->updateStats('total_files_found', $totalFilesFound);
            $this->updateStats('total_files_scanned', $totalFilesScanned);
            $this->updateStats('total_issues', count($allIssues));

            return [
                'issues' => $allIssues,
                'grouped_issues' => $groupedIssues,
                'paths_scanned' => $allPathsScanned,
                'path_results' => $pathResults,
                'files_found' => $totalFilesFound,
                'files_scanned' => $totalFilesScanned,
                'total_size' => $totalSize,
                'total_lines' => $totalLines,
                'scan_stats' => $this->getStats(),
                'summary' => $this->generateSummary($allIssues, $totalFilesScanned)
            ];

        } catch (\Exception $e) {
            $this->updateStats('error', $e->getMessage());
            if ($progressCallback) {
                $progressCallback(0, 'Scan failed: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Count PHP files in a directory for progress calculation
     */
    private function countPhpFiles(string $path): int
    {
        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($this->isPhpFile($file->getPathname())) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Scan the entire codebase
     */
    public function scan(?string $path, array $categories, array $options = []): array
    {
        return $this->scanWithProgress($path, $categories, $options);
    }
                
                $totalFilesFound += $pathResult['summary']['total_files_found'];
                $totalFilesScanned += $pathResult['summary']['total_files_scanned'];
                $totalSize += $pathResult['summary']['total_size_bytes'];
                $totalLines += $pathResult['summary']['total_lines'];

                $pathResults[$scanPath] = [
                    'path' => $fullPath,
                    'files_found' => $pathResult['summary']['total_files_found'],
                    'files_scanned' => $pathResult['summary']['total_files_scanned'],
                    'issues_found' => count($pathResult['issues']),
                    'size_bytes' => $pathResult['summary']['total_size_bytes'],
                ];
            }

            // Calculate codebase-level metrics
            $healthScore = $this->calculateHealthScore($allIssues, $totalFilesScanned);
            $complexityScore = $this->calculateCodebaseComplexity($allPathsScanned);

            // Update final statistics
            $this->updateStats('total_paths_scanned', count($scanPaths));
            $this->updateStats('total_files_found', $totalFilesFound);
            $this->updateStats('files_scanned', $totalFilesScanned);
            $this->updateStats('total_issues', count($allIssues));
            $this->updateStats('issues_by_severity', $this->groupIssuesBySeverity($allIssues));
            $this->updateStats('issues_by_category', $this->groupIssuesByCategory($allIssues));
            $this->updateStats('total_size_bytes', $totalSize);
            $this->updateStats('total_lines', $totalLines);
            $this->updateStats('health_score', $healthScore);
            $this->updateStats('complexity_score', $complexityScore);
            $this->updateStats('completed_at', now());

            return [
                'issues' => $allIssues,
                'stats' => $this->getStats(),
                'paths_scanned' => $allPathsScanned,
                'summary' => [
                    'scan_type' => 'codebase',
                    'scan_paths' => $scanPaths,
                    'categories_scanned' => $categories,
                    'total_paths_scanned' => count($scanPaths),
                    'total_files_found' => $totalFilesFound,
                    'total_files_scanned' => $totalFilesScanned,
                    'total_issues' => count($allIssues),
                    'total_size_bytes' => $totalSize,
                    'total_lines' => $totalLines,
                    'health_score' => $healthScore,
                    'complexity_score' => $complexityScore,
                    'average_issues_per_file' => $totalFilesScanned > 0 ? round(count($allIssues) / $totalFilesScanned, 2) : 0,
                    'issues_per_1000_lines' => $totalLines > 0 ? round((count($allIssues) / $totalLines) * 1000, 2) : 0,
                    'path_results' => $pathResults,
                ],
            ];

        } catch (\Exception $e) {
            $this->updateStats('error', $e->getMessage());
            $this->updateStats('completed_at', now());
            
            throw new \RuntimeException("Failed to scan codebase: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Calculate health score (0-100) based on issues found
     */
    protected function calculateHealthScore(array $issues, int $totalFiles): int
    {
        if ($totalFiles === 0) {
            return 100;
        }

        $criticalIssues = array_filter($issues, fn($issue) => $issue['severity'] === 'critical');
        $warningIssues = array_filter($issues, fn($issue) => $issue['severity'] === 'warning');

        // Weight critical issues more heavily
        $criticalWeight = 10;
        $warningWeight = 3;
        
        $totalWeight = (count($criticalIssues) * $criticalWeight) + (count($warningIssues) * $warningWeight);
        $maxPossibleWeight = $totalFiles * $criticalWeight; // Assume worst case

        $score = max(0, 100 - (($totalWeight / $maxPossibleWeight) * 100));

        return (int) round($score);
    }

    /**
     * Calculate overall codebase complexity score
     */
    protected function calculateCodebaseComplexity(array $filePaths): string
    {
        $totalComplexity = 0;
        $fileCount = 0;

        foreach ($filePaths as $filePath) {
            if (file_exists($filePath) && $this->isPhpFile($filePath)) {
                $content = file_get_contents($filePath);
                $totalComplexity += $this->calculateComplexity($content);
                $fileCount++;
            }
        }

        if ($fileCount === 0) {
            return 'low';
        }

        $averageComplexity = $totalComplexity / $fileCount;

        // Classify complexity
        if ($averageComplexity <= 10) {
            return 'low';
        } elseif ($averageComplexity <= 20) {
            return 'medium';
        } elseif ($averageComplexity <= 50) {
            return 'high';
        } else {
            return 'very_high';
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
     * Get top problematic files
     */
    public function getTopProblematicFiles(array $issues, int $limit = 10): array
    {
        $fileIssues = [];
        
        foreach ($issues as $issue) {
            $filePath = $issue['file_path'];
            if (!isset($fileIssues[$filePath])) {
                $fileIssues[$filePath] = [
                    'file_path' => $filePath,
                    'total_issues' => 0,
                    'critical_issues' => 0,
                    'warning_issues' => 0,
                    'info_issues' => 0,
                ];
            }
            
            $fileIssues[$filePath]['total_issues']++;
            $fileIssues[$filePath][$issue['severity'] . '_issues']++;
        }

        // Sort by total issues (critical weighted more)
        uasort($fileIssues, function ($a, $b) {
            $scoreA = ($a['critical_issues'] * 10) + ($a['warning_issues'] * 3) + $a['info_issues'];
            $scoreB = ($b['critical_issues'] * 10) + ($b['warning_issues'] * 3) + $b['info_issues'];
            return $scoreB <=> $scoreA;
        });

        return array_slice(array_values($fileIssues), 0, $limit);
    }

    /**
     * Get scan progress for codebase scanning
     */
    public function getProgress(): array
    {
        $totalPaths = count($this->stats['scan_paths'] ?? []);
        $completedPaths = $this->stats['completed_paths'] ?? 0;
        
        return [
            'total_paths' => $totalPaths,
            'completed_paths' => $completedPaths,
            'current_path' => $this->stats['current_path'] ?? null,
            'files_scanned' => $this->stats['files_scanned'] ?? 0,
            'total_files_found' => $this->stats['total_files_found'] ?? 0,
            'percentage' => $totalPaths > 0 ? round(($completedPaths / $totalPaths) * 100, 2) : 0,
        ];
    }
}
