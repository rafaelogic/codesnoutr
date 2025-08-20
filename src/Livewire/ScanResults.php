<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Illuminate\Support\Collection;

class ScanResults extends Component
{
    use WithPagination;

    public $scanId;
    public $scan;
    public $selectedSeverity = 'all';
    public $selectedCategory = 'all';
    public $selectedFile = 'all';
    public $searchTerm = '';
    public $sortBy = 'severity';
    public $sortDirection = 'desc';
    public $showFixSuggestions = true;
    public $selectedIssues = [];
    public $showBulkActions = false;
    public $viewMode = 'file-grouped'; // 'grouped', 'file-grouped', or 'detailed'
    public $maxGroupsPerPage = 10; // Limit groups to prevent memory issues
    public $fileGroupsPerPage = 5; // Files per page in file-grouped view
    public $issuesPerFile = 10; // Issues per file initially loaded
    public $currentFileGroupPage = 1; // Current page for file groups
    public $allFileGroups = []; // Store all loaded file groups
    public $loadedFileGroups = []; // Store loaded file group data (for individual file issues)
    public $loadingFiles = []; // Track which files are currently loading
    public $expandedFiles = []; // Track which files are expanded to show all issues
    public $expandedIssues = []; // Track which individual issues are expanded in detailed view
    public $isLoading = false; // Global loading state

    protected $queryString = [
        'selectedSeverity' => ['except' => 'all'],
        'selectedCategory' => ['except' => 'all'],
        'selectedFile' => ['except' => 'all'],
        'searchTerm' => ['except' => ''],
        'sortBy' => ['except' => 'severity'],
        'sortDirection' => ['except' => 'desc'],
        'viewMode' => ['except' => 'file-grouped'],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'scan-completed' => 'refreshResults',
        'issue-resolved' => 'refreshResults',
        'bulk-action-completed' => 'refreshResults',
    ];

    public function mount($scanId = null)
    {
        if ($scanId) {
            $this->scanId = $scanId;
            $this->loadScan();
        }
    }

    public function render()
    {
        // Clear memory before processing
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        if ($this->viewMode === 'grouped') {
            $groupedIssues = $this->getGroupedIssues();
            $fileGroupedIssues = null;
            $issues = null;
        } elseif ($this->viewMode === 'file-grouped') {
            // For file-grouped view, only load summary data initially
            $fileGroupedIssues = $this->getFileGroupSummaries();
            $groupedIssues = null;
            $issues = null;
        } else {
            $issues = $this->getFilteredIssues();
            $groupedIssues = null;
            $fileGroupedIssues = null;
        }
        
        $stats = $this->getIssueStats();
        $files = $this->getUniqueFiles();

        return view('codesnoutr::livewire.scan-results', [
            'issues' => $issues,
            'groupedIssues' => $groupedIssues,
            'fileGroupedIssues' => $fileGroupedIssues,
            'stats' => $stats,
            'files' => $files,
            'severityOptions' => $this->getSeverityOptions(),
            'categoryOptions' => $this->getCategoryOptions(),
        ]);
    }

    protected function loadScan()
    {
        if ($this->scanId) {
            // Load scan without eager loading all issues to save memory
            $this->scan = Scan::find($this->scanId);
        }
    }

    protected function getFilteredIssues()
    {
        if (!$this->scan) {
            return collect();
        }

        $query = $this->scan->issues();

        // Apply filters
        if ($this->selectedSeverity !== 'all') {
            $query->where('severity', $this->selectedSeverity);
        }

        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        if ($this->selectedFile !== 'all') {
            $query->where('file_path', $this->selectedFile);
        }

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('file_path', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(15);
    }

    protected function getFileGroupedIssues()
    {
        if (!$this->scan) {
            return collect();
        }

        // Return accumulated file groups from all loaded pages
        return collect($this->allFileGroups);
    }

    protected function getFileGroupSummaries($loadNew = false)
    {
        if (!$this->scan) {
            return collect();
        }

        if ($loadNew || empty($this->allFileGroups)) {
            $this->loadFileGroupPage($this->currentFileGroupPage);
        }

        return collect($this->allFileGroups);
    }

    protected function loadFileGroupPage($page)
    {
        if (!$this->scan) {
            return;
        }

        $query = $this->scan->issues();

        // Apply filters
        if ($this->selectedSeverity !== 'all') {
            $query->where('severity', $this->selectedSeverity);
        }

        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        if ($this->selectedFile !== 'all') {
            $query->where('file_path', $this->selectedFile);
        }

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('file_path', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Use database aggregation to get file summaries without loading all issues
        $fileGroups = $query->selectRaw('
                file_path,
                COUNT(*) as total_issues,
                COUNT(CASE WHEN fixed = 1 THEN 1 END) as resolved_issues,
                MAX(CASE 
                    WHEN severity = "critical" THEN 5
                    WHEN severity = "high" THEN 4
                    WHEN severity = "medium" THEN 3
                    WHEN severity = "low" THEN 2
                    WHEN severity = "info" THEN 1
                    ELSE 0
                END) as highest_severity_level
            ')
            ->groupBy('file_path')
            ->orderByRaw('highest_severity_level DESC, total_issues DESC')
            ->offset(($page - 1) * $this->fileGroupsPerPage)
            ->limit($this->fileGroupsPerPage)
            ->get();

        $newFileGroups = $fileGroups->map(function ($group) {
            $severityMap = [5 => 'critical', 4 => 'high', 3 => 'medium', 2 => 'low', 1 => 'info'];
            $highestSeverity = $severityMap[$group->highest_severity_level] ?? 'info';
            
            return [
                'file_path' => $group->file_path,
                'file_name' => basename($group->file_path),
                'file_extension' => pathinfo($group->file_path, PATHINFO_EXTENSION),
                'total_issues' => (int)$group->total_issues,
                'resolved_issues' => (int)$group->resolved_issues,
                'pending_issues' => (int)$group->total_issues - (int)$group->resolved_issues,
                'highest_severity' => $highestSeverity,
                'issues_loaded' => false, // Flag to track if issues are loaded
                'issues' => collect(), // Will be loaded on demand
                'has_more_pages' => false, // Will be set when loading issues
                'current_page' => 1,
            ];
        });

        // If this is page 1, replace all file groups, otherwise append
        if ($page === 1) {
            $this->allFileGroups = $newFileGroups->toArray();
        } else {
            $this->allFileGroups = array_merge($this->allFileGroups, $newFileGroups->toArray());
        }
    }

    /**
     * Load issues for a specific file via AJAX
     */
    public function loadFileIssues($filePath, $page = 1)
    {
        $this->isLoading = true;
        $this->loadingFiles[] = $filePath;

        try {
            if (!$this->scan) {
                return;
            }

            $query = $this->scan->issues()->where('file_path', $filePath);

            // Apply current filters
            if ($this->selectedSeverity !== 'all') {
                $query->where('severity', $this->selectedSeverity);
            }

            if ($this->selectedCategory !== 'all') {
                $query->where('category', $this->selectedCategory);
            }

            if ($this->searchTerm) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
                });
            }

            // Get all issues for this file and group them by title
            $allIssues = $query->orderByRaw('
                    CASE 
                        WHEN severity = "critical" THEN 5
                        WHEN severity = "high" THEN 4
                        WHEN severity = "medium" THEN 3
                        WHEN severity = "low" THEN 2
                        WHEN severity = "info" THEN 1
                        ELSE 0
                    END DESC
                ')
                ->orderBy('line_number')
                ->get();

            // Group issues by title
            $groupedIssues = $allIssues->groupBy('title')->map(function ($issues, $title) use ($filePath) {
                $firstIssue = $issues->first();
                
                return [
                    'title' => $title,
                    'description' => $firstIssue->description,
                    'severity' => $issues->max(function ($issue) {
                        $priority = ['critical' => 5, 'high' => 4, 'medium' => 3, 'low' => 2, 'info' => 1];
                        return $priority[$issue->severity] ?? 0;
                    }),
                    'severity_name' => $this->getSeverityName($issues->max(function ($issue) {
                        $priority = ['critical' => 5, 'high' => 4, 'medium' => 3, 'low' => 2, 'info' => 1];
                        return $priority[$issue->severity] ?? 0;
                    })),
                    'category' => $firstIssue->category,
                    'rule_id' => $firstIssue->rule_id,
                    'suggestion' => $firstIssue->suggestion,
                    'total_occurrences' => $issues->count(),
                    'resolved_occurrences' => $issues->where('fixed', true)->count(),
                    'instances' => $issues->map(function ($issue) use ($filePath) {
                        return [
                            'id' => $issue->id,
                            'line_number' => $issue->line_number,
                            'column_number' => $issue->column_number,
                            'code_snippet' => $this->getCodeSnippet($filePath, $issue->line_number),
                            'fixed' => $issue->fixed,
                            'fix_method' => $issue->fix_method,
                            'fixed_at' => $issue->fixed_at,
                            'created_at' => $issue->created_at,
                        ];
                    })->sortBy('line_number')->values(),
                ];
            });

            // Apply pagination to grouped issues
            $paginatedGroups = $groupedIssues->forPage($page, $this->issuesPerFile);
            $hasMorePages = $groupedIssues->count() > ($page * $this->issuesPerFile);

            // Update the loaded file groups data
            $fileKey = md5($filePath);
            
            if (!isset($this->loadedFileGroups[$fileKey])) {
                $this->loadedFileGroups[$fileKey] = [
                    'file_path' => $filePath,
                    'issue_groups' => collect(),
                    'current_page' => 0,
                    'has_more_pages' => true,
                ];
            }

            if ($page === 1) {
                $this->loadedFileGroups[$fileKey]['issue_groups'] = $paginatedGroups;
            } else {
                $this->loadedFileGroups[$fileKey]['issue_groups'] = $this->loadedFileGroups[$fileKey]['issue_groups']->concat($paginatedGroups);
            }

            $this->loadedFileGroups[$fileKey]['current_page'] = $page;
            $this->loadedFileGroups[$fileKey]['has_more_pages'] = $hasMorePages;

            // Mark file as expanded
            if (!in_array($filePath, $this->expandedFiles)) {
                $this->expandedFiles[] = $filePath;
            }

        } finally {
            $this->isLoading = false;
            $this->loadingFiles = array_diff($this->loadingFiles, [$filePath]);
        }
    }

    /**
     * Get code snippet around a specific line
     */
    protected function getCodeSnippet($filePath, $lineNumber, $contextLines = 2)
    {
        try {
            // Check if file exists and is readable
            if (!file_exists($filePath) || !is_readable($filePath)) {
                return "// Code not available";
            }

            $lines = file($filePath, FILE_IGNORE_NEW_LINES);
            if ($lines === false) {
                return "// Code not available";
            }

            $totalLines = count($lines);
            $startLine = max(0, $lineNumber - $contextLines - 1);
            $endLine = min($totalLines - 1, $lineNumber + $contextLines - 1);

            $snippet = [];
            for ($i = $startLine; $i <= $endLine; $i++) {
                $lineNum = $i + 1;
                $isTargetLine = $lineNum == $lineNumber;
                $snippet[] = [
                    'number' => $lineNum,
                    'content' => $lines[$i] ?? '',
                    'is_target' => $isTargetLine
                ];
            }

            return $snippet;
        } catch (\Exception $e) {
            return "// Code snippet unavailable: " . $e->getMessage();
        }
    }

    /**
     * Get severity name from priority number
     */
    protected function getSeverityName($priority)
    {
        $severityMap = [5 => 'critical', 4 => 'high', 3 => 'medium', 2 => 'low', 1 => 'info'];
        return $severityMap[$priority] ?? 'info';
    }

    /**
     * Load more issues for a file (pagination)
     */
    public function loadMoreFileIssues($filePath)
    {
        $fileKey = md5($filePath);
        $currentPage = $this->loadedFileGroups[$fileKey]['current_page'] ?? 0;
        $this->loadFileIssues($filePath, $currentPage + 1);
    }

    /**
     * Toggle file expansion
     */
    public function toggleFileExpansion($filePath)
    {
        if (in_array($filePath, $this->expandedFiles)) {
            // Collapse file
            $this->expandedFiles = array_diff($this->expandedFiles, [$filePath]);
        } else {
            // Expand file - load issues if not already loaded
            $fileKey = md5($filePath);
            if (!isset($this->loadedFileGroups[$fileKey]) || $this->loadedFileGroups[$fileKey]['issue_groups']->isEmpty()) {
                $this->loadFileIssues($filePath);
            } else {
                $this->expandedFiles[] = $filePath;
            }
        }
    }

    /**
     * Toggle individual issue expansion in detailed view
     */
    public function toggleIssueExpansion($issueId)
    {
        if (in_array($issueId, $this->expandedIssues)) {
            // Collapse issue
            $this->expandedIssues = array_diff($this->expandedIssues, [$issueId]);
        } else {
            // Expand issue
            $this->expandedIssues[] = $issueId;
        }
    }

    /**
     * Load next page of file groups
     */
    public function loadMoreFileGroups()
    {
        $this->currentFileGroupPage++;
        $this->loadFileGroupPage($this->currentFileGroupPage);
    }

    /**
     * Get the loaded issue groups for a specific file
     */
    public function getLoadedFileIssueGroups($filePath)
    {
        $fileKey = md5($filePath);
        return $this->loadedFileGroups[$fileKey]['issue_groups'] ?? collect();
    }

    /**
     * Check if a file has more issues to load
     */
    public function fileHasMorePages($filePath)
    {
        $fileKey = md5($filePath);
        return $this->loadedFileGroups[$fileKey]['has_more_pages'] ?? false;
    }

    /**
     * Check if a file is currently loading
     */
    public function isFileLoading($filePath)
    {
        return in_array($filePath, $this->loadingFiles);
    }

    /**
     * Check if a file is expanded
     */
    public function isFileExpanded($filePath)
    {
        return in_array($filePath, $this->expandedFiles);
    }

    protected function getGroupedIssues()
    {
        if (!$this->scan) {
            return collect();
        }

        // Use simplified mode for large datasets
        if ($this->shouldUseSimplifiedMode()) {
            return $this->getSimplifiedGroupedIssues();
        }

        $query = $this->scan->issues();

        // Apply filters
        if ($this->selectedSeverity !== 'all') {
            $query->where('severity', $this->selectedSeverity);
        }

        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        if ($this->selectedFile !== 'all') {
            $query->where('file_path', $this->selectedFile);
        }

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('file_path', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Use database aggregation instead of loading all records into memory
        $groupedData = $query->selectRaw('
                title,
                MAX(description) as description,
                category,
                MAX(rule_id) as rule_id,
                MAX(suggestion) as suggestion,
                severity,
                COUNT(*) as issue_count,
                COUNT(DISTINCT file_path) as files_count,
                SUM(CASE WHEN fixed = 1 THEN 1 ELSE 0 END) as resolved_count
            ')
            ->groupBy('title', 'category', 'severity')
            ->orderByRaw('issue_count DESC, 
                CASE 
                    WHEN severity = "critical" THEN 5
                    WHEN severity = "high" THEN 4
                    WHEN severity = "medium" THEN 3
                    WHEN severity = "low" THEN 2
                    WHEN severity = "info" THEN 1
                    ELSE 0
                END DESC
            ')
            ->limit($this->maxGroupsPerPage * 2) // Limit to prevent memory issues
            ->get();

        // Take only the first chunk to prevent memory overflow
        $limitedGroups = $groupedData->take($this->maxGroupsPerPage);

        // Now get the files for each group more efficiently
        return $limitedGroups->map(function ($group) use ($query) {
            // Clone the base query and add group-specific filters
            $filesQuery = clone $query;
            $filesQuery->where('title', $group->title)
                      ->where('category', $group->category)
                      ->where('severity', $group->severity);

            // Get only the necessary fields and limit to prevent memory issues
            $files = $filesQuery->select([
                'id', 'file_path', 'line_number', 'column_number', 
                'fixed', 'fix_method', 'fixed_at', 'created_at'
            ])
            ->orderBy('file_path')
            ->limit(100) // Limit files per group to prevent memory issues
            ->get()
            ->map(function ($issue) {
                return [
                    'id' => $issue->id,
                    'file_path' => $issue->file_path,
                    'line_number' => $issue->line_number,
                    'column_number' => $issue->column_number,
                    'fixed' => $issue->fixed,
                    'fix_method' => $issue->fix_method,
                    'fixed_at' => $issue->fixed_at,
                    'created_at' => $issue->created_at,
                ];
            });

            return [
                'title' => $group->title,
                'description' => $group->description ?? 'No description available',
                'category' => $group->category,
                'rule_id' => $group->rule_id,
                'rule' => $group->rule_id, // For backward compatibility
                'suggestion' => $group->suggestion,
                'severity' => $group->severity,
                'count' => (int)$group->issue_count,
                'files' => $files,
                'files_count' => (int)$group->files_count,
                'resolved_count' => (int)$group->resolved_count,
                'issues' => $files, // For backward compatibility with the view
                'simplified' => false,
                'has_files' => $files->count() > 0, // Debug flag
            ];
        });
    }

    protected function getIssueStats()
    {
        if (!$this->scan) {
            return [
                'total' => 0,
                'by_severity' => [],
                'by_category' => [],
                'resolved_count' => 0,
                'ignored_count' => 0,
                'false_positive_count' => 0,
            ];
        }

        // Use database aggregation instead of loading all issues
        $severityStats = $this->scan->issues()
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity');

        $categoryStats = $this->scan->issues()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        $fixStats = $this->scan->issues()
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN fix_method = "manual" THEN 1 END) as resolved_count,
                COUNT(CASE WHEN fix_method = "ignored" THEN 1 END) as ignored_count,
                COUNT(CASE WHEN fix_method = "false_positive" THEN 1 END) as false_positive_count
            ')
            ->first();

        return [
            'total' => $fixStats->total ?? 0,
            'by_severity' => $severityStats->toArray(),
            'by_category' => $categoryStats->toArray(),
            'resolved_count' => $fixStats->resolved_count ?? 0,
            'ignored_count' => $fixStats->ignored_count ?? 0,
            'false_positive_count' => $fixStats->false_positive_count ?? 0,
        ];
    }

    protected function getUniqueFiles()
    {
        if (!$this->scan) {
            return collect();
        }

        // Use distinct() at the database level instead of loading all records
        return $this->scan->issues()
            ->distinct()
            ->orderBy('file_path')
            ->pluck('file_path');
    }

    protected function getSeverityOptions()
    {
        return [
            'all' => 'All Severities',
            'critical' => 'Critical',
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
            'info' => 'Info',
        ];
    }

    protected function getCategoryOptions()
    {
        return [
            'all' => 'All Categories',
            'security' => 'Security',
            'performance' => 'Performance',
            'quality' => 'Code Quality',
            'laravel' => 'Laravel Best Practices',
        ];
    }

    public function setSeverityFilter($severity)
    {
        $this->selectedSeverity = $severity;
        $this->resetPage();
        $this->resetFileGroupData();
    }

    public function setCategoryFilter($category)
    {
        $this->selectedCategory = $category;
        $this->resetPage();
        $this->resetFileGroupData();
    }

    public function setFileFilter($file)
    {
        $this->selectedFile = $file;
        $this->resetPage();
        $this->resetFileGroupData();
    }

    public function clearFilters()
    {
        $this->selectedSeverity = 'all';
        $this->selectedCategory = 'all';
        $this->selectedFile = 'all';
        $this->searchTerm = '';
        $this->resetPage();
        $this->resetFileGroupData();
    }

    protected function resetFileGroupData()
    {
        $this->currentFileGroupPage = 1;
        $this->allFileGroups = [];
        $this->loadedFileGroups = [];
        $this->expandedFiles = [];
        $this->expandedIssues = [];
        $this->loadingFiles = [];
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
        $this->resetFileGroupData();
    }

    public function hasMoreFileGroups()
    {
        if (!$this->scan) {
            return false;
        }

        $query = $this->scan->issues();

        // Apply filters
        if ($this->selectedSeverity !== 'all') {
            $query->where('severity', $this->selectedSeverity);
        }

        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        if ($this->selectedFile !== 'all') {
            $query->where('file_path', $this->selectedFile);
        }

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('file_path', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $totalFiles = $query->distinct('file_path')->count('file_path');
        $loadedFiles = $this->currentFileGroupPage * $this->fileGroupsPerPage;

        return $totalFiles > $loadedFiles;
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
        
        $this->resetPage();
    }

    /**
     * Load files for a specific group (used for on-demand loading)
     */
    public function loadGroupFiles($groupTitle, $groupCategory, $groupSeverity)
    {
        if (!$this->scan) {
            return collect();
        }

        $query = $this->scan->issues();

        // Apply current filters
        if ($this->selectedSeverity !== 'all') {
            $query->where('severity', $this->selectedSeverity);
        }

        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        if ($this->selectedFile !== 'all') {
            $query->where('file_path', $this->selectedFile);
        }

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('file_path', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Filter for this specific group
        $query->where('title', $groupTitle)
              ->where('category', $groupCategory)
              ->where('severity', $groupSeverity);

        return $query->select([
            'id', 'file_path', 'line_number', 'column_number', 
            'fixed', 'fix_method', 'fixed_at', 'created_at'
        ])
        ->orderBy('file_path')
        ->limit(100)
        ->get()
        ->map(function ($issue) {
            return [
                'id' => $issue->id,
                'file_path' => $issue->file_path,
                'line_number' => $issue->line_number,
                'column_number' => $issue->column_number,
                'fixed' => $issue->fixed,
                'fix_method' => $issue->fix_method,
                'fixed_at' => $issue->fixed_at,
                'created_at' => $issue->created_at,
            ];
        });
    }

    public function toggleIssueSelection($issueId)
    {
        if (in_array($issueId, $this->selectedIssues)) {
            $this->selectedIssues = array_diff($this->selectedIssues, [$issueId]);
        } else {
            $this->selectedIssues[] = $issueId;
        }

        $this->showBulkActions = !empty($this->selectedIssues);
    }

    public function selectAllIssues()
    {
        if ($this->viewMode === 'file-grouped') {
            // For file-grouped view, we need to get all issue IDs from the database
            // without loading them all into memory
            if (!$this->scan) {
                return;
            }

            $query = $this->scan->issues();
            
            // Apply current filters
            if ($this->selectedSeverity !== 'all') {
                $query->where('severity', $this->selectedSeverity);
            }

            if ($this->selectedCategory !== 'all') {
                $query->where('category', $this->selectedCategory);
            }

            if ($this->selectedFile !== 'all') {
                $query->where('file_path', $this->selectedFile);
            }

            if ($this->searchTerm) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('file_path', 'like', '%' . $this->searchTerm . '%');
                });
            }

            // Get only IDs to avoid memory issues
            $this->selectedIssues = $query->pluck('id')->toArray();
        } else {
            $issues = $this->getFilteredIssues();
            $this->selectedIssues = $issues->pluck('id')->toArray();
        }
        $this->showBulkActions = !empty($this->selectedIssues);
    }

    public function deselectAllIssues()
    {
        $this->selectedIssues = [];
        $this->showBulkActions = false;
    }

    public function resolveIssue($issueId)
    {
        $issue = Issue::find($issueId);
        if ($issue) {
            $filePath = $issue->file_path;
            $issue->update([
                'fixed' => true,
                'fixed_at' => now(),
                'fix_method' => 'manual'
            ]);
            
            // Refresh the file group data for this file
            $this->refreshFileGroup($filePath);
            $this->dispatch('issue-resolved', issueId: $issueId);
            
            // Redirect to scan results main display after successful resolution
            $this->dispatch('redirect-to-scan-results', scanId: $this->scanId);
        }
    }

    public function markAsIgnored($issueId)
    {
        $issue = Issue::find($issueId);
        if ($issue) {
            $filePath = $issue->file_path;
            $issue->update([
                'fixed' => true,
                'fix_method' => 'ignored'
            ]);
            
            // Refresh the file group data for this file
            $this->refreshFileGroup($filePath);
            $this->dispatch('issue-ignored', issueId: $issueId);
            
            // Redirect to scan results main display after successful action
            $this->dispatch('redirect-to-scan-results', scanId: $this->scanId);
        }
    }

    public function markAsFalsePositive($issueId)
    {
        $issue = Issue::find($issueId);
        if ($issue) {
            $filePath = $issue->file_path;
            $issue->update([
                'fixed' => true,
                'fix_method' => 'false_positive'
            ]);
            
            // Refresh the file group data for this file
            $this->refreshFileGroup($filePath);
            $this->dispatch('issue-false-positive', issueId: $issueId);
            
            // Redirect to scan results main display after successful action
            $this->dispatch('redirect-to-scan-results', scanId: $this->scanId);
        }
    }

    /**
     * Refresh loaded issues for a specific file
     */
    protected function refreshFileGroup($filePath)
    {
        $fileKey = md5($filePath);
        if (isset($this->loadedFileGroups[$fileKey]) && in_array($filePath, $this->expandedFiles)) {
            // Reload the first page of issues for this file
            $this->loadFileIssues($filePath, 1);
        }
    }

    public function bulkResolve()
    {
        Issue::whereIn('id', $this->selectedIssues)
            ->update([
                'fixed' => true,
                'fixed_at' => now(),
                'fix_method' => 'manual'
            ]);

        $this->selectedIssues = [];
        $this->showBulkActions = false;
        $this->dispatch('bulk-action-completed', action: 'resolved');
    }

    public function bulkIgnore()
    {
        Issue::whereIn('id', $this->selectedIssues)
            ->update([
                'fixed' => true,
                'fix_method' => 'ignored'
            ]);

        $this->selectedIssues = [];
        $this->showBulkActions = false;
        $this->dispatch('bulk-action-completed', action: 'ignored');
    }

    public function bulkMarkFalsePositive()
    {
        Issue::whereIn('id', $this->selectedIssues)
            ->update([
                'fixed' => true,
                'fix_method' => 'false_positive'
            ]);

        $this->selectedIssues = [];
        $this->showBulkActions = false;
        $this->dispatch('bulk-action-completed', action: 'false_positive');
    }

    public function exportResults($format = 'json')
    {
        if (!$this->scan) {
            return;
        }

        $issues = $this->getFilteredIssues();
        
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($issues);
            case 'pdf':
                return $this->exportToPdf($issues);
            default:
                return $this->exportToJson($issues);
        }
    }

    protected function exportToJson($issues)
    {
        $data = [
            'scan' => [
                'id' => $this->scan->id,
                'type' => $this->scan->type,
                'target' => $this->scan->target,
                'status' => $this->scan->status,
                'started_at' => $this->scan->started_at,
                'completed_at' => $this->scan->completed_at,
                'files_scanned' => $this->scan->files_scanned,
                'issues_found' => $this->scan->issues_found,
            ],
            'issues' => $issues->toArray(),
            'exported_at' => now()->toISOString(),
        ];

        $filename = 'scan-' . $this->scan->id . '-' . now()->format('Y-m-d-H-i-s') . '.json';
        
        $this->dispatch('download-file', [
            'content' => json_encode($data, JSON_PRETTY_PRINT),
            'filename' => $filename,
            'contentType' => 'application/json'
        ]);
    }

    protected function exportToCsv($issues)
    {
        $csv = "ID,File,Line,Column,Severity,Category,Title,Description,Status\n";
        
        foreach ($issues as $issue) {
            $csv .= sprintf(
                "%d,\"%s\",%d,%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $issue->id,
                str_replace('"', '""', $issue->file_path),
                $issue->line_number,
                $issue->column_number,
                $issue->severity,
                $issue->category,
                str_replace('"', '""', $issue->title),
                str_replace('"', '""', $issue->description),
                $issue->status
            );
        }

        $filename = 'scan-' . $this->scan->id . '-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $this->dispatch('download-file', [
            'content' => $csv,
            'filename' => $filename,
            'contentType' => 'text/csv'
        ]);
    }

    public function refreshResults()
    {
        $this->loadScan();
        $this->resetPage();
        $this->resetFileGroupData();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
        $this->resetFileGroupData();
    }

    public function getSeverityColor($severity)
    {
        return match($severity) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'blue',
            'info' => 'gray',
            default => 'gray'
        };
    }

    public function getSeverityIcon($severity)
    {
        return match($severity) {
            'critical' => 'exclamation-triangle',
            'high' => 'exclamation-circle',
            'medium' => 'exclamation',
            'low' => 'info-circle',
            'info' => 'information-circle',
            default => 'question-mark-circle'
        };
    }

    public function getCategoryIcon($category)
    {
        return match($category) {
            'security' => 'shield-exclamation',
            'performance' => 'lightning-bolt',
            'quality' => 'code',
            'laravel' => 'cog',
            default => 'document-text'
        };
    }

    /**
     * Clean up memory usage
     */
    protected function cleanupMemory()
    {
        // Clear any cached data
        unset($this->scan->issues);
        
        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    /**
     * Check if we should use simplified mode for large datasets
     */
    protected function shouldUseSimplifiedMode()
    {
        if (!$this->scan) {
            return false;
        }

        // Use simplified mode if there are too many issues
        $totalIssues = $this->scan->issues()->count();
        return $totalIssues > 1000; // Threshold for simplified mode
    }

    /**
     * Get simplified grouped issues for large datasets
     */
    protected function getSimplifiedGroupedIssues()
    {
        if (!$this->scan) {
            return collect();
        }

        $query = $this->scan->issues();

        // Apply filters
        if ($this->selectedSeverity !== 'all') {
            $query->where('severity', $this->selectedSeverity);
        }

        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        // Get only the basic grouping without files
        return $query->selectRaw('
                title,
                MAX(description) as description,
                severity,
                category,
                COUNT(*) as issue_count,
                COUNT(DISTINCT file_path) as files_count,
                SUM(CASE WHEN fixed = 1 THEN 1 ELSE 0 END) as resolved_count
            ')
            ->groupBy('title', 'severity', 'category')
            ->orderByRaw('issue_count DESC,
                CASE 
                    WHEN severity = "critical" THEN 5
                    WHEN severity = "high" THEN 4
                    WHEN severity = "medium" THEN 3
                    WHEN severity = "low" THEN 2
                    WHEN severity = "info" THEN 1
                    ELSE 0
                END DESC
            ')
            ->limit(20) // Further limit for large datasets
            ->get()
            ->map(function ($group) {
                return [
                    'title' => $group->title,
                    'description' => $group->description ?? 'Click to view details...', 
                    'category' => $group->category,
                    'rule_id' => null,
                    'rule' => null,
                    'suggestion' => null,
                    'severity' => $group->severity,
                    'count' => (int)$group->issue_count,
                    'files' => collect(), // Empty files collection
                    'files_count' => (int)$group->files_count,
                    'resolved_count' => (int)$group->resolved_count,
                    'issues' => collect(),
                    'simplified' => true, // Flag to indicate simplified mode
                    'has_files' => false, // No files loaded in simplified mode
                ];
            });
    }
}
