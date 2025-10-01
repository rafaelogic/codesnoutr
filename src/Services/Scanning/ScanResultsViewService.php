<?php

namespace Rafaelogic\CodeSnoutr\Services\Scanning;

use Rafaelogic\CodeSnoutr\Contracts\Scanning\ScanResultsViewServiceInterface;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Services\UI\CodeDisplayService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ScanResultsViewService implements ScanResultsViewServiceInterface
{
    protected CodeDisplayService $codeDisplayService;

    public function __construct(CodeDisplayService $codeDisplayService)
    {
        $this->codeDisplayService = $codeDisplayService;
    }

    /**
     * Load directory tree structure with issues
     */
    public function loadDirectoryTree(Scan $scan, array $filters = []): array
    {
        try {
            $query = $scan->issues();
            
            // Apply filters
            $this->applyFilters($query, $filters);
            
            $issues = $query->get();
            
            if ($issues->isEmpty()) {
                return [
                    'tree' => [],
                    'stats' => []
                ];
            }

            // Build directory tree
            $tree = $this->buildDirectoryStructure($issues);
            $stats = $this->calculateDirectoryStats($issues);

            return [
                'tree' => $tree,
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            Log::error('Failed to load directory tree: ' . $e->getMessage());
            return [
                'tree' => [],
                'stats' => []
            ];
        }
    }

    /**
     * Load issues for a specific file
     */
    public function loadFileIssues(Scan $scan, string $filePath, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        try {
            $query = $scan->issues()->where('file_path', $filePath);
            
            // Apply filters
            $this->applyFilters($query, $filters);
            
            // Apply sorting
            $query->orderByRaw('
                CASE 
                    WHEN severity = "critical" THEN 5
                    WHEN severity = "high" THEN 4
                    WHEN severity = "medium" THEN 3
                    WHEN severity = "low" THEN 2
                    ELSE 1
                END DESC
            ')->orderBy('line_number');

            // Get total count before pagination
            $totalIssues = $query->count();
            
            // Count resolved issues (those marked as fixed)
            $resolvedIssues = $scan->issues()
                ->where('file_path', $filePath)
                ->where('fixed', true)
                ->count();
            
            // Apply pagination
            $issues = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            // Group issues by rule/category
            $groupedIssues = $this->groupIssuesByRule($issues);

            return [
                'issues' => $groupedIssues,
                'stats' => [
                    'total' => $totalIssues,
                    'total_issues' => $totalIssues, // Add expected key
                    'resolved_issues' => $resolvedIssues, // Add expected key
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'last_page' => ceil($totalIssues / $perPage),
                    'severity_counts' => $this->getSeverityCounts($issues)
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to load file issues: ' . $e->getMessage());
            return [
                'issues' => collect(),
                'stats' => [
                    'total' => 0,
                    'total_issues' => 0, // Add expected key
                    'resolved_issues' => 0, // Add expected key
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'last_page' => 1,
                    'severity_counts' => []
                ]
            ];
        }
    }

    /**
     * Apply filters to the query
     */
    protected function applyFilters($query, array $filters): void
    {
        if (!empty($filters['severity']) && $filters['severity'] !== 'all') {
            $query->where('severity', $filters['severity']);
        }

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['fixed'])) {
            if ($filters['fixed'] === 'fixed') {
                $query->where('fixed', true);
            } elseif ($filters['fixed'] === 'unfixed') {
                $query->where('fixed', false);
            }
        }
    }

    /**
     * Build directory structure from issues
     */
    protected function buildDirectoryStructure(Collection $issues): array
    {
        $structure = [];

        foreach ($issues as $issue) {
            // Get relative path from Laravel app root
            $relativePath = $this->getRelativePath($issue->file_path);
            $parts = explode('/', $relativePath);
            $current = &$structure;

            // Build nested directory structure
            for ($i = 0; $i < count($parts) - 1; $i++) {
                $part = $parts[$i];
                
                // Skip empty or whitespace-only parts
                if (empty($part) || trim($part) === '') {
                    continue;
                }
                
                if (!isset($current['directories'][$part])) {
                    $current['directories'][$part] = [
                        'name' => $part,
                        'path' => implode('/', array_slice($parts, 0, $i + 1)),
                        'directories' => [],
                        'files' => [],
                        'issue_count' => 0,
                        'issues_count' => 0, // Add plural version for consistency
                        'resolved_count' => 0, // Add resolved count
                        'highest_severity' => null, // Add highest severity for color coding
                        'severity_counts' => []
                    ];
                }
                $current = &$current['directories'][$part];
            }

            // Add file to structure
            $filename = end($parts);
            if (!isset($current['files'][$filename])) {
                $current['files'][$filename] = [
                    'name' => $filename,
                    'path' => $issue->file_path, // Keep full path for file access
                    'issue_count' => 0,
                    'issues_count' => 0, // Add plural version for Blade view
                    'resolved_count' => 0, // Add resolved count for Blade view
                    'highest_severity' => null, // Add highest severity for color coding
                    'severity_counts' => []
                ];
            }

            $current['files'][$filename]['issue_count']++;
            $current['files'][$filename]['issues_count']++; // Keep both in sync
            
            // Count resolved issues
            if ($issue->fixed) {
                $current['files'][$filename]['resolved_count']++;
            }
            
            // Update highest severity
            $current['files'][$filename]['highest_severity'] = $this->getHighestSeverity(
                $current['files'][$filename]['highest_severity'], 
                $issue->severity
            );
            
            $current['files'][$filename]['severity_counts'][$issue->severity] = 
                ($current['files'][$filename]['severity_counts'][$issue->severity] ?? 0) + 1;
        }

        return $this->calculateDirectoryIssueCounts($structure);
    }

    /**
     * Get relative path from Laravel app root
     */
    protected function getRelativePath(string $fullPath): string
    {
        $basePath = base_path();
        
        // If the path starts with base_path, make it relative
        if (strpos($fullPath, $basePath) === 0) {
            $relativePath = ltrim(substr($fullPath, strlen($basePath)), '/');
            if (!empty($relativePath)) {
                return $relativePath;
            }
        }
        
        // Otherwise, try to extract meaningful parts
        $parts = explode('/', $fullPath);
        $meaningfulParts = [];
        $foundApp = false;
        
        foreach ($parts as $part) {
            if (empty($part) || trim($part) === '') continue; // Skip empty parts
            
            // Start collecting from 'app' directory or similar
            if (in_array($part, ['app', 'resources', 'config', 'routes', 'database', 'tests', 'storage']) || $foundApp) {
                $foundApp = true;
                $meaningfulParts[] = $part;
            }
        }
        
        // If no meaningful parts found, try to get the last few directories
        if (empty($meaningfulParts)) {
            $parts = array_filter(explode('/', $fullPath), function($part) {
                return !empty($part) && trim($part) !== '';
            });
            
            if (count($parts) >= 2) {
                // Take last 2-3 meaningful parts
                $meaningfulParts = array_slice($parts, -3);
            } else {
                $meaningfulParts = $parts;
            }
        }
        
        return implode('/', $meaningfulParts) ?: basename($fullPath);
    }

    /**
     * Get the highest severity between two severities
     */
    protected function getHighestSeverity(?string $current, string $new): string
    {
        $severityLevels = [
            'critical' => 5,
            'high' => 4,
            'medium' => 3,
            'low' => 2,
            'info' => 1
        ];

        $currentLevel = $severityLevels[$current] ?? 0;
        $newLevel = $severityLevels[$new] ?? 0;

        return $newLevel > $currentLevel ? $new : ($current ?? $new);
    }

    /**
     * Calculate directory statistics
     */
    protected function calculateDirectoryStats(Collection $issues): array
    {
        $stats = [
            'total_issues' => $issues->count(),
            'affected_files' => $issues->pluck('file_path')->unique()->count(), // Changed from files_with_issues
            'files_with_issues' => $issues->pluck('file_path')->unique()->count(), // Keep for backward compatibility
            'severity_counts' => $issues->groupBy('severity')->map->count()->toArray(),
            'category_counts' => $issues->groupBy('category')->map->count()->toArray()
        ];

        return $stats;
    }

    /**
     * Group issues by rule for display
     */
    protected function groupIssuesByRule(Collection $issues): Collection
    {
        return $issues->groupBy(function ($issue) {
            return $issue->rule_name ?? $issue->category;
        })->map(function ($groupedIssues, $ruleName) {
            return [
                'rule_name' => $ruleName,
                'issues' => $groupedIssues,
                'count' => $groupedIssues->count(),
                'severity' => $groupedIssues->first()->severity,
                'severity_name' => $groupedIssues->first()->severity, // Add expected key
                'category' => $groupedIssues->first()->category,
                'title' => $groupedIssues->first()->title ?? $ruleName, // Add expected key
                'description' => $groupedIssues->first()->description ?? '', // Add expected key
                'rule_id' => $groupedIssues->first()->rule_id ?? null, // Add expected key
                'suggestion' => $groupedIssues->first()->suggestion ?? null, // Add expected key
                'total_occurrences' => $groupedIssues->count(), // Add expected key (same as count)
                'instances' => $groupedIssues->map(function ($issue) {
                    $codeSnippet = $this->codeDisplayService->getCodeSnippet(
                        $issue->file_path, 
                        $issue->line_number ?? 1, 
                        2
                    );
                    
                    return [
                        'id' => $issue->id,
                        'line_number' => $issue->line_number,
                        'column_number' => $issue->column_number,
                        'code_snippet' => $codeSnippet['lines'] ?? null,
                        'fixed' => $issue->fixed,
                        'fix_method' => $issue->fix_method,
                        'fixed_at' => $issue->fixed_at,
                        'created_at' => $issue->created_at,
                        'ai_fix' => $this->parseAiFixForDisplay($issue->ai_fix),
                        'ai_explanation' => $issue->ai_explanation ?? null,
                        'ai_confidence' => $issue->ai_confidence ?? null,
                        'severity' => $issue->severity,
                        'category' => $issue->category,
                        'title' => $issue->title,
                        'description' => $issue->description,
                        'file_path' => $issue->file_path,
                        'rule_name' => $issue->rule_name,
                    ];
                })->toArray(),
            ];
        });
    }

    /**
     * Parse AI fix data for display purposes
     */
    protected function parseAiFixForDisplay(?string $aiFixData): ?string
    {
        if (empty($aiFixData)) {
            return null;
        }

        // Try to parse as JSON first
        $jsonData = json_decode(trim($aiFixData), true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            // Successfully parsed JSON - extract the code/explanation for display
            if (isset($jsonData['code']) && !empty($jsonData['code'])) {
                return $jsonData['code'];
            } elseif (isset($jsonData['explanation']) && !empty($jsonData['explanation'])) {
                return $jsonData['explanation'];
            }
        }

        // Fallback to original content if not JSON or no useful content
        return $aiFixData;
    }

    /**
     * Get severity counts for issues
     */
    protected function getSeverityCounts(Collection $issues): array
    {
        return $issues->groupBy('severity')->map->count()->toArray();
    }

    /**
     * Calculate issue counts for directories (recursive)
     */
    protected function calculateDirectoryIssueCounts(array $structure): array
    {
        // Initialize highest_severity if not already set
        $structure['highest_severity'] = $structure['highest_severity'] ?? null;
        
        if (isset($structure['directories'])) {
            foreach ($structure['directories'] as &$directory) {
                $directory = $this->calculateDirectoryIssueCounts($directory);
                
                // Add directory counts to parent
                $structure['issue_count'] = ($structure['issue_count'] ?? 0) + $directory['issue_count'];
                $structure['issues_count'] = ($structure['issues_count'] ?? 0) + $directory['issues_count'];
                $structure['resolved_count'] = ($structure['resolved_count'] ?? 0) + $directory['resolved_count'];
                
                // Update highest severity
                $structure['highest_severity'] = $this->getHighestSeverity(
                    $structure['highest_severity'], 
                    $directory['highest_severity']
                );
                
                foreach ($directory['severity_counts'] as $severity => $count) {
                    $structure['severity_counts'][$severity] = ($structure['severity_counts'][$severity] ?? 0) + $count;
                }
            }
        }

        if (isset($structure['files'])) {
            foreach ($structure['files'] as $file) {
                $structure['issue_count'] = ($structure['issue_count'] ?? 0) + $file['issue_count'];
                $structure['issues_count'] = ($structure['issues_count'] ?? 0) + $file['issues_count'];
                $structure['resolved_count'] = ($structure['resolved_count'] ?? 0) + $file['resolved_count'];
                
                // Update highest severity
                $structure['highest_severity'] = $this->getHighestSeverity(
                    $structure['highest_severity'], 
                    $file['highest_severity']
                );
                
                foreach ($file['severity_counts'] as $severity => $count) {
                    $structure['severity_counts'][$severity] = ($structure['severity_counts'][$severity] ?? 0) + $count;
                }
            }
        }

        return $structure;
    }

    /**
     * Get directory statistics
     */
    public function getDirectoryStats(Scan $scan, string $directoryPath, array $filters): array
    {
        $query = $scan->issues();
        
        if ($directoryPath) {
            $query->where('file_path', 'like', $directoryPath . '/%');
        }
        
        $this->applyFilters($query, $filters);
        
        return [
            'total_issues' => $query->count(),
            'severity_breakdown' => $query->selectRaw('severity, count(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity')
                ->toArray(),
            'category_breakdown' => $query->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray()
        ];
    }
}