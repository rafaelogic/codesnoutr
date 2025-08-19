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
    public $viewMode = 'grouped'; // 'grouped' or 'detailed'
    public $maxGroupsPerPage = 10; // Limit groups to prevent memory issues

    protected $queryString = [
        'selectedSeverity' => ['except' => 'all'],
        'selectedCategory' => ['except' => 'all'],
        'selectedFile' => ['except' => 'all'],
        'searchTerm' => ['except' => ''],
        'sortBy' => ['except' => 'severity'],
        'sortDirection' => ['except' => 'desc'],
        'viewMode' => ['except' => 'grouped'],
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
            $issues = null;
        } else {
            $issues = $this->getFilteredIssues();
            $groupedIssues = null;
        }
        
        $stats = $this->getIssueStats();
        $files = $this->getUniqueFiles();

        return view('codesnoutr::livewire.scan-results', [
            'issues' => $issues,
            'groupedIssues' => $groupedIssues,
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
    }

    public function setCategoryFilter($category)
    {
        $this->selectedCategory = $category;
        $this->resetPage();
    }

    public function setFileFilter($file)
    {
        $this->selectedFile = $file;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->selectedSeverity = 'all';
        $this->selectedCategory = 'all';
        $this->selectedFile = 'all';
        $this->searchTerm = '';
        $this->resetPage();
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
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
        $issues = $this->getFilteredIssues();
        $this->selectedIssues = $issues->pluck('id')->toArray();
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
            $issue->update([
                'fixed' => true,
                'fixed_at' => now(),
                'fix_method' => 'manual'
            ]);
            $this->dispatch('issue-resolved', issueId: $issueId);
        }
    }

    public function markAsIgnored($issueId)
    {
        $issue = Issue::find($issueId);
        if ($issue) {
            $issue->update([
                'fixed' => true,
                'fix_method' => 'ignored'
            ]);
            $this->dispatch('issue-ignored', issueId: $issueId);
        }
    }

    public function markAsFalsePositive($issueId)
    {
        $issue = Issue::find($issueId);
        if ($issue) {
            $issue->update([
                'fixed' => true,
                'fix_method' => 'false_positive'
            ]);
            $this->dispatch('issue-false-positive', issueId: $issueId);
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
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
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
