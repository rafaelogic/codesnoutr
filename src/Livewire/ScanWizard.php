<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Rafaelogic\CodeSnoutr\Contracts\Services\Wizard\StepNavigationServiceContract;
use Rafaelogic\CodeSnoutr\Contracts\Services\Wizard\FileBrowserServiceContract;
use Rafaelogic\CodeSnoutr\Contracts\Services\Wizard\ScanExecutionServiceContract;
use Rafaelogic\CodeSnoutr\Contracts\Services\Wizard\ScanConfigurationServiceContract;
use Rafaelogic\CodeSnoutr\Contracts\Services\Wizard\WizardAiServiceContract;

/**
 * Refactored Scan Wizard Component
 * 
 * This component has been refactored from a 4689-line monolith into a focused
 * component that delegates to specialized services for different concerns.
 */
class ScanWizard extends Component
{
    // Core wizard state
    public $currentStep = 1;
    public $totalSteps = 5;
    
    // File browser state
    public $showFileBrowser = false;
    public $browserCurrentPath = '';
    
    // Scan configuration
    public $scanType = 'codebase';
    public $scanTarget = 'full_codebase';
    public $target = '';
    public $scanPath = '';
    public $ruleCategories = [];
    
    // Scan execution state
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
    
    // AI Assistant state
    public $aiSuggestions = [];
    public $showAiSuggestions = false;
    public $aiAvailable = false;

    // Service dependencies - lazy loaded
    protected ?StepNavigationServiceContract $stepService = null;
    protected ?FileBrowserServiceContract $browserService = null;
    protected ?ScanExecutionServiceContract $executionService = null;
    protected ?ScanConfigurationServiceContract $configService = null;
    protected ?WizardAiServiceContract $aiService = null;

    protected $listeners = [
        'apply-scan-suggestion' => 'applyScanSuggestion',
        'get-ai-suggestions' => 'getAiSuggestions',
    ];

    // Lazy load services only when needed
    protected function getStepService(): StepNavigationServiceContract
    {
        return $this->stepService ??= app(StepNavigationServiceContract::class);
    }

    protected function getBrowserService(): FileBrowserServiceContract
    {
        return $this->browserService ??= app(FileBrowserServiceContract::class);
    }

    protected function getExecutionService(): ScanExecutionServiceContract
    {
        return $this->executionService ??= app(ScanExecutionServiceContract::class);
    }

    protected function getConfigService(): ScanConfigurationServiceContract
    {
        return $this->configService ??= app(ScanConfigurationServiceContract::class);
    }

    protected function getAiService(): WizardAiServiceContract
    {
        return $this->aiService ??= app(WizardAiServiceContract::class);
    }

    // Computed properties using services
    public function getAllCategoriesProperty()
    {
        return $this->getConfigService()->getAllCategories();
    }

    // Method version for view compatibility
    public function getAllCategories()
    {
        return $this->getConfigService()->getAllCategories();
    }

    public function getBrowserItemsProperty()
    {
        if (!$this->showFileBrowser || empty($this->browserCurrentPath)) {
            return [];
        }
        
        return $this->getBrowserService()->loadDirectoryItems($this->browserCurrentPath);
    }

    public function getActivityLogProperty()
    {
        return $this->activityLog;
    }

    public function mount()
    {
        // Only do essential initialization
        $this->ruleCategories = ['security', 'performance', 'quality', 'laravel']; // Default categories
        $this->rulesApplied = count($this->ruleCategories) * 10; // Rough estimate
        $this->browserCurrentPath = base_path();
        $this->activityLog = [];
        
        // Defer AI initialization to avoid blocking
        $this->aiAvailable = false; // Will be checked when needed
    }

    // Step Navigation Methods (delegated to StepNavigationService)
    public function goToStep($step)
    {
        if ($this->getStepService()->goToStep($step, $this->totalSteps)) {
            $this->currentStep = $step;
        }
    }

    public function nextStep()
    {
        $data = [
            'scanType' => $this->scanType,
            'target' => $this->target,
            'ruleCategories' => $this->ruleCategories
        ];
        
        if ($this->getStepService()->validateStep($this->currentStep, $data, [])) {
            $this->currentStep = $this->getStepService()->nextStep($this->currentStep, $this->totalSteps);
        }
    }

    public function previousStep()
    {
        $this->currentStep = $this->getStepService()->previousStep($this->currentStep);
    }

    // File Browser Methods (delegated to FileBrowserService)
    public function openFileBrowser()
    {
        $this->showFileBrowser = true;
        $this->browserCurrentPath = base_path();
    }

    public function closeFileBrowser()
    {
        $this->showFileBrowser = false;
        $this->browserCurrentPath = '';
    }

    public function navigateTo($path)
    {
        if ($this->getBrowserService()->navigateTo($path)) {
            $this->browserCurrentPath = $path;
        }
    }

    public function navigateUp()
    {
        $this->browserCurrentPath = $this->getBrowserService()->navigateUp($this->browserCurrentPath);
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

    // Scan Configuration Methods (delegated to ScanConfigurationService)
    public function selectScanType($type)
    {
        $this->scanType = $type;
        $this->ruleCategories = $this->getConfigService()->getDefaultCategories($type);
        $this->updateRulesApplied();
    }

    public function selectScanTarget($target)
    {
        $this->scanTarget = $target;
        
        if ($target === 'full_codebase') {
            $this->scanPath = '';
            $this->target = '';
        }
    }

    public function selectAllCategories()
    {
        $this->ruleCategories = $this->getConfigService()->selectAllCategories();
        $this->updateRulesApplied();
    }

    public function deselectAllCategories()
    {
        $this->ruleCategories = $this->getConfigService()->deselectAllCategories();
        $this->updateRulesApplied();
    }

    public function updateRulesApplied()
    {
        $this->rulesApplied = $this->getConfigService()->getRulesCount($this->ruleCategories);
    }

    public function updatedRuleCategories()
    {
        $this->updateRulesApplied();
    }

    public function toggleCategory($category)
    {
        if (in_array($category, $this->ruleCategories)) {
            $this->ruleCategories = array_values(array_diff($this->ruleCategories, [$category]));
        } else {
            $this->ruleCategories[] = $category;
        }
        
        $this->updateRulesApplied();
    }

    public function getScanTypeDescription($type)
    {
        return $this->getConfigService()->getScanTypeDescription($type);
    }

    // Scan Execution Methods (delegated to ScanExecutionService)
    public function startScan()
    {
        $config = [
            'scanType' => $this->scanType,
            'target' => $this->scanType === 'codebase' ? base_path() : $this->target,
            'ruleCategories' => $this->ruleCategories
        ];

        $result = $this->getExecutionService()->startScan($config);
        
        if ($result['success']) {
            $this->scanId = $result['scanId'];
            $this->scanStatus = 'running';
            $this->currentStep = 5;
            
            $this->addToActivityLog('info', 'Scan started successfully', 'Scan ID: ' . $this->scanId);
            
            // Emit event to start JavaScript polling
            $this->dispatch('start-progress-polling', ['scanId' => $this->scanId]);
        } else {
            session()->flash('error', 'Failed to start scan: ' . $result['error']);
            $this->addToActivityLog('error', 'Failed to start scan', $result['error']);
        }
    }

    public function refreshProgress()
    {
        if (!$this->scanId) {
            return;
        }

        // Check scan status from database
        $scanStatus = $this->getExecutionService()->checkScanStatus($this->scanId);
        
        if ($scanStatus['found']) {
            $this->scanStatus = $scanStatus['status'];
            $this->timeElapsed = $scanStatus['elapsed_time'] ?? '0:00';
            
            if ($scanStatus['status'] === 'completed') {
                $this->scanProgress = 100;
                $this->currentActivity = 'Scan completed successfully';
                $this->filesScanned = $scanStatus['total_files'] ?? 0;
                $this->issuesFound = $scanStatus['total_issues'] ?? 0;
                
                $this->dispatch('stop-progress-polling');
                $this->dispatch('scan-completed', ['scanId' => $this->scanId]);
                return;
            }
            
            if ($scanStatus['status'] === 'failed') {
                $this->scanProgress = 0;
                $this->currentActivity = 'Scan failed: ' . ($scanStatus['error_message'] ?? 'Unknown error');
                $this->dispatch('stop-progress-polling');
                return;
            }
        }

        // Check progress from cache
        $progress = $this->getExecutionService()->getScanProgress($this->scanId);
        $this->scanProgress = $progress['percentage'];
        $this->currentActivity = $progress['message'];
        $this->currentFile = $progress['current_file'];
        $this->filesScanned = $progress['files_processed'];
        $this->issuesFound = $progress['issues_found'];
        
        if ($progress['target_path']) {
            $this->scanPath = $progress['target_path'];
        }
    }

    public function pauseScan()
    {
        if ($this->scanId && $this->getExecutionService()->pauseScan($this->scanId)) {
            $this->scanStatus = 'paused';
            $this->addToActivityLog('warning', 'Scan paused by user');
        }
    }

    public function resumeScan()
    {
        if ($this->scanId && $this->getExecutionService()->resumeScan($this->scanId)) {
            $this->scanStatus = 'running';
            $this->addToActivityLog('info', 'Scan resumed by user');
        }
    }

    public function cancelScan()
    {
        if ($this->scanId && $this->getExecutionService()->cancelScan($this->scanId)) {
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

        $scanStatus = $this->getExecutionService()->checkScanStatus($id);
        
        if ($scanStatus['found']) {
            $this->scanId = $id;
            $this->scanStatus = $scanStatus['status'];
            
            if ($scanStatus['status'] === 'completed') {
                $this->scanProgress = 100;
                $this->currentActivity = 'Scan completed successfully';
                $this->filesScanned = $scanStatus['total_files'] ?? 0;
                $this->issuesFound = $scanStatus['total_issues'] ?? 0;
                $this->currentStep = 5;
            }
        }
    }

    // AI Methods (delegated to WizardAiService)
    public function loadAiSuggestions()
    {
        // Check AI availability on demand
        $this->aiAvailable = $this->getAiService()->isAvailable();
        
        if ($this->aiAvailable) {
            $this->aiSuggestions = $this->getAiService()->loadScanSuggestions($this->target ?: base_path());
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
            $currentConfig = [
                'scanType' => $this->scanType,
                'target' => $this->target,
                'scanPath' => $this->scanPath,
                'ruleCategories' => $this->ruleCategories
            ];

            $updatedConfig = $this->getAiService()->applySuggestion($suggestion, $currentConfig);
            
            // Apply the updated configuration
            $this->scanType = $updatedConfig['scanType'] ?? $this->scanType;
            $this->target = $updatedConfig['target'] ?? $this->target;
            $this->scanPath = $updatedConfig['scanPath'] ?? $this->scanPath;
            $this->ruleCategories = $updatedConfig['ruleCategories'] ?? $this->ruleCategories;
            
            $this->updateRulesApplied();
            $this->addToActivityLog('info', 'Applied AI suggestion: ' . ($suggestion['title'] ?? 'Smart recommendation'));
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

        return $this->getAiService()->getSmartRecommendations($context);
    }

    public function getAiInsights()
    {
        if (!$this->aiAvailable) {
            return null;
        }

        $config = [
            'scanType' => $this->scanType,
            'target' => $this->target,
            'ruleCategories' => $this->ruleCategories
        ];

        return $this->getAiService()->getAiInsights($config);
    }

    // Helper Methods
    protected function addToActivityLog($type, $message, $details = null)
    {
        $this->activityLog[] = [
            'type' => $type,
            'message' => $message,
            'details' => $details,
            'timestamp' => now()->format('H:i:s')
        ];
    }

    public function render()
    {
        return view('codesnoutr::livewire.scan-wizard');
    }
}
