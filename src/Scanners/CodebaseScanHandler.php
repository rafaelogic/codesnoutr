<?php

namespace Rafaelogic\CodeSnoutr\Scanners;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;

class CodebaseScanHandler extends AbstractScanner
{
    /**
     * Scan the entire codebase with progress tracking
     */
    public function scanWithProgress(?string $path, array $categories, array $options = [], ?callable $progressCallback = null): array
    {
        // Use configured scan paths if no specific path provided
        $scanPaths = $path ? [$path] : config('codesnoutr.scan.paths', ['app']);
        
        Log::info('CodebaseScanHandler: Starting scan', [
            'input_path' => $path,
            'scan_paths' => $scanPaths,
            'categories' => $categories
        ]);
        
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
                // If path is already absolute (like when scanning full codebase), use it as-is
                $fullPath = str_starts_with($scanPath, '/') ? $scanPath : base_path($scanPath);
                Log::info('CodebaseScanHandler: Checking path', [
                    'scan_path' => $scanPath,
                    'full_path' => $fullPath,
                    'is_dir' => is_dir($fullPath)
                ]);
                if (is_dir($fullPath)) {
                    $fileCount = $this->countPhpFiles($fullPath);
                    $totalFiles += $fileCount;
                    Log::info('CodebaseScanHandler: File count for path', [
                        'path' => $scanPath,
                        'file_count' => $fileCount
                    ]);
                }
            }
            
            Log::info('CodebaseScanHandler: Total files to scan', ['total_files' => $totalFiles]);

            $filesProcessed = 0;

            // Process each configured path
            foreach ($scanPaths as $pathIndex => $scanPath) {
                // If path is already absolute (like when scanning full codebase), use it as-is
                $fullPath = str_starts_with($scanPath, '/') ? $scanPath : base_path($scanPath);
                
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
                
                $totalFilesFound += $pathResult['summary']['total_files_found'];
                $totalFilesScanned += $pathResult['summary']['total_files_scanned'];
                $totalSize += $pathResult['summary']['total_size_bytes'];
                $totalLines += $pathResult['summary']['total_lines'];
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

    /**
     * Group issues by file and severity for better organization
     */
    protected function groupIssues(array $issues): array
    {
        $grouped = [];
        
        foreach ($issues as $issue) {
            $filePath = $issue['file_path'] ?? 'unknown';
            $severity = $issue['severity'] ?? 'info';
            
            if (!isset($grouped[$filePath])) {
                $grouped[$filePath] = [
                    'file_path' => $filePath,
                    'total_issues' => 0,
                    'issues_by_severity' => [
                        'critical' => [],
                        'high' => [],
                        'medium' => [],
                        'low' => [],
                        'info' => []
                    ]
                ];
            }
            
            $grouped[$filePath]['total_issues']++;
            $grouped[$filePath]['issues_by_severity'][$severity][] = $issue;
        }
        
        // Sort files by total issues count (most problematic first)
        uasort($grouped, function($a, $b) {
            return $b['total_issues'] <=> $a['total_issues'];
        });
        
        return $grouped;
    }

    /**
     * Generate comprehensive scan summary
     */
    protected function generateSummary(array $issues, int $totalFilesScanned): array
    {
        $issueCount = count($issues);
        $severityCounts = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'info' => 0
        ];
        
        $categoryCounts = [];
        $affectedFiles = [];
        
        foreach ($issues as $issue) {
            $severity = $issue['severity'] ?? 'info';
            $category = $issue['category'] ?? 'unknown';
            $filePath = $issue['file_path'] ?? 'unknown';
            
            // Count by severity
            if (isset($severityCounts[$severity])) {
                $severityCounts[$severity]++;
            }
            
            // Count by category
            if (!isset($categoryCounts[$category])) {
                $categoryCounts[$category] = 0;
            }
            $categoryCounts[$category]++;
            
            // Track affected files
            if (!in_array($filePath, $affectedFiles)) {
                $affectedFiles[] = $filePath;
            }
        }
        
        // Calculate quality score
        $qualityScore = $this->calculateQualityScore($issueCount, $totalFilesScanned);
        
        return [
            'total_issues' => $issueCount,
            'files_scanned' => $totalFilesScanned,
            'affected_files' => count($affectedFiles),
            'clean_files' => max(0, $totalFilesScanned - count($affectedFiles)),
            'quality_score' => $qualityScore,
            'severity_breakdown' => $severityCounts,
            'category_breakdown' => $categoryCounts,
            'top_categories' => $this->getTopCategories($categoryCounts, 5),
            'most_affected_files' => $this->getMostAffectedFiles($issues, 10),
            'scan_timestamp' => now()->toISOString(),
            'recommendations' => $this->generateRecommendations($severityCounts, $qualityScore)
        ];
    }

    /**
     * Calculate quality score (0-100) based on issues found
     */
    protected function calculateQualityScore(int $issueCount, int $totalFiles): int
    {
        if ($totalFiles === 0) {
            return 100;
        }
        
        // Base score calculation: fewer issues = higher score
        $issuesPerFile = $issueCount / $totalFiles;
        
        // Scoring scale:
        // 0 issues per file = 100 points
        // 1 issue per file = 85 points
        // 2 issues per file = 70 points
        // 5+ issues per file = 50 points or lower
        
        if ($issuesPerFile === 0) {
            return 100;
        } elseif ($issuesPerFile <= 0.5) {
            return max(90, 100 - ($issuesPerFile * 20));
        } elseif ($issuesPerFile <= 1) {
            return max(80, 90 - (($issuesPerFile - 0.5) * 20));
        } elseif ($issuesPerFile <= 2) {
            return max(60, 80 - (($issuesPerFile - 1) * 20));
        } elseif ($issuesPerFile <= 5) {
            return max(30, 60 - (($issuesPerFile - 2) * 10));
        } else {
            return max(10, 30 - (($issuesPerFile - 5) * 5));
        }
    }

    /**
     * Get top categories by issue count
     */
    protected function getTopCategories(array $categoryCounts, int $limit = 5): array
    {
        arsort($categoryCounts);
        return array_slice($categoryCounts, 0, $limit, true);
    }

    /**
     * Get most affected files by issue count
     */
    protected function getMostAffectedFiles(array $issues, int $limit = 10): array
    {
        $fileCounts = [];
        
        foreach ($issues as $issue) {
            $filePath = $issue['file_path'] ?? 'unknown';
            if (!isset($fileCounts[$filePath])) {
                $fileCounts[$filePath] = 0;
            }
            $fileCounts[$filePath]++;
        }
        
        arsort($fileCounts);
        return array_slice($fileCounts, 0, $limit, true);
    }

    /**
     * Generate recommendations based on scan results
     */
    protected function generateRecommendations(array $severityCounts, int $qualityScore): array
    {
        $recommendations = [];
        
        if ($severityCounts['critical'] > 0) {
            $recommendations[] = [
                'priority' => 'high',
                'type' => 'security',
                'message' => "Address {$severityCounts['critical']} critical security issues immediately",
                'action' => 'Review and fix critical vulnerabilities'
            ];
        }
        
        if ($severityCounts['high'] > 5) {
            $recommendations[] = [
                'priority' => 'medium',
                'type' => 'quality',
                'message' => "Consider addressing {$severityCounts['high']} high-priority issues",
                'action' => 'Plan fixes for high-impact issues'
            ];
        }
        
        if ($qualityScore < 60) {
            $recommendations[] = [
                'priority' => 'medium',
                'type' => 'process',
                'message' => "Quality score is {$qualityScore}/100. Consider implementing more rigorous code review",
                'action' => 'Establish coding standards and review process'
            ];
        } elseif ($qualityScore >= 90) {
            $recommendations[] = [
                'priority' => 'low',
                'type' => 'maintenance',
                'message' => "Excellent code quality! Continue maintaining current standards",
                'action' => 'Keep up the good work'
            ];
        }
        
        if (array_sum($severityCounts) === 0) {
            $recommendations[] = [
                'priority' => 'low',
                'type' => 'maintenance',
                'message' => "No issues found! Consider running more comprehensive scans",
                'action' => 'Expand scan categories or add custom rules'
            ];
        }
        
        return $recommendations;
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
