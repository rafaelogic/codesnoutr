<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Rafaelogic\CodeSnoutr\Services\Issues\{
    IssueActionInvoker,
    IssueFilterService,
    IssueExportService,
    BulkActionService
};
use Rafaelogic\CodeSnoutr\Services\UI\CodeDisplayService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ScanResultsByIssues extends Component
{
    // Basic properties
    public $scanId;
    public $scan = null;
    
    // Filter properties
    public $selectedSeverity = 'all';
    public $selectedCategory = 'all';
    public $searchTerm = '';
    
    // UI state properties
    public $selectedIssueGroup = null;
    public $expandedGroups = [];
    public $issueGroups = [];
    public $selectedGroupIssues = null;
    public $selectedGroupStats = null;
    public $groupLoading = false;
    public $instancesPerPage = 25;
    public $currentInstancePage = 1;
    
    // Group pagination
    public $groupsPerPage = 10;
    public $currentGroupPage = 1;
    public $loadedGroups = [];

    // Service dependencies
    protected IssueActionInvoker $actionInvoker;
    protected IssueFilterService $filterService;
    protected IssueExportService $exportService;
    protected BulkActionService $bulkActionService;
    protected CodeDisplayService $codeDisplayService;

    public function boot(
        IssueActionInvoker $actionInvoker,
        IssueFilterService $filterService,
        IssueExportService $exportService,
        BulkActionService $bulkActionService,
        CodeDisplayService $codeDisplayService
    ) {
        $this->actionInvoker = $actionInvoker;
        $this->filterService = $filterService;
        $this->exportService = $exportService;
        $this->bulkActionService = $bulkActionService;
        $this->codeDisplayService = $codeDisplayService;
    }

    public function mount($scanId = null)
    {
        if ($scanId) {
            $this->scanId = $scanId;
            $this->loadScan();
            $this->loadInitialData();
        }
    }

    public function render()
    {
        if (!$this->scan) {
            return view('codesnoutr::livewire.scan-results-by-issues', [
                'scan' => null,
                'scanId' => null,
                'stats' => [
                    'total' => 0,
                    'resolved_count' => 0,
                    'pending_count' => 0
                ],
                'issueGroups' => [],
                'selectedGroupIssues' => collect(),
                'selectedGroupStats' => null,
                'severityOptions' => $this->getSeverityOptions(),
                'categoryOptions' => $this->getCategoryOptions(),
                'paginatedIssueGroups' => [],
                'currentGroupPage' => 1,
                'totalGroupPages' => 1,
                'currentInstancePage' => 1,
                'totalInstancePages' => 1,
                'selectedIssueGroup' => null,
                'expandedGroups' => [],
                'groupLoading' => false
            ]);
        }

        $data = $this->prepareViewData();
        
        return view('codesnoutr::livewire.scan-results-by-issues', $data);
    }

    /**
     * Load scan from database
     */
    protected function loadScan(): void
    {
        $this->scan = Scan::find($this->scanId);
        
        if (!$this->scan) {
            Log::warning('Scan not found', ['scanId' => $this->scanId]);
            return;
        }
    }

    /**
     * Load initial data for the component
     */
    protected function loadInitialData(): void
    {
        if (!$this->scan) return;

        $this->loadIssueGroups();
        $this->autoSelectFirstGroup();
    }

    /**
     * Load issue groups based on filters
     */
    protected function loadIssueGroups(): void
    {
        if (!$this->scan) return;

        $filters = $this->getFilters();
        $issueGroups = $this->buildIssueGroups($filters);
        
        $this->issueGroups = $issueGroups;
        $this->loadSelectedGroupIssues();
    }

    /**
     * Build issue groups from scan issues
     */
    protected function buildIssueGroups(array $filters = []): array
    {
        $query = $this->scan->issues();
        
        // Apply filters
        $this->applyFilters($query, $filters);
        
        $issues = $query->get();
        
        // Group issues by rule_id + title + severity combination
        $groups = [];
        
        foreach ($issues as $issue) {
            $groupKey = $this->generateGroupKey($issue);
            
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'key' => $groupKey,
                    'title' => $issue->title,
                    'description' => $issue->description,
                    'category' => $issue->category,
                    'severity' => $issue->severity,
                    'rule_id' => $issue->rule_id,
                    'suggestion' => $issue->suggestion,
                    'total_instances' => 0,
                    'resolved_instances' => 0,
                    'affected_files' => [],
                    'highest_severity' => $issue->severity,
                    'severity_priority' => $this->getSeverityPriority($issue->severity),
                    'instances' => []
                ];
            }
            
            $groups[$groupKey]['total_instances']++;
            
            if ($issue->fixed) {
                $groups[$groupKey]['resolved_instances']++;
            }
            
            // Track affected files
            if (!in_array($issue->file_path, $groups[$groupKey]['affected_files'])) {
                $groups[$groupKey]['affected_files'][] = $issue->file_path;
            }
            
            // Update highest severity
            $currentPriority = $this->getSeverityPriority($groups[$groupKey]['highest_severity']);
            $issuePriority = $this->getSeverityPriority($issue->severity);
            
            if ($issuePriority > $currentPriority) {
                $groups[$groupKey]['highest_severity'] = $issue->severity;
                $groups[$groupKey]['severity_priority'] = $issuePriority;
            }
            
            // Add instance data
            $codeSnippet = $this->codeDisplayService->getCodeSnippet(
                $issue->file_path, 
                $issue->line_number ?? 1, 
                2
            );
            
            $groups[$groupKey]['instances'][] = [
                'id' => $issue->id,
                'file_path' => $issue->file_path,
                'file_name' => basename($issue->file_path),
                'line_number' => $issue->line_number,
                'column_number' => $issue->column_number,
                'fixed' => $issue->fixed,
                'fix_method' => $issue->fix_method,
                'code_snippet' => $codeSnippet['lines'] ?? $issue->code_snippet,
                'ai_fix' => $this->parseAiFixForDisplay($issue->ai_fix),
                'ai_confidence' => $issue->ai_confidence,
                'ai_explanation' => $issue->ai_explanation
            ];
        }
        
        // Sort groups by severity priority (highest first), then by title
        uasort($groups, function ($a, $b) {
            if ($a['severity_priority'] !== $b['severity_priority']) {
                return $b['severity_priority'] <=> $a['severity_priority'];
            }
            return strcmp($a['title'], $b['title']);
        });
        
        return array_values($groups);
    }

    /**
     * Generate a unique key for grouping issues
     */
    protected function generateGroupKey(Issue $issue): string
    {
        return md5($issue->rule_id . '|' . $issue->title . '|' . $issue->category);
    }

    /**
     * Get numeric priority for severity levels
     */
    protected function getSeverityPriority(string $severity): int
    {
        return match($severity) {
            'critical' => 5,
            'high' => 4,
            'medium' => 3,
            'low' => 2,
            default => 1
        };
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
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('file_path', 'like', "%{$searchTerm}%")
                  ->orWhere('rule_id', 'like', "%{$searchTerm}%");
            });
        }
    }

    /**
     * Get current filters array
     */
    protected function getFilters(): array
    {
        return [
            'severity' => $this->selectedSeverity,
            'category' => $this->selectedCategory,
            'search' => $this->searchTerm
        ];
    }

    /**
     * Load issues for selected group
     */
    protected function loadSelectedGroupIssues(): void
    {
        if (!$this->scan || !$this->selectedIssueGroup) {
            $this->selectedGroupIssues = collect();
            $this->selectedGroupStats = null;
            return;
        }

        $this->groupLoading = true;

        // Find the selected group
        $group = collect($this->issueGroups)->firstWhere('key', $this->selectedIssueGroup);
        
        if (!$group) {
            $this->selectedGroupIssues = collect();
            $this->selectedGroupStats = null;
            $this->groupLoading = false;
            return;
        }

        // Paginate instances
        $instances = collect($group['instances']);
        $perPage = $this->instancesPerPage;
        $page = $this->currentInstancePage;
        
        $paginatedInstances = $instances->forPage($page, $perPage);
        
        $this->selectedGroupIssues = $paginatedInstances;
        $this->selectedGroupStats = [
            'total_instances' => $group['total_instances'],
            'resolved_instances' => $group['resolved_instances'],
            'affected_files' => count($group['affected_files']),
            'total_pages' => ceil($instances->count() / $perPage)
        ];
        
        $this->groupLoading = false;
    }

    /**
     * Auto-select the first issue group
     */
    protected function autoSelectFirstGroup(): void
    {
        if (!empty($this->issueGroups)) {
            $firstGroup = $this->issueGroups[0];
            $this->selectedIssueGroup = $firstGroup['key'];
        }
    }

    /**
     * Prepare data for the view
     */
    protected function prepareViewData(): array
    {
        $stats = $this->getScanStats();
        
        return [
            'scan' => $this->scan,
            'scanId' => $this->scanId,
            'stats' => $stats,
            'issueGroups' => $this->issueGroups,
            'selectedGroupIssues' => $this->selectedGroupIssues ?? collect(),
            'selectedGroupStats' => $this->selectedGroupStats,
            'severityOptions' => $this->getSeverityOptions(),
            'categoryOptions' => $this->getCategoryOptions(),
            'selectedIssueGroup' => $this->selectedIssueGroup,
            'expandedGroups' => $this->expandedGroups,
            'groupLoading' => $this->groupLoading,
            'paginatedIssueGroups' => $this->getPaginatedIssueGroups(),
            'currentGroupPage' => $this->currentGroupPage,
            'totalGroupPages' => $this->getTotalGroupPages(),
            'totalGroupCount' => $this->getTotalGroupCount(),
            'loadedGroupCount' => $this->getLoadedGroupCount(),
            'currentInstancePage' => $this->currentInstancePage,
            'totalInstancePages' => $this->getTotalInstancePages()
        ];
    }

    /**
     * Get scan statistics
     */
    protected function getScanStats(): array
    {
        if (!$this->scan) {
            return [
                'total' => 0,
                'resolved_count' => 0,
                'pending_count' => 0
            ];
        }

        $totalIssues = $this->scan->issues()->count();
        $resolvedIssues = $this->scan->issues()->where('fixed', true)->count();
        
        return [
            'total' => $totalIssues,
            'resolved_count' => $resolvedIssues,
            'pending_count' => $totalIssues - $resolvedIssues
        ];
    }

    /**
     * Get paginated issue groups (shows cumulative groups, not just current page)
     */
    protected function getPaginatedIssueGroups(): array
    {
        $groups = $this->issueGroups;
        $perPage = $this->groupsPerPage;
        $page = $this->currentGroupPage;
        
        // Return all groups up to the current page (cumulative)
        return array_slice($groups, 0, $page * $perPage);
    }

    /**
     * Get total group pages
     */
    protected function getTotalGroupPages(): int
    {
        return max(1, ceil(count($this->issueGroups) / $this->groupsPerPage));
    }

    /**
     * Get total group count
     */
    protected function getTotalGroupCount(): int
    {
        return count($this->issueGroups);
    }

    /**
     * Get loaded group count
     */
    protected function getLoadedGroupCount(): int
    {
        return min(
            $this->currentGroupPage * $this->groupsPerPage,
            count($this->issueGroups)
        );
    }

    /**
     * Get total instance pages
     */
    protected function getTotalInstancePages(): int
    {
        return $this->selectedGroupStats['total_pages'] ?? 1;
    }

    /**
     * Get severity options
     */
    protected function getSeverityOptions(): array
    {
        return [
            'all' => 'All Severities',
            'critical' => 'Critical',
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
            'info' => 'Info'
        ];
    }

    /**
     * Get category options
     */
    protected function getCategoryOptions(): array
    {
        return [
            'all' => 'All Categories',
            'security' => 'Security',
            'performance' => 'Performance',
            'quality' => 'Code Quality',
            'style' => 'Code Style',
            'complexity' => 'Complexity',
            'maintainability' => 'Maintainability',
            'bugs' => 'Bugs',
            'documentation' => 'Documentation'
        ];
    }

    // ===== User Actions =====

    /**
     * Select an issue group
     */
    public function selectGroup($groupKey)
    {
        $this->selectedIssueGroup = $groupKey;
        $this->currentInstancePage = 1; // Reset pagination
        $this->loadSelectedGroupIssues();
    }

    /**
     * Toggle group expansion
     */
    public function toggleGroup($groupKey)
    {
        if (in_array($groupKey, $this->expandedGroups)) {
            $this->expandedGroups = array_diff($this->expandedGroups, [$groupKey]);
        } else {
            $this->expandedGroups[] = $groupKey;
        }
    }

    /**
     * Check if group is expanded
     */
    public function isGroupExpanded($groupKey): bool
    {
        return in_array($groupKey, $this->expandedGroups);
    }

    /**
     * Clear all filters
     */
    public function clearFilters()
    {
        $this->selectedSeverity = 'all';
        $this->selectedCategory = 'all';
        $this->searchTerm = '';
        $this->loadIssueGroups();
    }

    /**
     * Next group page
     */
    public function nextGroupPage()
    {
        if ($this->currentGroupPage < $this->getTotalGroupPages()) {
            $this->currentGroupPage++;
        }
    }

    /**
     * Load more groups (append to existing list)
     */
    public function loadMoreGroups()
    {
        if ($this->currentGroupPage < $this->getTotalGroupPages()) {
            $this->currentGroupPage++;
            // Don't reload all groups, just notify view to show more
            $this->dispatch('groups-loaded-more');
        }
    }

    /**
     * Previous group page
     */
    public function previousGroupPage()
    {
        if ($this->currentGroupPage > 1) {
            $this->currentGroupPage--;
        }
    }

    /**
     * Next instance page
     */
    public function nextInstancePage()
    {
        if ($this->currentInstancePage < $this->getTotalInstancePages()) {
            $this->currentInstancePage++;
            $this->loadSelectedGroupIssues();
        }
    }

    /**
     * Previous instance page
     */
    public function previousInstancePage()
    {
        if ($this->currentInstancePage > 1) {
            $this->currentInstancePage--;
            $this->loadSelectedGroupIssues();
        }
    }

    // ===== Filter Update Handlers =====

    public function updatedSelectedSeverity()
    {
        $this->currentGroupPage = 1;
        $this->loadIssueGroups();
    }

    public function updatedSelectedCategory()
    {
        $this->currentGroupPage = 1;
        $this->loadIssueGroups();
    }

    public function updatedSearchTerm()
    {
        $this->currentGroupPage = 1;
        $this->loadIssueGroups();
    }

    // ===== Issue Actions =====

    public function resolveIssue($issueId)
    {
        $issue = Issue::find($issueId);
        if (!$issue) return;

        $result = $this->actionInvoker->executeAction('resolve', $issue);
        
        if ($result['success']) {
            $this->handleActionSuccess($result, 'issue-resolved', $issueId);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    /**
     * Generate AI fix for a specific issue
     */
    public function generateAiFix($issueId)
    {
        $issue = Issue::find($issueId);
        if (!$issue) return;

        try {
            $result = $this->actionInvoker->executeAction('generate_ai_fix', $issue);
            
            if ($result['success']) {
                $this->handleActionSuccess([
                    'success' => true,
                    'message' => 'AI fix suggestion generated successfully! You can now review and apply it.'
                ], 'ai-fix-generated', $issueId);
            } else {
                session()->flash('error', $result['message']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate AI fix: ' . $e->getMessage());
        }
    }

    public function applyAiFix($issueId)
    {
        $issue = Issue::find($issueId);
        if (!$issue) return;

        // Check if AI fix exists
        if (empty($issue->ai_fix)) {
            session()->flash('error', 'No AI fix available for this issue. Please generate one first.');
            return;
        }

        try {
            $result = $this->actionInvoker->executeAction('apply_ai_fix', $issue);
            
            if ($result['success']) {
                $this->handleActionSuccess([
                    'success' => true,
                    'message' => 'AI fix has been applied successfully! A backup was created.'
                ], 'issue-ai-fixed', $issueId);
            } else {
                session()->flash('error', $result['message']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to apply AI fix: ' . $e->getMessage());
        }
    }

    public function markAsIgnored($issueId)
    {
        $issue = Issue::find($issueId);
        if (!$issue) return;

        $result = $this->actionInvoker->executeAction('ignore', $issue);
        
        if ($result['success']) {
            $this->handleActionSuccess($result, 'issue-ignored', $issueId);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function markAsFalsePositive($issueId)
    {
        $issue = Issue::find($issueId);
        if (!$issue) return;

        $result = $this->actionInvoker->executeAction('false_positive', $issue);
        
        if ($result['success']) {
            $this->handleActionSuccess($result, 'issue-false-positive', $issueId);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    /**
     * Handle successful action
     */
    protected function handleActionSuccess(array $result, string $event, $issueId): void
    {
        session()->flash('success', $result['message']);
        $this->dispatch($event, ['issueId' => $issueId]);
        
        // Reload data to reflect changes
        $this->loadIssueGroups();
    }

    // ===== AI Integration =====

    /**
     * Check if AI is configured
     */
    public function isAiConfigured(): bool
    {
        $apiKey = Setting::getOpenAiApiKey() ?? env('OPENAI_API_KEY');
        $aiEnabled = Setting::get('ai_enabled', false);
        
        return $aiEnabled && !empty($apiKey);
    }

    /**
     * Check if AI features are available
     */
    public function isAiAvailable(): bool
    {
        return $this->isAiConfigured();
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
     * Get scan display title
     */
    public function getScanDisplayTitle(): string
    {
        if (!$this->scan) return 'Unknown Scan';
        
        return 'Scan #' . $this->scan->id;
    }
}