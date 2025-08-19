<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Rafaelogic\CodeSnoutr\Jobs\ScanCodebaseJob;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Services\AiAssistantService;

class ScanWizard extends Component
{
    public $currentStep = 1;
    public $totalSteps = 5;
    
    // File browser
    public $showFileBrowser = false;
    public $browserCurrentPath = '';
    protected $browserItems = [];
    
    // Scan configuration
    public $scanType = 'codebase';
    public $scanTarget = 'full_codebase';
    public $target = '';
    public $scanPath = '';
    public $ruleCategories = [];
    
    // Job tracking
    public $jobId = null;
    public $scanId = null;
    public $scanProgress = 0;
    public $scanStatus = 'idle';
    public $currentActivity = '';
    public $currentFile = null;
    public $filesScanned = 0;
    public $issuesFound = 0;
    public $rulesApplied = 0;
    public $timeElapsed = '0:00';
    protected $activityLog = [];
    protected $previewIssues = [];

    // AI Assistant
    public $aiSuggestions = [];
    public $showAiSuggestions = false;
    public $aiAvailable = false;
    protected $aiService;

    protected $rules = [
        'scanType' => 'required|in:file,directory,codebase',
        'target' => 'required_unless:scanType,codebase',
        'ruleCategories' => 'required|array|min:1',
        'ruleCategories.*' => 'in:security,performance,quality,laravel'
    ];

    protected $listeners = [
        'apply-scan-suggestion' => 'applyScanSuggestion',
        'get-ai-suggestions' => 'getAiSuggestions',
    ];

    public function getAllCategories()
    {
        return [
            'security' => [
                'title' => 'Security Issues',
                'description' => 'Detect vulnerabilities like SQL injection, XSS, and insecure code patterns',
                'icon' => 'shield-check',
                'color' => 'red'
            ],
            'performance' => [
                'title' => 'Performance Issues',
                'description' => 'Find N+1 queries, memory leaks, and inefficient algorithms',
                'icon' => 'lightning-bolt',
                'color' => 'yellow'
            ],
            'quality' => [
                'title' => 'Code Quality',
                'description' => 'Check coding standards, complexity, and maintainability issues',
                'icon' => 'star',
                'color' => 'blue'
            ],
            'laravel' => [
                'title' => 'Laravel Specific',
                'description' => 'Laravel best practices, Eloquent optimization, and framework patterns',
                'icon' => 'code',
                'color' => 'green'
            ]
        ];
    }

    public function getAllCategoriesProperty()
    {
        return $this->getAllCategories();
    }

    public function getBrowserItemsProperty()
    {
        return $this->browserItems;
    }

    public function getActivityLogProperty()
    {
        return $this->activityLog;
    }

    public function getPreviewIssuesProperty()
    {
        return $this->previewIssues;
    }

    public function mount()
    {
        $this->ruleCategories = ['security', 'quality'];
        $this->updateRulesApplied();
        $this->browserCurrentPath = base_path();
        $this->browserItems = [];
        $this->activityLog = [];
        $this->previewIssues = [];
        
        // Initialize AI service
        try {
            $this->aiService = new AiAssistantService();
            $this->aiAvailable = $this->aiService ? $this->aiService->isAvailable() : false;
        } catch (\Exception $e) {
            $this->aiService = null;
            $this->aiAvailable = false;
            Log::warning('Failed to initialize AI service: ' . $e->getMessage());
        }
        
        // Load AI suggestions if available
        if ($this->aiAvailable) {
            $this->loadAiSuggestions();
        }
        
        // Don't load browser items immediately to avoid complex data during initialization
    }

    public function goToStep($step)
    {
        if ($step >= 1 && $step <= $this->totalSteps) {
            $this->currentStep = $step;
        }
    }

    public function nextStep()
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->validateCurrentStep();
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function validateCurrentStep()
    {
        switch ($this->currentStep) {
            case 1:
                $this->validate(['scanType' => $this->rules['scanType']]);
                break;
            case 2:
                $this->validate(['target' => $this->rules['target']]);
                break;
            case 3:
                $this->validate(['ruleCategories' => $this->rules['ruleCategories']]);
                break;
        }
    }

    public function selectScanType($type)
    {
        $this->scanType = $type;
        
        switch ($type) {
            case 'file':
                $this->ruleCategories = ['security', 'quality'];
                break;
            case 'directory':
                $this->ruleCategories = ['security', 'quality', 'performance'];
                break;
            case 'codebase':
                $this->ruleCategories = array_keys($this->getAllCategories());
                break;
        }
        
        $this->updateRulesApplied();
    }

    public function selectScanTarget($target)
    {
        // This method is deprecated but kept for backward compatibility
        $this->scanTarget = $target;
        
        if ($target === 'full_codebase') {
            $this->scanPath = '';
            $this->target = '';
        }
    }

    public function openFileBrowser()
    {
        $this->showFileBrowser = true;
        $this->browserCurrentPath = base_path();
        $this->loadBrowserItems();
    }

    public function closeFileBrowser()
    {
        $this->showFileBrowser = false;
        $this->browserItems = [];
        $this->browserCurrentPath = '';
    }

    public function navigateTo($path)
    {
        $this->browserCurrentPath = $path;
        $this->loadBrowserItems();
    }

    public function navigateUp()
    {
        $parentPath = dirname($this->browserCurrentPath);
        if ($parentPath !== $this->browserCurrentPath) {
            $this->browserCurrentPath = $parentPath;
            $this->loadBrowserItems();
        }
    }

    public function selectPath($path)
    {
        $this->scanPath = $path;
        $this->target = $path;
        $this->closeFileBrowser();
    }

    public function browseForPath()
    {
        $this->openFileBrowser();
    }

    protected function loadBrowserItems()
    {
        try {
            if (!File::exists($this->browserCurrentPath) || !File::isDirectory($this->browserCurrentPath)) {
                return;
            }

            $items = [];
            $files = File::files($this->browserCurrentPath);
            $directories = File::directories($this->browserCurrentPath);

            foreach ($directories as $directory) {
                $name = basename($directory);
                if (!str_starts_with($name, '.')) {
                    $items[] = [
                        'name' => $name,
                        'path' => $directory,
                        'type' => 'directory',
                        'size' => null,
                        'modified' => File::lastModified($directory)
                    ];
                }
            }

            foreach ($files as $file) {
                $name = basename($file);
                $extension = File::extension($file);
                
                if (!str_starts_with($name, '.') && in_array($extension, ['php', 'js', 'vue', 'blade.php'])) {
                    $items[] = [
                        'name' => $name,
                        'path' => $file,
                        'type' => 'file',
                        'size' => File::size($file),
                        'modified' => File::lastModified($file)
                    ];
                }
            }

            usort($items, function ($a, $b) {
                if ($a['type'] !== $b['type']) {
                    return $a['type'] === 'directory' ? -1 : 1;
                }
                return strcasecmp($a['name'], $b['name']);
            });

            $this->browserItems = $items;
        } catch (\Exception $e) {
            $this->browserItems = [];
        }
    }

    public function selectAllCategories()
    {
        $this->ruleCategories = array_keys($this->getAllCategories());
        $this->updateRulesApplied();
    }

    public function deselectAllCategories()
    {
        $this->ruleCategories = [];
        $this->updateRulesApplied();
    }

    public function updateRulesApplied()
    {
        $this->rulesApplied = count($this->ruleCategories);
    }

    public function updatedRuleCategories()
    {
        $this->updateRulesApplied();
    }

    public function startScan()
    {
        $this->validate();

        try {
            // Determine the actual scan path
            $scanPath = $this->scanType === 'codebase' ? base_path() : $this->target;
            
            $scan = Scan::create([
                'type' => $this->scanType,
                'target' => $this->scanType, // Use scanType as target for consistency
                'path' => $scanPath,
                'categories' => $this->ruleCategories,
                'status' => 'pending',
                'started_at' => now()
            ]);

            // Store the scan ID for progress tracking
            $this->scanId = $scan->id;
            
            // Add debug logging
            Log::info('Scan created with ID: ' . $this->scanId);
            
            // Dispatch the job with correct parameters
            ScanCodebaseJob::dispatch(
                $scan->id,
                $this->scanType,
                $scanPath,
                $this->ruleCategories,
                []
            );

            $this->scanStatus = 'running';
            $this->currentStep = 5;
            
            $this->addToActivityLog('info', 'Scan started successfully', 'Scan ID: ' . $this->scanId);

            // Emit event to start JavaScript polling with proper validation
            if ($this->scanId) {
                $this->dispatch('start-progress-polling', ['scanId' => $this->scanId]);
            } else {
                throw new \Exception('Scan ID is null after creating scan');
            }

        } catch (\Exception $e) {
            Log::error('Failed to start scan: ' . $e->getMessage());
            session()->flash('error', 'Failed to start scan: ' . $e->getMessage());
            $this->addToActivityLog('error', 'Failed to start scan', $e->getMessage());
        }
    }

    public function refreshProgress()
    {
        if (!$this->scanId) {
            return;
        }

        // Check scan status from database
        $scan = Scan::find($this->scanId);
        if ($scan) {
            $this->scanStatus = $scan->status;
            
            // Calculate elapsed time for any scan with a start time
            if ($scan->started_at) {
                $endTime = $scan->completed_at ?? now();
                $elapsed = $scan->started_at->diffInSeconds($endTime);
                $this->timeElapsed = sprintf('%d:%02d', intval($elapsed / 60), $elapsed % 60);
            }
            
            if ($scan->status === 'completed') {
                $this->scanProgress = 100;
                $this->currentActivity = 'Scan completed successfully';
                $this->filesScanned = $scan->total_files ?? 0;
                $this->issuesFound = $scan->total_issues ?? 0;
                
                // Clear progress cache since scan is done
                Cache::forget("scan_progress_{$this->scanId}");
                
                // Stop polling and emit completion event
                $this->dispatch('stop-progress-polling');
                $this->dispatch('scan-completed', ['scanId' => $this->scanId]);
                return;
            }
            
            if ($scan->status === 'failed') {
                $this->scanProgress = 0;
                $this->currentActivity = 'Scan failed: ' . ($scan->error_message ?? 'Unknown error');
                Cache::forget("scan_progress_{$this->scanId}");
                
                // Stop polling on failure
                $this->dispatch('stop-progress-polling');
                return;
            }
        }

        // Check progress from cache
        $progress = Cache::get("scan_progress_{$this->scanId}");
        if ($progress) {
            $this->scanProgress = $progress['percentage'] ?? 0;
            $this->currentActivity = $progress['message'] ?? '';
            
            // Update scan path if available
            if (isset($progress['target_path']) && $progress['target_path']) {
                $this->scanPath = $progress['target_path'];
            }
            
            // Update current file being scanned
            if (isset($progress['current_file'])) {
                $this->currentFile = $progress['current_file'];
            }
            
            // Update files scanned count
            if (isset($progress['files_processed'])) {
                $this->filesScanned = $progress['files_processed'];
            }
            
            // Update issues found
            if (isset($progress['issues_found_so_far'])) {
                $this->issuesFound = $progress['issues_found_so_far'];
            }
            
            // If progress shows 100%, check database for final status
            if ($this->scanProgress >= 100) {
                $this->refreshProgress(); // Recursive call to check database
            }
        }
    }

    public function pauseScan()
    {
        if ($this->scanId) {
            Cache::put("scan_control_{$this->scanId}", 'pause', 300);
            $this->scanStatus = 'paused';
            $this->addToActivityLog('warning', 'Scan paused by user');
        }
    }

    public function resumeScan()
    {
        if ($this->scanId) {
            Cache::put("scan_control_{$this->scanId}", 'resume', 300);
            $this->scanStatus = 'running';
            $this->addToActivityLog('info', 'Scan resumed by user');
        }
    }

    public function cancelScan()
    {
        if ($this->scanId) {
            Cache::put("scan_control_{$this->scanId}", 'cancel', 300);
            $this->scanStatus = 'cancelled';
            $this->addToActivityLog('error', 'Scan cancelled by user');
        }
    }

    public function viewResults()
    {
        if ($this->scanStatus === 'completed' && $this->scanId) {
            return redirect()->route('codesnoutr.scan-results.show', ['scan' => $this->scanId]);
        }
    }

    public function checkScanStatus($scanId = null)
    {
        $id = $scanId ?: $this->scanId;
        if (!$id) return;

        $scan = Scan::find($id);
        if ($scan) {
            $this->scanId = $scan->id;
            $this->scanStatus = $scan->status;
            
            if ($scan->status === 'completed') {
                $this->scanProgress = 100;
                $this->currentActivity = 'Scan completed successfully';
                $this->filesScanned = $scan->total_files ?? 0;
                $this->issuesFound = $scan->total_issues ?? 0;
                $this->currentStep = 5; // Show progress step
            }
        }
    }

    protected function addToActivityLog($type, $message, $details = null)
    {
        $this->activityLog[] = [
            'type' => $type,
            'message' => $message,
            'details' => $details,
            'timestamp' => now()->format('H:i:s')
        ];
    }

    public function getScanTypeDescription($type)
    {
        $descriptions = [
            'file' => 'Analyze a single PHP file for security vulnerabilities, code quality issues, and best practices.',
            'directory' => 'Scan all PHP files within a specific directory, perfect for analyzing modules or specific components.',
            'codebase' => 'Comprehensive analysis of your entire Laravel application including all PHP files, views, and configuration.'
        ];

        return $descriptions[$type] ?? 'Select a scan type to see the description.';
    }

    // AI Assistant Methods
    
    public function loadAiSuggestions()
    {
        if (!$this->aiAvailable || !$this->aiService) {
            return;
        }

        try {
            $this->aiSuggestions = $this->aiService->getScanSuggestions($this->target ?: base_path());
        } catch (\Exception $e) {
            Log::warning('Failed to load AI suggestions: ' . $e->getMessage());
            $this->aiSuggestions = [];
        }
    }

    public function getAiSuggestions()
    {
        $this->loadAiSuggestions();
        $this->showAiSuggestions = true;
    }

    public function applyScanSuggestion($suggestion)
    {
        if (!is_array($suggestion)) {
            return;
        }

        try {
            // Apply scan type if suggested
            if (isset($suggestion['scan_type'])) {
                $this->selectScanType($suggestion['scan_type']);
            }

            // Apply categories if suggested
            if (isset($suggestion['categories']) && is_array($suggestion['categories'])) {
                $validCategories = array_intersect($suggestion['categories'], array_keys($this->getAllCategories()));
                if (!empty($validCategories)) {
                    $this->ruleCategories = $validCategories;
                    $this->updateRulesApplied();
                }
            }

            // Apply target if suggested
            if (isset($suggestion['target']) && !empty($suggestion['target'])) {
                $this->target = $suggestion['target'];
                $this->scanPath = $suggestion['target'];
            }

            $this->addToActivityLog('info', 'Applied AI suggestion: ' . ($suggestion['title'] ?? 'Smart recommendation'));
            
            // Hide suggestions after applying
            $this->showAiSuggestions = false;

        } catch (\Exception $e) {
            Log::error('Failed to apply AI suggestion: ' . $e->getMessage());
            $this->addToActivityLog('error', 'Failed to apply suggestion', $e->getMessage());
        }
    }

    public function toggleAiSuggestions()
    {
        $this->showAiSuggestions = !$this->showAiSuggestions;
        
        if ($this->showAiSuggestions && empty($this->aiSuggestions)) {
            $this->loadAiSuggestions();
        }
    }

    public function getSmartRecommendations()
    {
        if (!$this->aiAvailable) {
            return [];
        }

        $context = [
            'scan_type' => $this->scanType,
            'target' => $this->target,
            'categories' => $this->ruleCategories,
            'current_step' => $this->currentStep,
        ];

        return Cache::remember('scan_wizard_recommendations_' . md5(json_encode($context)), 300, function () use ($context) {
            try {
                if ($this->aiService && $this->aiAvailable) {
                    return $this->aiService->getContextualHelp('scan_wizard', 'configuration');
                } else {
                    return [];
                }
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    public function getAiInsights()
    {
        if (!$this->aiAvailable) {
            return null;
        }

        // Generate insights based on current configuration
        $insights = [
            'scan_optimization' => $this->getOptimizationTips(),
            'security_focus' => $this->getSecurityRecommendations(),
            'performance_tips' => $this->getPerformanceTips(),
        ];

        return array_filter($insights);
    }

    protected function getOptimizationTips()
    {
        $tips = [];
        
        if ($this->scanType === 'codebase' && count($this->ruleCategories) < 2) {
            $tips[] = [
                'type' => 'info',
                'title' => 'Add More Categories',
                'description' => 'For codebase scans, consider including all rule categories for comprehensive analysis.'
            ];
        }

        if ($this->scanType === 'file' && in_array('performance', $this->ruleCategories)) {
            $tips[] = [
                'type' => 'warning',
                'title' => 'Performance Rules for Single File',
                'description' => 'Performance issues are better detected at directory or codebase level.'
            ];
        }

        return $tips;
    }

    protected function getSecurityRecommendations()
    {
        if (!in_array('security', $this->ruleCategories)) {
            return [
                'type' => 'warning',
                'title' => 'Security Scanning Recommended',
                'description' => 'Always include security rules to detect vulnerabilities and potential threats.'
            ];
        }

        return null;
    }

    protected function getPerformanceTips()
    {
        if ($this->scanType === 'codebase' && !in_array('performance', $this->ruleCategories)) {
            return [
                'type' => 'info',
                'title' => 'Performance Analysis',
                'description' => 'Include performance rules to identify N+1 queries and optimization opportunities.'
            ];
        }

        return null;
    }

    public function render()
    {
        return view('codesnoutr::livewire.scan-wizard');
    }
}
