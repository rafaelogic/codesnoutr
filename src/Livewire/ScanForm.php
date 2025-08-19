<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\ScanManager;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ScanForm extends Component
{
    public $scanType = 'file';
    public $target = '';
    
    public function browseForPath()
    {
        $this->currentBrowsePath = base_path();
        $this->loadBrowseItems();
        $this->showFileBrowser = true;
    }
    
    public function closeFileBrowser()
    {
        $this->showFileBrowser = false;
        $this->browseItems = [];
        $this->currentBrowsePath = '';
    }
    
    public function browseToDirectory($path)
    {
        $this->currentBrowsePath = $path;
        $this->loadBrowseItems();
    }
    
    public function browseToParent()
    {
        $parentPath = dirname($this->currentBrowsePath);
        if ($parentPath !== $this->currentBrowsePath && strlen($parentPath) >= strlen(base_path())) {
            $this->currentBrowsePath = $parentPath;
            $this->loadBrowseItems();
        }
    }
    
    public function selectPath($path)
    {
        // Make path relative to base_path if it's within the project
        $basePath = base_path();
        if (str_starts_with($path, $basePath)) {
            $relativePath = str_replace($basePath . '/', '', $path);
            $this->target = $relativePath;
        } else {
            $this->target = $path;
        }
        
        $this->closeFileBrowser();
        $this->resetErrorBag('target');
    }
    
    protected function loadBrowseItems()
    {
        $this->browseItems = [];
        
        if (!is_dir($this->currentBrowsePath) || !is_readable($this->currentBrowsePath)) {
            return;
        }
        
        try {
            $items = scandir($this->currentBrowsePath);
            
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                
                $fullPath = $this->currentBrowsePath . '/' . $item;
                
                // Skip hidden files and common ignore patterns
                if (str_starts_with($item, '.') || 
                    in_array($item, ['vendor', 'node_modules', 'storage', 'bootstrap', 'public'])) {
                    continue;
                }
                
                if (is_dir($fullPath)) {
                    $this->browseItems[] = [
                        'name' => $item,
                        'path' => $fullPath,
                        'type' => 'directory',
                        'extension' => null,
                    ];
                } elseif ($this->scanType === 'file') {
                    // Only show PHP files for file scanning
                    $extension = pathinfo($item, PATHINFO_EXTENSION);
                    if ($extension === 'php' || str_ends_with($item, '.blade.php')) {
                        $this->browseItems[] = [
                            'name' => $item,
                            'path' => $fullPath,
                            'type' => 'file',
                            'extension' => $extension,
                        ];
                    }
                }
            }
            
            // Sort: directories first, then files, both alphabetically
            usort($this->browseItems, function ($a, $b) {
                if ($a['type'] !== $b['type']) {
                    return $a['type'] === 'directory' ? -1 : 1;
                }
                return strcmp($a['name'], $b['name']);
            });
            
        } catch (\Exception $e) {
            // Handle permission errors gracefully
            $this->browseItems = [];
        }
    }
    public $ruleCategories = [];
    public $scanOptions = [];
    public $fileExtensionsString = '';
    public $isScanning = false;
    public $scanProgress = 0;
    public $currentScan = null;
    public $showAdvanced = false;
    public $showFileBrowser = false;
    public $currentBrowsePath = '';
    public $browseItems = [];

    protected $rules = [
        'scanType' => 'required|in:file,directory,codebase',
        'target' => 'required|string|min:1',
        'ruleCategories' => 'required|array|min:1',
        'ruleCategories.*' => 'in:security,performance,quality,laravel',
    ];

    protected $messages = [
        'target.required' => 'Please specify a target file or directory to scan.',
        'scanType.in' => 'Please select a valid scan type.',
        'ruleCategories.required' => 'Please select at least one rule category.',
        'ruleCategories.min' => 'Please select at least one rule category.',
    ];

    public function mount()
    {
        $this->ruleCategories = ['security', 'performance', 'quality', 'laravel'];
        $this->scanOptions = [
            'ignore_vendor' => true,
            'ignore_node_modules' => true,
            'ignore_storage' => true,
            'max_file_size' => 10485760, // 10MB
            'file_extensions' => ['php'],
        ];
        $this->fileExtensionsString = implode(',', $this->scanOptions['file_extensions']);
        
        // Set target based on initial scan type
        if ($this->scanType === 'codebase') {
            $this->target = base_path();
        }
    }

    public function updatedRuleCategories()
    {
        // Reset validation errors when categories change
        $this->resetErrorBag('ruleCategories');
    }

    public function updatedFileExtensionsString($value)
    {
        $this->scanOptions['file_extensions'] = array_map('trim', explode(',', $value));
    }

    public function render()
    {
        return view('codesnoutr::livewire.scan-form');
    }

    public function startScan()
    {
        // Dynamic validation based on scan type
        $rules = [
            'scanType' => 'required|in:file,directory,codebase',
            'ruleCategories' => 'required|array|min:1',
            'ruleCategories.*' => 'in:security,performance,quality,laravel',
        ];

        $messages = [
            'scanType.required' => 'Please select a scan type.',
            'scanType.in' => 'Please select a valid scan type.',
            'ruleCategories.required' => 'Please select at least one rule category.',
            'ruleCategories.min' => 'Please select at least one rule category.',
            'ruleCategories.*.in' => 'Please select valid rule categories.',
        ];

        // Target is only required for file and directory scans
        if (in_array($this->scanType, ['file', 'directory'])) {
            $rules['target'] = 'required|string|min:1';
            $messages['target.required'] = 'Please specify a target file or directory to scan.';
            $messages['target.min'] = 'Please specify a valid target file or directory.';
        }

        $this->validate($rules, $messages);

        if ($this->isScanning) {
            return;
        }

        // Validate target exists (only for file/directory scans)
        if (in_array($this->scanType, ['file', 'directory']) && !$this->validateTarget()) {
            return;
        }

        $this->isScanning = true;
        $this->scanProgress = 0;
        $this->currentScan = null;

        try {
            // Start scanning process - ScanManager will create the scan record
            $this->performScan();

        } catch (\Exception $e) {
            $this->isScanning = false;
            $this->addError('scan', 'Failed to start scan: ' . $e->getMessage());
        }
    }

    protected function validateTarget()
    {
        $basePath = base_path();
        $fullPath = $this->scanType === 'codebase' ? $basePath : $basePath . '/' . ltrim($this->target, '/');

        switch ($this->scanType) {
            case 'file':
                if (!file_exists($fullPath) || !is_file($fullPath)) {
                    $this->addError('target', 'The specified file does not exist.');
                    return false;
                }
                if (!str_ends_with($fullPath, '.php')) {
                    $this->addError('target', 'Only PHP files are currently supported.');
                    return false;
                }
                break;

            case 'directory':
                if (!file_exists($fullPath) || !is_dir($fullPath)) {
                    $this->addError('target', 'The specified directory does not exist.');
                    return false;
                }
                break;

            case 'codebase':
                if (!file_exists($fullPath) || !is_dir($fullPath)) {
                    $this->addError('target', 'The codebase directory does not exist.');
                    return false;
                }
                break;
        }

        return true;
    }

    protected function performScan()
    {
        // Create scan record first
        $scan = Scan::create([
            'type' => $this->scanType,
            'target' => $this->target,
            'status' => 'pending',
            'scan_options' => [
                'path' => $this->target,
                'categories' => $this->ruleCategories,
                'options' => $this->scanOptions,
            ],
            'started_at' => now(),
        ]);

        $this->currentScan = $scan;

        // Dispatch background job for scanning
        \Rafaelogic\CodeSnoutr\Jobs\ScanCodebaseJob::dispatch(
            $scan->id,
            $this->scanType,
            $this->target,
            $this->ruleCategories,
            $this->scanOptions
        );

        // Start polling for progress
        $this->startProgressPolling();
    }

    public function startProgressPolling()
    {
        $this->dispatch('start-progress-polling', scanId: $this->currentScan->id);
    }

    public function checkScanProgress()
    {
        if (!$this->currentScan) {
            return;
        }

        // Get progress from cache
        $progressData = \Illuminate\Support\Facades\Cache::get("scan_progress_{$this->currentScan->id}");
        
        if ($progressData) {
            $this->scanProgress = $progressData['percentage'];
            $this->dispatch('scan-progress-updated', 
                progress: $this->scanProgress,
                message: $progressData['message'] ?? ''
            );
        }

        // Check if scan is completed
        $scan = $this->currentScan->fresh();
        if ($scan && in_array($scan->status, ['completed', 'failed'])) {
            $this->isScanning = false;
            
            if ($scan->status === 'completed') {
                $this->scanProgress = 100;
                $this->dispatch('scan-completed', 
                    scanId: $scan->id,
                    issuesFound: $scan->total_issues ?? 0,
                    filesScanned: $scan->total_files ?? 0
                );
                
                // Reset form
                $this->reset(['target', 'currentScan', 'scanProgress']);
            } else {
                $this->addError('scan', 'Scan failed: ' . ($scan->error_message ?? 'Unknown error'));
            }
            
            $this->dispatch('stop-progress-polling');
        }
    }

    protected function updateProgress($progress)
    {
        $this->scanProgress = $progress;
        $this->dispatch('scan-progress-updated', progress: $progress);
    }

    public function cancelScan()
    {
        if ($this->currentScan) {
            $this->currentScan->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);
        }

        $this->isScanning = false;
        $this->scanProgress = 0;
        $this->currentScan = null;

        $this->dispatch('scan-cancelled');
    }

    public function toggleAdvanced()
    {
        $this->showAdvanced = !$this->showAdvanced;
    }

    public function updatedScanType()
    {
        // Reset target when scan type changes
        if ($this->scanType === 'codebase') {
            // For codebase scans, automatically set to the current project's base path
            $this->target = base_path();
        } else {
            // For file and directory scans, reset to empty so user can input
            $this->target = '';
        }
        $this->resetErrorBag('target');
    }

    public function setScanType($type)
    {
        $this->scanType = $type;
        
        if ($this->scanType === 'codebase') {
            // For codebase scans, automatically set to the current project's base path
            $this->target = base_path();
        } else {
            // For file and directory scans, reset to empty so user can input
            $this->target = '';
        }
        
        $this->resetErrorBag();
    }

    public function toggleRuleCategory($category)
    {
        if (in_array($category, $this->ruleCategories)) {
            $this->ruleCategories = array_values(array_diff($this->ruleCategories, [$category]));
        } else {
            $this->ruleCategories[] = $category;
        }
        
        // Reset validation errors for this field
        $this->resetErrorBag('ruleCategories');
    }

    public function selectAllCategories()
    {
        $this->ruleCategories = ['security', 'performance', 'quality', 'laravel'];
        $this->resetErrorBag('ruleCategories');
    }

    public function deselectAllCategories()
    {
        $this->ruleCategories = [];
        $this->resetErrorBag('ruleCategories');
    }

    public function updateScanOption($key, $value)
    {
        if ($key === 'file_extensions') {
            // Convert comma-separated string to array
            $this->scanOptions[$key] = array_map('trim', explode(',', $value));
        } else {
            $this->scanOptions[$key] = $value;
        }
    }

    public function updatedScanOptions($value, $key)
    {
        // Handle file extensions conversion
        if ($key === 'file_extensions' && is_string($value)) {
            $this->scanOptions['file_extensions'] = array_map('trim', explode(',', $value));
        }
    }

    public function getProgressPercentage()
    {
        return min(100, max(0, $this->scanProgress));
    }

    public function getScanTypeLabel($type)
    {
        return match($type) {
            'file' => 'Single File',
            'directory' => 'Directory',
            'codebase' => 'Full Codebase',
            default => 'Unknown'
        };
    }

    public function getCategoryLabel($category)
    {
        return match($category) {
            'security' => 'Security',
            'performance' => 'Performance',
            'quality' => 'Code Quality',
            'laravel' => 'Laravel Best Practices',
            default => ucfirst($category)
        };
    }

    public function getCategoryDescription($category)
    {
        return match($category) {
            'security' => 'SQL injection, XSS, hardcoded credentials, and other security vulnerabilities',
            'performance' => 'N+1 queries, memory issues, caching opportunities, and performance bottlenecks',
            'quality' => 'Code standards, complexity, documentation, and maintainability issues',
            'laravel' => 'Laravel-specific best practices for Eloquent, routes, validation, and Blade',
            default => 'General code analysis'
        };
    }
}
