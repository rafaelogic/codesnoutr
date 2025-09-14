<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ScanResults extends Component
{
    use WithPagination;

    public $scanId;
    public $scan = null; // Explicitly set to null
    public $selectedSeverity = 'all';
    public $selectedCategory = 'all';
    public $selectedFile = 'all';
    public $searchTerm = '';
    public $sortBy = 'severity';
    public $sortDirection = 'desc';
    public $showFixSuggestions = true;
    public $selectedIssues = [];
    public $showBulkActions = false;
    // Removed viewMode - always use two-column view
    // Temporary property to handle old URLs with viewMode parameter
    public $viewMode = 'two-column';
    public $maxGroupsPerPage = 10; // Keep for backward compatibility with existing data processing
    public $issuesPerFile = 10; // Issues per file initially loaded
    // Removed fileGroupsPerPage and currentFileGroupPage - not needed for two-column view
    public $allFileGroups = []; // Store all loaded file groups
    public $loadedFileGroups = []; // Store loaded file group data (for individual file issues)
    public $loadingFiles = []; // Track which files are currently loading
    public $expandedFiles = []; // Track which files are expanded to show all issues
    public $expandedIssues = []; // Track which individual issues are expanded in detailed view
    public $isLoading = false; // Global loading state
    
    // Two-column view properties
    public $selectedFilePath = null; // Currently selected file in two-column view
    public $directoryTree = []; // Directory structure with files that have issues
    public $expandedDirectories = []; // Track which directories are expanded
    public $selectedFileIssues = null; // Issues for the selected file
    public $selectedFileStats = null; // Stats for the selected file
    public $directoryStats = []; // Overall directory statistics
    public $fileLoading = false; // Track if file issues are being loaded
    public $issuesPerPage = 25;
    public $currentIssuePage = 1; // Current page for issues
    public $totalIssuePages = 1; // Total pages for issues
    public $maxInstancesPerIssue = 3; // Limit instances per issue group
    
    // Directory and file pagination properties
    public $directoriesPerPage = 10; // Number of directories to show per page
    public $currentDirectoryPage = 1; // Current directory page
    public $totalDirectoryPages = 1; // Total directory pages
    public $filesPerDirectoryPage = 20; // Files per page within a directory
    public $directoryFilePage = []; // Track current page for each directory
    public $paginatedDirectoryTree = []; // Paginated version of directory tree
    
    // Lazy loading flags
    public $lazyLoadEnabled = true; // Enable lazy loading
    public $initialLoadComplete = false; // Track if initial directory structure is loaded

    protected $queryString = [
        'selectedSeverity' => ['except' => 'all'],
        'selectedCategory' => ['except' => 'all'],
        'selectedFile' => ['except' => 'all'],
        'searchTerm' => ['except' => ''],
        'sortBy' => ['except' => 'severity'],
        'sortDirection' => ['except' => 'desc'],
        'selectedFilePath' => ['except' => null],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'scan-completed' => 'refreshResults',
        'issue-resolved' => 'refreshResults',
        'bulk-action-completed' => 'refreshResults',
    ];

    protected function getListeners()
    {
        return [
            'scan-completed' => 'refreshResults',
            'issue-resolved' => 'refreshResults',
            'bulk-action-completed' => 'refreshResults',
        ];
    }

    public function boot()
    {
        // Ensure all array properties are properly initialized as arrays
        $this->allFileGroups = is_array($this->allFileGroups) ? $this->allFileGroups : [];
        $this->loadedFileGroups = is_array($this->loadedFileGroups) ? $this->loadedFileGroups : [];
        $this->directoryTree = is_array($this->directoryTree) ? $this->directoryTree : [];
        $this->expandedDirectories = is_array($this->expandedDirectories) ? $this->expandedDirectories : [];
        $this->directoryStats = is_array($this->directoryStats) ? $this->directoryStats : [];
        $this->selectedIssues = is_array($this->selectedIssues) ? $this->selectedIssues : [];
        $this->loadingFiles = is_array($this->loadingFiles) ? $this->loadingFiles : [];
        $this->expandedFiles = is_array($this->expandedFiles) ? $this->expandedFiles : [];
        $this->expandedIssues = is_array($this->expandedIssues) ? $this->expandedIssues : [];
    }

    public function getName()
    {
        return 'codesnoutr-scan-results';
    }

    public function mount($scanId = null)
    {
        // Initialize all properties with safe defaults
        $this->scanId = $scanId;
        $this->scan = null;
        $this->selectedSeverity = 'all';
        $this->selectedCategory = 'all';
        $this->selectedFile = 'all';
        $this->searchTerm = '';
        $this->sortBy = 'severity';
        $this->sortDirection = 'desc';
        $this->showFixSuggestions = true;
        $this->selectedIssues = [];
        $this->showBulkActions = false;
        $this->maxGroupsPerPage = 10;
        $this->issuesPerFile = 10;
        $this->allFileGroups = [];
        $this->loadedFileGroups = [];
        $this->loadingFiles = [];
        $this->expandedFiles = [];
        $this->expandedIssues = [];
        $this->isLoading = false;
        $this->selectedFilePath = null;
        $this->directoryTree = [];
        $this->expandedDirectories = [];
        $this->selectedFileIssues = null;
        $this->selectedFileStats = null;
        $this->directoryStats = [];
        $this->fileLoading = false;
        
        // Initialize pagination properties
        $this->directoriesPerPage = 10;
        $this->currentDirectoryPage = 1;
        $this->totalDirectoryPages = 1;
        $this->filesPerDirectoryPage = 20;
        $this->directoryFilePage = [];
        $this->paginatedDirectoryTree = [];
        $this->lazyLoadEnabled = true;
        $this->initialLoadComplete = false;

        Log::info('ScanResults component mounting', [
            'scanId' => $scanId,
            'component_id' => $this->getId()
        ]);

        if ($scanId) {
            $this->loadScan();
            
            // Always initialize two-column view (the only view now)
            $this->initializeLazyTwoColumnView();
        } else {
            Log::warning('ScanResults component mounted without scanId');
        }
    }

    public function render()
    {
        try {
            // Log that the component is rendering
            Log::info('ScanResults component rendering', [
                'scanId' => $this->scanId,
                'scan_exists' => $this->scan ? 'yes' : 'no',
                'component_id' => $this->getId(),
                'view' => 'two-column', // Always two-column view now
                'selectedFilePath' => $this->selectedFilePath
            ]);

            // Clear memory before processing
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            // Ensure we have safe default values
            $this->directoryTree = $this->directoryTree ?? [];
            $this->directoryStats = $this->directoryStats ?? [];
            $this->expandedDirectories = $this->expandedDirectories ?? [];
            $this->selectedFileIssues = $this->selectedFileIssues ?? collect();
            $this->selectedFileStats = $this->selectedFileStats ?? null;

            // Always use two-column view - lazy loading approach
            if (!$this->initialLoadComplete) {
                // Try the new pagination first, fall back to old method if needed
                try {
                    $this->loadPaginatedDirectoryStructure();
                    $this->initialLoadComplete = true;
                } catch (\Exception $e) {
                    Log::warning('Paginated directory loading failed, falling back to old method', [
                        'error' => $e->getMessage()
                    ]);
                    // Fall back to the old directory loading method
                    $this->loadDirectoryTree();
                    $this->paginatedDirectoryTree = $this->directoryTree;
                    $this->initialLoadComplete = true;
                }
            }
            
            Log::info('Two-column lazy render', [
                'paginatedDirectories_count' => count($this->paginatedDirectoryTree),
                'currentDirectoryPage' => $this->currentDirectoryPage,
                'totalDirectoryPages' => $this->totalDirectoryPages,
                'selectedFilePath' => $this->selectedFilePath ? basename($this->selectedFilePath) : null,
                'selectedFileIssues_count' => $this->selectedFileIssues ? $this->selectedFileIssues->count() : 0
            ]);
            
            return view('codesnoutr::livewire.scan-results-two-column', [
                'scan' => $this->scan,
                'stats' => $this->getIssueStats(),
                'directoryTree' => $this->paginatedDirectoryTree,
                'directoryStats' => $this->directoryStats,
                'selectedFileIssues' => $this->selectedFileIssues,
                'selectedFileStats' => $this->selectedFileStats,
                'severityOptions' => $this->getSeverityOptions(),
                'categoryOptions' => $this->getCategoryOptions(),
                'expandedDirectories' => $this->expandedDirectories,
                'currentDirectoryPage' => $this->currentDirectoryPage,
                'totalDirectoryPages' => $this->totalDirectoryPages,
                'directoryFilePage' => $this->directoryFilePage,
            ]);
            
        } catch (\Exception $e) {
            Log::error('=== ScanResults RENDER ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'scanId' => $this->scanId,
                'view' => 'two-column', // Always two-column view now
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a safe fallback view with minimal data
            return view('codesnoutr::livewire.scan-results-two-column', [
                'scan' => $this->scan,
                'stats' => ['total' => 0, 'by_severity' => [], 'by_category' => [], 'resolved_count' => 0, 'ignored_count' => 0, 'false_positive_count' => 0],
                'directoryTree' => [],
                'directoryStats' => ['affected_files' => 0, 'total_issues' => 0, 'resolved_issues' => 0],
                'selectedFileIssues' => collect(),
                'selectedFileStats' => null,
                'severityOptions' => $this->getSeverityOptions(),
                'categoryOptions' => $this->getCategoryOptions(),
                'expandedDirectories' => [],
            ]);
        }
    }

    protected function loadScan()
    {
        try {
            if ($this->scanId) {
                // Load scan without eager loading all issues to save memory
                $this->scan = Scan::find($this->scanId);
                
                if (!$this->scan) {
                    Log::warning('Scan not found', ['scanId' => $this->scanId]);
                    // Don't redirect here, just set scan to null
                    $this->scan = null;
                }
            } else {
                $this->scan = null;
            }
        } catch (\Exception $e) {
            Log::error('Error loading scan', [
                'scanId' => $this->scanId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->scan = null;
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
        try {
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
                    COUNT(CASE WHEN fixed = 1 THEN 1 END) as resolved_count,
                    COUNT(CASE WHEN fix_method = "ignored" THEN 1 END) as ignored_count,
                    COUNT(CASE WHEN fix_method = "false_positive" THEN 1 END) as false_positive_count
                ')
                ->first();

            // Add some debug logging to track any discrepancies
            Log::info('Issue stats calculated', [
                'scan_id' => $this->scanId,
                'database_total' => $fixStats->total ?? 0,
                'scan_issues_found' => $this->scan->issues_found ?? 0,
                'scan_total_issues' => $this->scan->total_issues ?? 0,
                'severity_stats' => $severityStats->toArray(),
                'category_stats' => $categoryStats->toArray(),
                'resolved_count' => $fixStats->resolved_count ?? 0,
            ]);

            return [
                'total' => $fixStats->total ?? 0,
                'by_severity' => $severityStats->toArray(),
                'by_category' => $categoryStats->toArray(),
                'resolved_count' => $fixStats->resolved_count ?? 0,
                'ignored_count' => $fixStats->ignored_count ?? 0,
                'false_positive_count' => $fixStats->false_positive_count ?? 0,
            ];
            
        } catch (\Exception $e) {
            Log::error('Error getting issue stats', [
                'scanId' => $this->scanId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'total' => 0,
                'by_severity' => [],
                'by_category' => [],
                'resolved_count' => 0,
                'ignored_count' => 0,
                'false_positive_count' => 0,
            ];
        }
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

    /**
     * Watch for filter changes and reload directory tree
     */
    public function updatedSelectedSeverity()
    {
        $this->currentDirectoryPage = 1;
        $this->selectedFilePath = null;
        $this->selectedFileIssues = null;
        $this->loadPaginatedDirectoryStructure();
    }

    public function updatedSelectedCategory()
    {
        $this->currentDirectoryPage = 1;
        $this->selectedFilePath = null;
        $this->selectedFileIssues = null;
        $this->loadPaginatedDirectoryStructure();
    }

    protected function resetFileGroupData()
    {
        // Removed currentFileGroupPage - not needed for two-column view
        $this->allFileGroups = [];
        $this->loadedFileGroups = [];
        $this->expandedFiles = [];
        $this->expandedIssues = [];
        $this->loadingFiles = [];
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
        // Simplified for two-column view only
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

        // Get all filtered issue IDs
        $this->selectedIssues = $query->pluck('id')->toArray();
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
        
        // For two-column view, also reload directory tree with filters
        $this->currentDirectoryPage = 1;
        $this->selectedFilePath = null;
        $this->selectedFileIssues = null;
        $this->loadPaginatedDirectoryStructure();
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

    // Two-Column View Methods

    /**
     * Load directory tree with files that have issues
     */
    protected function loadDirectoryTree()
    {
        try {
            Log::info('loadDirectoryTree called', ['scanId' => $this->scanId, 'scan_exists' => $this->scan ? 'yes' : 'no']);
            
            if (!$this->scan) {
                Log::warning('loadDirectoryTree: No scan available');
                $this->directoryTree = [];
                $this->directoryStats = ['affected_files' => 0, 'total_issues' => 0, 'resolved_issues' => 0];
                return;
            }

            // Get unique file paths with basic stats including highest severity
            $files = $this->scan->issues()
                ->select('file_path')
                ->selectRaw('COUNT(*) as issues_count')
                ->selectRaw('SUM(CASE WHEN fixed = 1 THEN 1 ELSE 0 END) as resolved_count')
                ->selectRaw('MAX(CASE 
                    WHEN severity = "critical" THEN 5
                    WHEN severity = "high" THEN 4  
                    WHEN severity = "medium" THEN 3
                    WHEN severity = "low" THEN 2
                    ELSE 1
                END) as severity_priority')
                ->groupBy('file_path')
                ->orderBy('file_path')
                ->get();

            Log::info('loadDirectoryTree: Raw files loaded', ['files_count' => $files->count()]);

            $filesWithStats = $files->map(function ($item) {
                $severityMap = [5 => 'critical', 4 => 'high', 3 => 'medium', 2 => 'low', 1 => 'info'];
                return [
                    'path' => $item->file_path,
                    'name' => basename($item->file_path),
                    'issues_count' => $item->issues_count,
                    'resolved_count' => $item->resolved_count,
                    'highest_severity' => $severityMap[$item->severity_priority] ?? 'info',
                ];
            });

        } catch (\Exception $e) {
            Log::error('Error loading directory tree', [
                'scanId' => $this->scanId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Set safe defaults
            $this->directoryTree = [];
            $this->directoryStats = ['affected_files' => 0, 'total_issues' => 0, 'resolved_issues' => 0];
            return;
        }

        // Group files by directory
        $tree = [];
        $totalFiles = 0;
        $totalIssues = 0;
        $totalResolved = 0;

        foreach ($filesWithStats as $file) {
            $directory = dirname($file['path']);
            if ($directory === '.') {
                $directory = '';
            }

            if (!isset($tree[$directory])) {
                $tree[$directory] = [];
            }

            $tree[$directory][] = $file;
            $totalFiles++;
            $totalIssues += $file['issues_count'];
            $totalResolved += $file['resolved_count'];
        }

        // Sort directories: root first, then alphabetically
        uksort($tree, function ($a, $b) {
            if ($a === '') return -1;
            if ($b === '') return 1;
            return strcmp($a, $b);
        });

        $this->directoryTree = $tree;
        $this->directoryStats = [
            'affected_files' => $totalFiles,
            'total_issues' => $totalIssues,
            'resolved_issues' => $totalResolved,
        ];

        Log::info('loadDirectoryTree completed', [
            'directoryTree_count' => count($tree),
            'directoryStats' => $this->directoryStats
        ]);

        // Only set initial expanded directories if none are set yet (first load)
        // Don't override user's manual toggles
        if (empty($this->expandedDirectories)) {
            Log::info('Initializing expandedDirectories - expanding all directories for better UX');
            
            if ($this->selectedFilePath) {
                // Expand the directory containing the selected file
                $selectedDirectory = dirname($this->selectedFilePath);
                if ($selectedDirectory === '.') {
                    $selectedDirectory = '';
                }
                $this->expandedDirectories = [$selectedDirectory];
            } else {
                // On first load, expand all directories for better UX
                $this->expandedDirectories = array_keys($tree);
            }
        }
    }

    /**
     * Load issues for the selected file
     */
    protected function loadSelectedFileIssues()
    {
        if (!$this->selectedFilePath || !$this->scan) {
            Log::info('loadSelectedFileIssues - early return', [
                'selectedFilePath' => $this->selectedFilePath,
                'scan_exists' => $this->scan ? 'yes' : 'no'
            ]);
            $this->selectedFileIssues = collect();
            $this->selectedFileStats = null;
            return;
        }

        try {
            Log::info('loadSelectedFileIssues starting', [
                'selectedFilePath' => $this->selectedFilePath,
                'scanId' => $this->scanId
            ]);

            $query = $this->scan->issues()->where('file_path', $this->selectedFilePath);

            // Apply filters
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

            $issues = $query->orderByRaw('
                CASE 
                    WHEN severity = "critical" THEN 5
                    WHEN severity = "high" THEN 4
                    WHEN severity = "medium" THEN 3
                    WHEN severity = "low" THEN 2
                    ELSE 1
                END DESC
            ')
            ->orderBy('line_number')
            ->get();

            Log::info('loadSelectedFileIssues - loaded raw issues', [
                'count' => $issues->count()
            ]);

            // Calculate total pages for pagination
            $totalIssues = $issues->count();
            $this->totalIssuePages = max(1, ceil($totalIssues / $this->issuesPerPage));
            
            // Ensure current page is valid
            if ($this->currentIssuePage > $this->totalIssuePages) {
                $this->currentIssuePage = 1;
            }

            // Group issues first, then paginate the groups
            $groupedIssues = $issues->groupBy(function ($issue) {
                return $issue->title . '|' . $issue->category . '|' . $issue->severity;
            })->map(function ($group) {
                $first = $group->first();
                return [
                    'title' => $first->title,
                    'description' => $first->description,
                    'category' => $first->category,
                    'severity_name' => $first->severity,
                    'rule_id' => $first->rule_id,
                    'suggestion' => $first->suggestion,
                    'total_occurrences' => $group->count(),
                    'resolved_occurrences' => $group->where('fixed', true)->count(),
                    'instances' => $group->take($this->maxInstancesPerIssue)->map(function ($issue) {
                        return [
                            'id' => $issue->id,
                            'line_number' => $issue->line_number,
                            'column_number' => $issue->column_number,
                            'code_snippet' => $this->extractCodeSnippet($issue),
                            'fixed' => $issue->fixed,
                            'fix_method' => $issue->fix_method,
                            'fixed_at' => $issue->fixed_at,
                        ];
                    })->values()->all(),
                ];
            })->values();

            Log::info('loadSelectedFileIssues - grouped issues', [
                'groups_count' => $groupedIssues->count(),
                'totalIssuePages' => $this->totalIssuePages,
                'currentIssuePage' => $this->currentIssuePage
            ]);

            // Apply pagination to groups (limit to issuesPerPage groups)
            $offset = ($this->currentIssuePage - 1) * $this->issuesPerPage;
            $paginatedGroups = $groupedIssues->slice($offset, $this->issuesPerPage)->values();

            $this->selectedFileIssues = $paginatedGroups;
            
            // Calculate file stats
            $this->selectedFileStats = [
                'total_issues' => $issues->count(),
                'resolved_issues' => $issues->where('fixed', true)->count(),
                'pending_issues' => $issues->where('fixed', false)->count(),
                'highest_severity' => $issues->min(function ($issue) {
                    return match($issue->severity) {
                        'critical' => 1,
                        'high' => 2,
                        'medium' => 3,
                        'low' => 4,
                        default => 5
                    };
                }),
            ];

            Log::info('loadSelectedFileIssues completed', [
                'selectedFileIssues_count' => $this->selectedFileIssues->count(),
                'selectedFileStats' => $this->selectedFileStats
            ]);

        } catch (\Exception $e) {
            Log::error('Error in loadSelectedFileIssues', [
                'selectedFilePath' => $this->selectedFilePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Set safe defaults
            $this->selectedFileIssues = collect();
            $this->selectedFileStats = [
                'total_issues' => 0,
                'resolved_issues' => 0,
                'pending_issues' => 0,
                'highest_severity' => null,
            ];
        }
    }

    /**
     * Refresh data method to reload component data after dehydration
     */
    public function refreshData()
    {
        Log::info('refreshData method called', ['component_id' => $this->getId()]);
        
        if ($this->scanId) {
            // Reload the scan data
            $this->loadScan($this->scanId);
            Log::info('Data refreshed successfully');
        } else {
            Log::warning('No scanId available for refresh');
        }
    }

    /**
     * Reset component state completely
     */
    public function resetComponent()
    {
        Log::info('resetComponent method called', ['component_id' => $this->getId()]);
        
        // Clear all data structures
        $this->allFileGroups = [];
        $this->loadedFileGroups = [];
        $this->directoryTree = [];
        $this->expandedDirectories = [];
        $this->directoryStats = [];
        $this->selectedIssues = [];
        $this->loadingFiles = [];
        $this->expandedFiles = [];
        $this->expandedIssues = [];
        $this->selectedFileIssues = null;
        $this->selectedFileStats = null;
        $this->selectedFilePath = null;
        
        // Reset pagination
        // Removed currentFileGroupPage - not needed for two-column view
        $this->currentIssuePage = 1;
        $this->totalIssuePages = 1;
        
        // Reload if scanId is available
        if ($this->scanId) {
            $this->loadScan($this->scanId);
        }
        
        Log::info('Component reset completed');
    }

    /**
     * Toggle directory expansion by index (alternative method)
     */
    public function toggleDirectoryByIndex($index)
    {
        try {
            Log::info('toggleDirectoryByIndex called with index: ' . $index);
            
            if (!is_array($this->directoryTree) || empty($this->directoryTree)) {
                Log::warning('Directory tree is empty or not loaded');
                return;
            }
            
            $directories = array_keys($this->directoryTree);
            if (!isset($directories[$index])) {
                Log::error('Invalid directory index: ' . $index . ', available: ' . count($directories));
                return;
            }
            
            $directory = $directories[$index];
            Log::info('Directory resolved to: ' . $directory);
            
            // Call the original method
            $this->toggleDirectory($directory);
            
        } catch (\Exception $e) {
            Log::error('Error in toggleDirectoryByIndex: ' . $e->getMessage(), [
                'index' => $index,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Toggle directory expansion
     */
    public function toggleDirectory($directory)
    {
        Log::info('toggleDirectory called', ['directory' => basename($directory)]);
        
        // Ensure directory is a string
        if (!is_string($directory)) {
            Log::warning('toggleDirectory: directory is not a string', ['directory' => $directory, 'type' => gettype($directory)]);
            return;
        }
        
        // Ensure expandedDirectories is an array
        if (!is_array($this->expandedDirectories)) {
            Log::warning('toggleDirectory: expandedDirectories is not an array, resetting', ['type' => gettype($this->expandedDirectories)]);
            $this->expandedDirectories = [];
        }
        
        $wasExpanded = in_array($directory, $this->expandedDirectories);
        
        if ($wasExpanded) {
            // Remove the directory - use array_filter to remove all instances
            $this->expandedDirectories = array_values(array_filter($this->expandedDirectories, function ($dir) use ($directory) {
                return $dir !== $directory;
            }));
        } else {
            // Add the directory - ensure no duplicates
            if (!in_array($directory, $this->expandedDirectories)) {
                $this->expandedDirectories[] = $directory;
            }
        }
        
        Log::info('toggleDirectory completed', [
            'directory' => basename($directory),
            'action' => $wasExpanded ? 'collapsed' : 'expanded'
        ]);
        
        // Force Livewire to detect the change by calling a method that triggers reactivity
        $this->dispatch('directory-toggled', ['directory' => $directory, 'expanded' => !$wasExpanded]);
    }

    /**
     * Select a file to view its issues
     */
    public function selectFile($filePath)
    {
        try {
            Log::info('selectFile called', [
                'filePath' => $filePath,
                'component_id' => $this->getId()
            ]);
            
            $this->fileLoading = true;
            $this->selectedFilePath = $filePath;
            $this->currentIssuePage = 1; // Reset to first page when selecting new file
            
            // Ensure the directory of the selected file is expanded (but don't collapse others)
            $selectedDirectory = dirname($filePath);
            if ($selectedDirectory === '.') {
                $selectedDirectory = '';
            }
            
            // Only add the directory if it's not already expanded
            $directoryAlreadyExpanded = in_array($selectedDirectory, $this->expandedDirectories);
            if (!$directoryAlreadyExpanded) {
                $this->expandedDirectories[] = $selectedDirectory;
            }
            
            Log::info('selectFile - about to load issues', [
                'filePath' => $filePath,
                'selectedDirectory' => $selectedDirectory,
                'expandedDirectories' => $this->expandedDirectories,
                'directory_was_added' => !$directoryAlreadyExpanded
            ]);
            
            // Load issues for this specific file
            $this->loadSelectedFileIssues();
            
            $this->fileLoading = false;
            
            Log::info('selectFile completed successfully', [
                'filePath' => $filePath,
                'issues_count' => $this->selectedFileIssues ? $this->selectedFileIssues->count() : 0,
                'file_stats' => $this->selectedFileStats
            ]);
            
        } catch (\Exception $e) {
            $this->fileLoading = false;
            Log::error('Error in selectFile', [
                'filePath' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Set some default values to prevent Blade template errors
            $this->selectedFileIssues = collect();
            $this->selectedFileStats = ['total_issues' => 0, 'resolved_issues' => 0];
            
            // Optionally, dispatch an error event to the frontend
            $this->dispatch('file-selection-error', ['message' => 'Failed to load file: ' . $e->getMessage()]);
        }
    }

    /**
     * Navigate to next page of issues
     */
    public function nextIssuePage()
    {
        if ($this->currentIssuePage < $this->totalIssuePages) {
            $this->currentIssuePage++;
            $this->loadSelectedFileIssues();
        }
    }

    /**
     * Navigate to previous page of issues  
     */
    public function previousIssuePage()
    {
        if ($this->currentIssuePage > 1) {
            $this->currentIssuePage--;
            $this->loadSelectedFileIssues();
        }
    }

    /**
     * Go to specific page of issues
     */
    public function goToIssuePage($page)
    {
        if ($page >= 1 && $page <= $this->totalIssuePages) {
            $this->currentIssuePage = $page;
            $this->loadSelectedFileIssues();
        }
    }

    /**
     * Check if a directory is expanded
     */
    public function isDirectoryExpanded($directory)
    {
        return in_array($directory, $this->expandedDirectories);
    }

    /**
     * Initialize two-column view with directory tree and auto-select first file
     */
    protected function initializeTwoColumnView()
    {
        if (!$this->scan) {
            return;
        }

        $this->loadDirectoryTree();
        
        // Auto-select first file if none selected and we have files
        if (!$this->selectedFilePath && !empty($this->directoryTree)) {
            foreach ($this->directoryTree as $files) {
                if (!empty($files)) {
                    $this->selectFile($files[0]['path']);
                    break;
                }
            }
        }
    }

    /**
     * Initialize two-column view with lazy loading approach
     */
    protected function initializeLazyTwoColumnView()
    {
        if (!$this->scan) {
            return;
        }

        // Only load basic directory structure, no file issues
        $this->initialLoadComplete = false;
        $this->currentDirectoryPage = 1;
        $this->directoryFilePage = [];
        
        Log::info('Lazy two-column view initialized');
    }

    /**
     * Load paginated directory structure without file issues
     */
    protected function loadPaginatedDirectoryStructure()
    {
        if (!$this->scan) {
            Log::warning('No scan found in loadPaginatedDirectoryStructure');
            $this->paginatedDirectoryTree = [];
            return;
        }

        try {
            Log::info('Starting paginated directory structure load', [
                'scan_id' => $this->scan->id,
                'current_page' => $this->currentDirectoryPage,
                'per_page' => $this->directoriesPerPage
            ]);

            // Get all directories with files that have issues (with filters applied)
            $issuesQuery = $this->scan->issues()->select('file_path')->distinct();
            
            // Apply filters to directory loading
            if ($this->selectedSeverity !== 'all') {
                $issuesQuery->where('severity', $this->selectedSeverity);
            }
            
            if ($this->selectedCategory !== 'all') {
                $issuesQuery->where('category', $this->selectedCategory);
            }
            
            if ($this->searchTerm) {
                $issuesQuery->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('file_path', 'like', '%' . $this->searchTerm . '%');
                });
            }
            
            $issuesCount = $issuesQuery->count();
            
            Log::info('Issues query result', ['count' => $issuesCount]);
            
            if ($issuesCount === 0) {
                Log::info('No issues found for scan', ['scan_id' => $this->scan->id]);
                $this->paginatedDirectoryTree = [];
                $this->totalDirectoryPages = 1;
                return;
            }

            $directories = $issuesQuery->get()
                ->map(function ($issue) {
                    return dirname($issue->file_path);
                })
                ->unique()
                ->sort()
                ->values();

            Log::info('Directories found', ['count' => $directories->count()]);
            Log::info('Sample directories', ['directories' => $directories->take(5)->toArray()]);
            
            // Log some sample file paths to understand the structure
            $sampleFiles = $this->scan->issues()->take(5)->pluck('file_path');
            Log::info('Sample file paths', ['file_paths' => $sampleFiles->toArray()]);

            // Calculate pagination
            $this->totalDirectoryPages = max(1, ceil($directories->count() / $this->directoriesPerPage));
            $offset = ($this->currentDirectoryPage - 1) * $this->directoriesPerPage;
            
            $paginatedDirs = $directories->slice($offset, $this->directoriesPerPage);
            
            // Build directory tree with file counts (but no actual issues)
            $this->paginatedDirectoryTree = [];
            
            foreach ($paginatedDirs as $directory) {
                // Get files in this directory with issue counts
                $files = $this->scan->issues()
                    ->where('file_path', 'LIKE', $directory . '/%')
                    ->where('file_path', 'NOT LIKE', $directory . '/%/%') // Only direct children
                    ->select('file_path')
                    ->selectRaw('COUNT(*) as issues_count')
                    ->selectRaw('COUNT(CASE WHEN fixed_at IS NOT NULL THEN 1 END) as resolved_count')
                    ->selectRaw('MAX(CASE 
                        WHEN severity = "critical" THEN 1
                        WHEN severity = "high" THEN 2  
                        WHEN severity = "medium" THEN 3
                        WHEN severity = "low" THEN 4
                        WHEN severity = "info" THEN 5
                        ELSE 6 
                    END) as severity_order')
                    ->groupBy('file_path')
                    ->orderBy('severity_order')
                    ->orderBy('file_path')
                    ->get();

                if ($files->isNotEmpty()) {
                    // Paginate files within directory
                    $currentPage = $this->directoryFilePage[$directory] ?? 1;
                    $offset = ($currentPage - 1) * $this->filesPerDirectoryPage;
                    $paginatedFiles = $files->slice($offset, $this->filesPerDirectoryPage);
                    
                    $this->paginatedDirectoryTree[$directory] = $paginatedFiles->map(function ($file) {
                        return [
                            'path' => $file->file_path,
                            'name' => basename($file->file_path),
                            'issues_count' => $file->issues_count,
                            'resolved_count' => $file->resolved_count,
                            'highest_severity' => $this->mapSeverityOrder($file->severity_order),
                        ];
                    })->toArray();
                }
            }

            Log::info('Paginated directory structure loaded successfully', [
                'total_directories' => $directories->count(),
                'current_page' => $this->currentDirectoryPage,
                'total_pages' => $this->totalDirectoryPages,
                'paginated_directories' => count($this->paginatedDirectoryTree),
            ]);

            // Calculate directory stats for the header
            $this->calculateDirectoryStats();

        } catch (\Exception $e) {
            Log::error('Error loading paginated directory structure', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'scan_id' => $this->scan ? $this->scan->id : null
            ]);
            $this->paginatedDirectoryTree = [];
            throw $e; // Re-throw so the fallback mechanism can catch it
        }
    }

    /**
     * Map severity order number back to severity name
     */
    private function mapSeverityOrder($order)
    {
        switch ($order) {
            case 1: return 'critical';
            case 2: return 'high';
            case 3: return 'medium';
            case 4: return 'low';
            case 5: return 'info';
            default: return 'info';
        }
    }

    /**
     * Navigate to next directory page
     */
    public function nextDirectoryPage()
    {
        if ($this->currentDirectoryPage < $this->totalDirectoryPages) {
            $this->currentDirectoryPage++;
            $this->initialLoadComplete = false; // Force reload
        }
    }

    /**
     * Navigate to previous directory page
     */
    public function previousDirectoryPage()
    {
        if ($this->currentDirectoryPage > 1) {
            $this->currentDirectoryPage--;
            $this->initialLoadComplete = false; // Force reload
        }
    }

    /**
     * Go to specific directory page
     */
    public function goToDirectoryPage($page)
    {
        if ($page >= 1 && $page <= $this->totalDirectoryPages) {
            $this->currentDirectoryPage = $page;
            $this->initialLoadComplete = false; // Force reload
        }
    }

    /**
     * Navigate files within a directory
     */
    public function nextDirectoryFilePage($directory)
    {
        $this->directoryFilePage[$directory] = ($this->directoryFilePage[$directory] ?? 1) + 1;
        $this->initialLoadComplete = false; // Force reload
    }

    /**
     * Get a user-friendly directory display name
     */
    public function getDirectoryDisplayName($directory)
    {
        if (empty($directory) || $directory === '.' || $directory === '/') {
            return 'Root';
        }
        
        // Clean up the directory path
        $directory = rtrim($directory, '/\\');
        
        // Split by both forward and backward slashes to handle different OS paths
        $segments = array_filter(preg_split('/[\/\\\\]/', $directory), function($segment) {
            return $segment !== '';
        });
        
        if (empty($segments)) {
            return 'Root';
        }
        
        // For very long paths, show the last 2-3 directory segments
        if (count($segments) > 3) {
            $lastSegments = array_slice($segments, -2);
            $result = '.../' . implode('/', $lastSegments);
            return $result;
        }
        
        // Get the last segment (directory name)
        $result = end($segments);
        
        // If result is empty, return a fallback
        if (empty($result)) {
            return 'Root';
        }
        
        return $result;
    }

    /**
     * Get a display-friendly title for the scan
     */
    public function getScanDisplayTitle()
    {
        if (!$this->scan) {
            return 'Scan Results';
        }

        // If we have specific paths scanned
        if ($this->scan->paths_scanned && is_array($this->scan->paths_scanned) && count($this->scan->paths_scanned) > 0) {
            $paths = $this->scan->paths_scanned;
            
            // If only one path, use its directory name
            if (count($paths) === 1) {
                $path = $paths[0];
                $dirName = basename(rtrim($path, '/'));
                return $dirName ? ucfirst($dirName) . ' Scan Results' : 'Codebase Scan Results';
            }
            
            // Multiple paths - use generic title
            return 'Multi-Directory Scan Results';
        }
        
        // Try to use target field
        if ($this->scan->target) {
            $dirName = basename(rtrim($this->scan->target, '/'));
            return $dirName ? ucfirst($dirName) . ' Scan Results' : 'Codebase Scan Results';
        }
        
        // Fallback
        return 'Codebase Scan Results';
    }

    /**
     * Check if AI is properly configured with API key
     */
    public function isAiConfigured()
    {
        // Check if AI is enabled in settings
        $aiEnabled = Setting::get('ai_enabled', false);
        
        // Check if API key is configured
        $apiKey = Setting::getOpenAiApiKey();
        
        return $aiEnabled && !empty($apiKey);
    }

    /**
     * Generate auto-fix suggestion for an issue using AI
     */
    public function generateAutoFix($issueId)
    {
        try {
            $issue = Issue::find($issueId);
            
            if (!$issue) {
                session()->flash('error', 'Issue not found.');
                return;
            }

            if (!$this->isAiConfigured()) {
                session()->flash('error', 'AI Auto-Fix is not configured. Please set up OpenAI API key in Settings.');
                return;
            }

            // Check if the issue already has an auto-fix
            if (!empty($issue->ai_fix)) {
                session()->flash('info', 'Auto-fix already generated for this issue.');
                return;
            }

            // Get the code context
            $codeSnippet = $this->extractCodeSnippet($issue);
            
            if (empty($codeSnippet)) {
                session()->flash('error', 'Could not extract code context for this issue.');
                return;
            }

            // Prepare the AI prompt
            $prompt = $this->buildAutoFixPrompt($issue, $codeSnippet);
            
            // Call OpenAI API
            $apiKey = Setting::getOpenAiApiKey();
            $response = $this->callOpenAiApi($apiKey, $prompt);
            
            if ($response) {
                // Save the auto-fix suggestion
                $issue->update([
                    'ai_fix' => $response,
                    'ai_explanation' => 'Generated by AI Auto-Fix',
                    'ai_confidence' => 0.75 // Default confidence score
                ]);
                
                // Refresh the file group data for this file
                $this->refreshFileGroup($issue->file_path);
                
                session()->flash('success', 'Auto-fix suggestion generated successfully!');
            } else {
                session()->flash('error', 'Failed to generate auto-fix suggestion. Please try again.');
            }

        } catch (\Exception $e) {
            Log::error('Auto-fix generation failed: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while generating auto-fix. Please try again.');
        }
    }

    /**
     * Build the AI prompt for auto-fix generation
     */
    private function buildAutoFixPrompt($issue, $codeSnippet)
    {
        $codeContext = is_array($codeSnippet) 
            ? implode("\n", array_map(fn($line) => $line['content'], $codeSnippet))
            : $codeSnippet;

        return "You are a code review assistant. Please analyze the following code issue and provide a specific fix suggestion.

**Issue Details:**
- Type: {$issue->type}
- Severity: {$issue->severity}
- Message: {$issue->message}
- File: {$issue->file_path}
- Line: {$issue->line_number}

**Code Context:**
```php
{$codeContext}
```

Please provide:
1. A clear explanation of the issue
2. The specific code change needed to fix it
3. Why this fix addresses the problem

Keep your response concise and focused on actionable fixes.";
    }

    /**
     * Call OpenAI API to generate auto-fix
     */
    private function callOpenAiApi($apiKey, $prompt)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert PHP code reviewer focused on providing clear, actionable fix suggestions.'
                    ],
                    [
                        'role' => 'user', 
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['choices'][0]['message']['content'])) {
                    return $data['choices'][0]['message']['content'];
                }

                Log::error('OpenAI API response missing content', ['response' => $data]);
            } else {
                Log::error('OpenAI API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('OpenAI API call failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract code snippet from issue context
     */
    private function extractCodeSnippet($issue)
    {
        // Try to get code from context
        if (is_array($issue->context) && isset($issue->context['code'])) {
            $code = $issue->context['code'];
            
            // If code is an array of lines, convert it to the format expected by template
            if (is_array($code)) {
                $result = [];
                $startLine = max(1, $issue->line_number - floor(count($code) / 2));
                
                foreach ($code as $index => $line) {
                    $lineNumber = $startLine + $index;
                    $result[] = [
                        'number' => $lineNumber,
                        'content' => $line,
                        'is_target' => $lineNumber === $issue->line_number,
                    ];
                }
                
                return $result;
            } else if (is_string($code)) {
                return $code;
            }
        }
        
        // Fallback: try to read the actual file
        if (file_exists($issue->file_path)) {
            try {
                $fileLines = file($issue->file_path, FILE_IGNORE_NEW_LINES);
                $lineNumber = $issue->line_number;
                $contextLines = 3; // Show 3 lines before and after
                
                $start = max(0, $lineNumber - $contextLines - 1);
                $end = min(count($fileLines), $lineNumber + $contextLines);
                
                $result = [];
                for ($i = $start; $i < $end; $i++) {
                    $result[] = [
                        'number' => $i + 1,
                        'content' => $fileLines[$i] ?? '',
                        'is_target' => ($i + 1) === $lineNumber,
                    ];
                }
                
                return $result;
            } catch (\Exception $e) {
                Log::warning('Could not read file for code snippet', [
                    'file_path' => $issue->file_path,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return "// Code not available - file may have been moved or deleted";
    }

    /**
     * Calculate overall directory statistics for the current paginated view
     */
    private function calculateDirectoryStats()
    {
        if (!$this->scan) {
            $this->directoryStats = ['affected_files' => 0, 'total_issues' => 0, 'resolved_issues' => 0];
            return;
        }

        // Count all unique files that have issues
        $affectedFiles = $this->scan->issues()->distinct('file_path')->count();
        
        // Count total issues
        $totalIssues = $this->scan->issues()->count();
        
        // Count resolved issues - using consistent logic with getIssueStats()
        $resolvedIssues = $this->scan->issues()->where('fixed', true)->count();

        $this->directoryStats = [
            'affected_files' => $affectedFiles,
            'total_issues' => $totalIssues,
            'resolved_issues' => $resolvedIssues,
        ];
    }

    /**
     * Navigate files within a directory (previous)
     */
    public function previousDirectoryFilePage($directory)
    {
        $currentPage = $this->directoryFilePage[$directory] ?? 1;
        if ($currentPage > 1) {
            $this->directoryFilePage[$directory] = $currentPage - 1;
            $this->initialLoadComplete = false; // Force reload
        }
    }
}
