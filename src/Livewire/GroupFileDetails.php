<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;

class GroupFileDetails extends Component
{
    use WithPagination;

    public $scanId;
    public $scan;
    public $groupTitle;
    public $groupCategory;
    public $groupSeverity;
    public $groupDescription;
    public $groupRule;
    public $groupSuggestion;
    public $searchTerm = '';
    public $sortBy = 'file_path';
    public $sortDirection = 'asc';
    public $selectedIssues = [];

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'sortBy' => ['except' => 'file_path'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    public function mount($scanId, $title, $category, $severity, $description = null, $rule = null, $suggestion = null)
    {
        $this->scanId = $scanId;
        $this->groupTitle = $title;
        $this->groupCategory = $category;
        $this->groupSeverity = $severity;
        $this->groupDescription = $description;
        $this->groupRule = $rule;
        $this->groupSuggestion = $suggestion;
        
        $this->loadScan();
    }

    public function render()
    {
        $issues = $this->getFilteredIssues();
        $stats = $this->getGroupStats();

        return view('codesnoutr::livewire.group-file-details', [
            'issues' => $issues,
            'stats' => $stats,
        ]);
    }

    protected function loadScan()
    {
        if ($this->scanId) {
            $this->scan = Scan::find($this->scanId);
        }
    }

    protected function getFilteredIssues()
    {
        if (!$this->scan) {
            return collect();
        }

        $query = $this->scan->issues()
            ->where('title', $this->groupTitle)
            ->where('category', $this->groupCategory)
            ->where('severity', $this->groupSeverity);

        // Apply search filter
        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('file_path', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(25);
    }

    protected function getGroupStats()
    {
        if (!$this->scan) {
            return [
                'total_files' => 0,
                'total_occurrences' => 0,
                'resolved_count' => 0,
                'ignored_count' => 0,
                'pending_count' => 0,
            ];
        }

        $baseQuery = $this->scan->issues()
            ->where('title', $this->groupTitle)
            ->where('category', $this->groupCategory)
            ->where('severity', $this->groupSeverity);

        $stats = $baseQuery->selectRaw('
            COUNT(*) as total_occurrences,
            COUNT(DISTINCT file_path) as total_files,
            SUM(CASE WHEN fix_method = "manual" THEN 1 ELSE 0 END) as resolved_count,
            SUM(CASE WHEN fix_method = "ignored" THEN 1 ELSE 0 END) as ignored_count,
            SUM(CASE WHEN fixed = 0 OR fixed IS NULL THEN 1 ELSE 0 END) as pending_count
        ')->first();

        return [
            'total_files' => $stats->total_files ?? 0,
            'total_occurrences' => $stats->total_occurrences ?? 0,
            'resolved_count' => $stats->resolved_count ?? 0,
            'ignored_count' => $stats->ignored_count ?? 0,
            'pending_count' => $stats->pending_count ?? 0,
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->resetPage();
    }

    public function toggleIssueSelection($issueId)
    {
        if (in_array($issueId, $this->selectedIssues)) {
            $this->selectedIssues = array_diff($this->selectedIssues, [$issueId]);
        } else {
            $this->selectedIssues[] = $issueId;
        }
    }

    public function selectAllIssues()
    {
        $issues = $this->getFilteredIssues();
        $this->selectedIssues = $issues->pluck('id')->toArray();
    }

    public function deselectAllIssues()
    {
        $this->selectedIssues = [];
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

    public function bulkResolve()
    {
        Issue::whereIn('id', $this->selectedIssues)
            ->update([
                'fixed' => true,
                'fixed_at' => now(),
                'fix_method' => 'manual'
            ]);

        $this->selectedIssues = [];
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
        $this->dispatch('bulk-action-completed', action: 'ignored');
    }

    public function goBackToResults()
    {
        return redirect()->route('codesnoutr.scan-results', ['scanId' => $this->scanId]);
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
}
