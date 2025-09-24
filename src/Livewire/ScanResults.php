<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
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
use Rafaelogic\CodeSnoutr\Services\Scanning\ScanResultsViewService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ScanResults extends Component
{
    use WithPagination;

    // Basic properties
    public $scanId;
    public $scan = null;
    
    // Filter properties
    public $selectedSeverity = 'all';
    public $selectedCategory = 'all';
    public $selectedFile = 'all';
    public $searchTerm = '';
    public $sortBy = 'severity';
    public $sortDirection = 'desc';
    
    // UI state properties
    public $selectedIssues = [];
    public $showBulkActions = false;
    public $expandedIssues = [];
    
    // Two-column view properties
    public $selectedFilePath = null;
    public $directoryTree = [];
    public $directoryStats = [];
    public $expandedDirectories = [];
    public $selectedFileIssues = null;
    public $selectedFileStats = null;
    public $fileLoading = false;
    public $issuesPerPage = 25;
    public $currentIssuePage = 1;
    
    // Directory pagination
    public $directoriesPerPage = 10;
    public $currentDirectoryPage = 1;
    public $loadedDirectories = []; // Track which directories have been loaded for appending

    // Service dependencies
    protected IssueActionInvoker $actionInvoker;
    protected IssueFilterService $filterService;
    protected IssueExportService $exportService;
    protected BulkActionService $bulkActionService;
    protected CodeDisplayService $codeDisplayService;
    protected ScanResultsViewService $viewService;

    public function boot(
        IssueActionInvoker $actionInvoker,
        IssueFilterService $filterService,
        IssueExportService $exportService,
        BulkActionService $bulkActionService,
        CodeDisplayService $codeDisplayService,
        ScanResultsViewService $viewService
    ) {
        $this->actionInvoker = $actionInvoker;
        $this->filterService = $filterService;
        $this->exportService = $exportService;
        $this->bulkActionService = $bulkActionService;
        $this->codeDisplayService = $codeDisplayService;
        $this->viewService = $viewService;
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
            return view('codesnoutr::livewire.scan-results-two-column', [
                'scan' => null,
                'scanId' => null,
                'stats' => [
                    'total' => 0,
                    'resolved_count' => 0,
                    'pending_count' => 0
                ],
                'directoryTree' => [],
                'selectedFileIssues' => collect(),
                'selectedFileStats' => null,
                'directoryStats' => [],
                'filterOptions' => [],
                'severityOptions' => $this->getSeverityOptions(),
                'categoryOptions' => $this->getCategoryOptions(),
                'paginatedDirectoryTree' => [],
                'currentDirectoryPage' => 1,
                'totalDirectoryPages' => 1,
                'currentIssuePage' => 1,
                'totalIssuePages' => 1,
                'selectedFilePath' => null,
                'expandedDirectories' => [],
                'expandedIssues' => [],
                'selectedIssues' => [],
                'showBulkActions' => false,
                'fileLoading' => false
            ]);
        }

        $data = $this->prepareViewData();
        
        return view('codesnoutr::livewire.scan-results-two-column', $data);
    }

    /**
     * Load scan from database
     */
    protected function loadScan(): void
    {
        $this->scan = Scan::with('issues')->find($this->scanId);
        if (!$this->scan) {
            session()->flash('error', 'Scan not found');
        }
    }

    /**
     * Load initial data for the component
     */
    protected function loadInitialData(): void
    {
        if (!$this->scan) return;

        $filters = $this->getFilters();
        $treeData = $this->viewService->loadDirectoryTree($this->scan, $filters);
        
        $this->directoryTree = $treeData['tree'];
        $this->directoryStats = $treeData['stats'];

        // Initialize loaded directories with the first page
        $this->loadedDirectories = [];
        $this->currentDirectoryPage = 1;
        $this->loadNextDirectoryPage();

        // Auto-select first file if none selected
        if (!$this->selectedFilePath && !empty($this->loadedDirectories)) {
            $this->autoSelectFirstFile();
        }

        $this->loadSelectedFileIssues();
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
            'directoryTree' => $this->directoryTree,
            'selectedFileIssues' => $this->selectedFileIssues ?? collect(),
            'selectedFileStats' => $this->selectedFileStats,
            'directoryStats' => $this->directoryStats,
            'filterOptions' => $this->getFilterOptions(),
            'severityOptions' => $this->getSeverityOptions(),
            'categoryOptions' => $this->getCategoryOptions(),
            'selectedFilePath' => $this->selectedFilePath,
            'expandedDirectories' => $this->expandedDirectories,
            'expandedIssues' => $this->expandedIssues,
            'selectedIssues' => $this->selectedIssues,
            'showBulkActions' => $this->showBulkActions,
            'fileLoading' => $this->fileLoading,
            'paginatedDirectoryTree' => $this->getPaginatedDirectoryTree(),
            'currentDirectoryPage' => $this->currentDirectoryPage,
            'totalDirectoryPages' => $this->getTotalDirectoryPages(),
            'totalDirectoryCount' => $this->getTotalDirectoryCount(),
            'loadedDirectoryCount' => $this->getLoadedDirectoryCount(),
            'currentIssuePage' => $this->currentIssuePage,
            'totalIssuePages' => $this->getTotalIssuePages()
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
     * Get current filters
     */
    protected function getFilters(): array
    {
        return [
            'severity' => $this->selectedSeverity,
            'category' => $this->selectedCategory,
            'file' => $this->selectedFile,
            'search' => $this->searchTerm
        ];
    }

    /**
     * Get filter options
     */
    protected function getFilterOptions(): array
    {
        if (!$this->scan) return [];
        return $this->filterService->getFilterOptions($this->scan);
    }

    /**
     * Get severity options for the dropdown
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
     * Get category options for the dropdown
     */
    protected function getCategoryOptions(): array
    {
        return [
            'all' => 'All Categories',
            'security' => 'Security',
            'performance' => 'Performance',
            'quality' => 'Quality',
            'laravel' => 'Laravel',
            'blade' => 'Blade',
            'php' => 'PHP'
        ];
    }

    /**
     * Load issues for selected file
     */
    protected function loadSelectedFileIssues(): void
    {
        if (!$this->scan || !$this->selectedFilePath) {
            $this->selectedFileIssues = collect();
            $this->selectedFileStats = null;
            return;
        }

        $this->fileLoading = true;

        $filters = $this->getFilters();
        $data = $this->viewService->loadFileIssues(
            $this->scan, 
            $this->selectedFilePath, 
            $filters, 
            $this->currentIssuePage, 
            $this->issuesPerPage
        );

        $this->selectedFileIssues = $data['issues'];
        $this->selectedFileStats = $data['stats'];
        $this->fileLoading = false;
    }

    /**
     * Auto-select the first file with issues
     */
    protected function autoSelectFirstFile(): void
    {
        $firstFile = $this->findFirstFileInLoadedDirectories();
        if ($firstFile) {
            $this->selectedFilePath = $firstFile['path'];
        }
    }

    /**
     * Find the first file in loaded directories
     */
    protected function findFirstFileInLoadedDirectories(): ?array
    {
        foreach ($this->loadedDirectories as $directoryPath => $files) {
            if (!empty($files) && is_array($files)) {
                $firstFile = reset($files);
                if ($firstFile && isset($firstFile['path'])) {
                    return $firstFile;
                }
            }
        }
        return null;
    }

    /**
     * Recursively find first file in directory tree
     */
    protected function findFirstFileInTree(array $tree): ?array
    {
        // Check files in current level
        if (isset($tree['files']) && !empty($tree['files'])) {
            return reset($tree['files']);
        }

        // Check subdirectories
        if (isset($tree['directories'])) {
            foreach ($tree['directories'] as $directory) {
                $file = $this->findFirstFileInTree($directory);
                if ($file) return $file;
            }
        }

        return null;
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

    public function generateAutoFix($issueId)
    {
        $issue = Issue::find($issueId);
        if (!$issue) return;

        $result = $this->actionInvoker->executeAction('generate_ai_fix', $issue);
        
        if ($result['success']) {
            $this->refreshSelectedFileIssues();
            session()->flash('success', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function applyAutoFix($issueId)
    {
        $issue = Issue::find($issueId);
        if (!$issue) return;

        $result = $this->actionInvoker->executeAction('apply_ai_fix', $issue);
        
        if ($result['success']) {
            $this->handleActionSuccess($result, 'issue-ai-fixed', $issueId);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    /**
     * Handle successful action execution
     */
    protected function handleActionSuccess(array $result, string $eventName, int $issueId): void
    {
        session()->flash('success', $result['message']);
        $this->refreshSelectedFileIssues();
        $this->dispatch($eventName, issueId: $issueId);
    }

    /**
     * Refresh selected file issues after action
     */
    protected function refreshSelectedFileIssues(): void
    {
        $this->loadSelectedFileIssues();
        $this->loadInitialData(); // Refresh directory tree stats
    }

    // ===== Bulk Actions =====

    public function bulkResolve()
    {
        if (empty($this->selectedIssues)) return;

        $result = $this->bulkActionService->executeBulkAction('resolve', $this->selectedIssues);
        $this->handleBulkActionResult($result);
    }

    public function bulkIgnore()
    {
        if (empty($this->selectedIssues)) return;

        $result = $this->bulkActionService->executeBulkAction('ignore', $this->selectedIssues);
        $this->handleBulkActionResult($result);
    }

    public function bulkMarkFalsePositive()
    {
        if (empty($this->selectedIssues)) return;

        $result = $this->bulkActionService->executeBulkAction('false_positive', $this->selectedIssues);
        $this->handleBulkActionResult($result);
    }

    /**
     * Handle bulk action result
     */
    protected function handleBulkActionResult(array $result): void
    {
        if ($result['success']) {
            session()->flash('success', $result['message']);
            $this->selectedIssues = [];
            $this->showBulkActions = false;
            $this->refreshSelectedFileIssues();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    // ===== Export Actions =====

    public function exportResults($format = 'json')
    {
        if (!$this->scan) return;

        $issues = $this->scan->issues;
        return $this->exportService->export($issues, $format, $this->scan);
    }

    // ===== Filter and UI Actions =====

    public function selectFile($filePath)
    {
        $this->selectedFilePath = $filePath;
        $this->currentIssuePage = 1;
        $this->loadSelectedFileIssues();
    }

    public function toggleDirectory($directoryPath)
    {
        if (in_array($directoryPath, $this->expandedDirectories)) {
            $this->expandedDirectories = array_diff($this->expandedDirectories, [$directoryPath]);
        } else {
            $this->expandedDirectories[] = $directoryPath;
        }
    }

    public function toggleIssueExpansion($issueId)
    {
        if (in_array($issueId, $this->expandedIssues)) {
            $this->expandedIssues = array_diff($this->expandedIssues, [$issueId]);
        } else {
            $this->expandedIssues[] = $issueId;
        }
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
        if (!$this->selectedFileIssues) return;

        $allIssueIds = $this->selectedFileIssues->flatten()->pluck('issues')->flatten()->pluck('id')->toArray();
        $this->selectedIssues = array_unique(array_merge($this->selectedIssues, $allIssueIds));
        $this->showBulkActions = true;
    }

    public function deselectAllIssues()
    {
        $this->selectedIssues = [];
        $this->showBulkActions = false;
    }

    // ===== Filter Updates =====

    public function updatedSelectedSeverity()
    {
        $this->currentIssuePage = 1;
        $this->loadInitialData();
    }

    public function updatedSelectedCategory()
    {
        $this->currentIssuePage = 1;
        $this->loadInitialData();
    }

    public function updatedSearchTerm()
    {
        $this->currentIssuePage = 1;
        $this->loadInitialData();
    }

    public function clearFilters()
    {
        $this->selectedSeverity = 'all';
        $this->selectedCategory = 'all';
        $this->selectedFile = 'all';
        $this->searchTerm = '';
        $this->currentIssuePage = 1;
        $this->loadInitialData();
    }

    // ===== Utility Methods =====

    public function getCodeSnippet($filePath, $lineNumber, $contextLines = 2)
    {
        return $this->codeDisplayService->getCodeSnippet($filePath, $lineNumber, $contextLines);
    }

    public function getSeverityColor($severity)
    {
        return $this->codeDisplayService->getSeverityInfo($severity)['color'];
    }

    public function refreshResults()
    {
        $this->loadInitialData();
        session()->flash('success', 'Results refreshed successfully');
    }

    // ===== Pagination Methods =====

    public function nextIssuePage()
    {
        $this->currentIssuePage++;
        $this->loadSelectedFileIssues();
    }

    public function previousIssuePage()
    {
        if ($this->currentIssuePage > 1) {
            $this->currentIssuePage--;
            $this->loadSelectedFileIssues();
        }
    }

    public function goToIssuePage($page)
    {
        $this->currentIssuePage = max(1, intval($page));
        $this->loadSelectedFileIssues();
    }

    public function nextDirectoryPage()
    {
        $totalPages = $this->getTotalDirectoryPages();
        if ($this->currentDirectoryPage < $totalPages) {
            $this->currentDirectoryPage++;
            $this->loadNextDirectoryPage();
        }
    }

    public function previousDirectoryPage()
    {
        if ($this->currentDirectoryPage > 1) {
            $this->currentDirectoryPage--;
        }
    }

    /**
     * Load directories for the next page and append them to loaded directories
     */
    protected function loadNextDirectoryPage()
    {
        if (empty($this->directoryTree)) {
            return;
        }

        // Flatten the nested structure and filter out empty directories
        $flatTree = $this->flattenDirectoryTree($this->directoryTree);
        $nonEmptyDirectories = [];
        foreach ($flatTree as $dirName => $dirData) {
            if (is_array($dirData) && count($dirData) > 0) {
                $nonEmptyDirectories[$dirName] = $dirData;
            }
        }
        
        $offset = ($this->currentDirectoryPage - 1) * $this->directoriesPerPage;
        $newDirectories = array_slice($nonEmptyDirectories, $offset, $this->directoriesPerPage, true);
        
        // Append new directories to loaded directories
        foreach ($newDirectories as $dirName => $dirData) {
            if (!isset($this->loadedDirectories[$dirName])) {
                $this->loadedDirectories[$dirName] = $dirData;
            }
        }
    }

    public function getPaginatedDirectoryTree(): array
    {
        return $this->loadedDirectories;
    }

    /**
     * Flatten the nested directory structure to match view expectations
     */
    protected function flattenDirectoryTree(array $tree): array
    {
        $flattened = [];
        
        if (isset($tree['directories'])) {
            foreach ($tree['directories'] as $dirName => $dirData) {
                // Skip directories with empty names
                if (empty($dirName) || trim($dirName) === '' || empty($dirData['path'])) {
                    continue;
                }
                
                $files = [];
                if (isset($dirData['files'])) {
                    foreach ($dirData['files'] as $fileName => $fileData) {
                        $files[$fileName] = $fileData;
                    }
                }
                
                // Only add directory if it has files or a valid path
                if (!empty($files) || !empty($dirData['path'])) {
                    $flattened[$dirData['path']] = $files;
                }
                
                // Recursively flatten subdirectories
                $subTree = $this->flattenDirectoryTree($dirData);
                $flattened = array_merge($flattened, $subTree);
            }
        }
        
        return $flattened;
    }

    public function getTotalDirectoryPages(): int
    {
        $totalCount = $this->getTotalDirectoryCount(); // This already counts only non-empty directories
        return ceil($totalCount / $this->directoriesPerPage);
    }

    public function getTotalDirectoryCount(): int
    {
        if (empty($this->directoryTree)) {
            return 0;
        }
        
        $flatTree = $this->flattenDirectoryTree($this->directoryTree);
        // Count only directories that have files
        $count = 0;
        foreach ($flatTree as $directory => $files) {
            if (is_array($files) && count($files) > 0) {
                $count++;
            }
        }
        return $count;
    }

    public function getLoadedDirectoryCount(): int
    {
        // Count only non-empty loaded directories
        $count = 0;
        foreach ($this->loadedDirectories as $directory => $files) {
            if (is_array($files) && count($files) > 0) {
                $count++;
            }
        }
        return $count;
    }

    public function getTotalIssuePages(): int
    {
        if (!$this->selectedFileIssues || $this->selectedFileIssues->isEmpty()) {
            return 1;
        }
        
        return ceil($this->selectedFileIssues->count() / $this->issuesPerPage);
    }

    // ===== Helper Methods for Blade =====

    public function getSeverityIcon($severity)
    {
        return $this->codeDisplayService->getSeverityInfo($severity)['icon'];
    }

    public function getCategoryIcon($category)
    {
        return $this->codeDisplayService->getCategoryInfo($category)['icon'];
    }

    public function getName()
    {
        return 'scan-results';
    }

    public function getScanDisplayTitle()
    {
        if (!$this->scan) return 'Scan Results';
        
        return $this->scan->title ?: "Scan #{$this->scan->id}";
    }

    public function getDirectoryDisplayName($directory)
    {
        if (empty($directory) || trim($directory) === '') {
            return '[Empty Directory]'; // Fallback for debugging
        }
        
        $basename = basename($directory);
        return !empty($basename) ? $basename : $directory;
    }

    public function getDirectoryRelativePath($directory)
    {
        if (empty($directory) || trim($directory) === '') {
            return '';
        }

        $basePath = base_path();
        
        // If the directory path starts with the base path, make it relative
        if (str_starts_with($directory, $basePath)) {
            $relativePath = ltrim(substr($directory, strlen($basePath)), '/');
            return empty($relativePath) ? '/' : $relativePath;
        }
        
        // If it doesn't start with base path, just return the directory path
        return $directory;
    }

    public function isDirectoryExpanded($directory)
    {
        return in_array($directory, $this->expandedDirectories);
    }

    // ===== Compatibility Methods =====

    public function refreshData()
    {
        $this->loadInitialData();
    }

    public function resetComponent()
    {
        $this->selectedIssues = [];
        $this->expandedIssues = [];
        $this->expandedDirectories = [];
        $this->selectedFilePath = null;
        $this->currentIssuePage = 1;
        $this->selectedSeverity = 'all';
        $this->selectedCategory = 'all';
        $this->searchTerm = '';
        
        $this->loadInitialData();
    }

    // ===== AI Configuration =====

    /**
     * Check if AI is properly configured with API key
     */
    public function isAiConfigured()
    {
        // Check if AI is enabled in settings
        $aiEnabled = Setting::getValue('ai_enabled', false);
        
        // Check if API key is configured
        $apiKey = Setting::getOpenAiApiKey();
        
        return $aiEnabled && !empty($apiKey);
    }
}