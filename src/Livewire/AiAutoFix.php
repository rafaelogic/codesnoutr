<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Services\AiAssistantService;
use Rafaelogic\CodeSnoutr\Services\AutoFixService;
use Illuminate\Support\Facades\Log;

class AiAutoFix extends Component
{
    public $issueId;
    public Issue $issue;
    public $recommendations = null;
    public $fixPreview = null;
    public $isAnalyzing = false;
    public $isApplying = false;
    public $showPreview = false;
    public $showRecommendations = false;
    public $aiAvailable = false;
    public $autoFixEnabled = false;
    public $error = null;
    public $fixApplied = false;
    public $backupPath = null;

    protected $aiService;
    protected $autoFixService;

    protected $listeners = [
        'refreshAiStatus' => 'checkAiAvailability'
    ];

    public function mount($issueId)
    {
        $this->issueId = $issueId;
        $this->issue = Issue::findOrFail($issueId);
        $this->checkAiAvailability();
    }

    public function render()
    {
        return view('codesnoutr::livewire.ai-auto-fix');
    }

    public function checkAiAvailability()
    {
        try {
            $this->aiService = new AiAssistantService();
            $this->autoFixService = new AutoFixService($this->aiService);
            
            $this->aiAvailable = $this->aiService->isAvailable();
            $this->autoFixEnabled = $this->autoFixService->isAutoFixEnabled();
        } catch (\Exception $e) {
            $this->aiService = null;
            $this->autoFixService = null;
            $this->aiAvailable = false;
            $this->autoFixEnabled = false;
            Log::warning('Failed to initialize AI services in AiAutoFix: ' . $e->getMessage());
        }
    }

    /**
     * Analyze the issue and get AI recommendations
     */
    public function analyzeIssue()
    {
        if (!$this->aiAvailable || !$this->aiService) {
            $this->error = 'AI Assistant is not available. Please configure AI integration in settings.';
            return;
        }

        $this->isAnalyzing = true;
        $this->error = null;
        $this->recommendations = null;

        try {
            // Get AI recommendations
            $recommendations = $this->aiService->getFixSuggestion($this->issue);
            
            if ($recommendations) {
                $this->recommendations = $recommendations;
                $this->showRecommendations = true;
                
                $this->dispatch('notification', [
                    'message' => 'AI analysis completed successfully!',
                    'type' => 'success'
                ]);
            } else {
                $this->error = 'Unable to generate recommendations for this issue.';
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to analyze issue: ' . $e->getMessage();
            Log::error('AI analysis failed for issue ' . $this->issue->id . ': ' . $e->getMessage());
        } finally {
            $this->isAnalyzing = false;
        }
    }

    /**
     * Generate auto-fix code and show preview
     */
    public function generateAutoFix()
    {
        if (!$this->autoFixEnabled || !$this->autoFixService) {
            $this->error = 'Auto-fix is not enabled. Please check your AI settings.';
            return;
        }

        $this->isAnalyzing = true;
        $this->error = null;
        $this->fixPreview = null;

        try {
            // Generate the fix
            $fixData = $this->autoFixService->generateFix($this->issue);
            
            if (!$fixData) {
                $this->error = 'Unable to generate an automatic fix for this issue.';
                return;
            }

            // Generate preview
            $preview = $this->autoFixService->previewFix($this->issue, $fixData);
            
            if (!$preview) {
                $this->error = 'Unable to generate fix preview.';
                return;
            }

            $this->fixPreview = [
                'fix_data' => $fixData,
                'preview' => $preview
            ];
            
            $this->showPreview = true;

            $this->dispatch('notification', [
                'message' => 'Auto-fix generated successfully! Review the preview below.',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            $this->error = 'Failed to generate auto-fix: ' . $e->getMessage();
            Log::error('Auto-fix generation failed for issue ' . $this->issue->id . ': ' . $e->getMessage());
        } finally {
            $this->isAnalyzing = false;
        }
    }

    /**
     * Apply the generated fix
     */
    public function applyFix()
    {
        if (!$this->fixPreview || !$this->autoFixService) {
            $this->error = 'No fix available to apply.';
            return;
        }

        $this->isApplying = true;
        $this->error = null;

        try {
            $result = $this->autoFixService->applyFix($this->issue, $this->fixPreview['fix_data']);
            
            if ($result['success']) {
                $this->fixApplied = true;
                $this->backupPath = $result['backup_path'];
                
                // Refresh the issue
                $this->issue->refresh();
                
                $this->dispatch('notification', [
                    'message' => 'Fix applied successfully! A backup has been created.',
                    'type' => 'success'
                ]);

                $this->dispatch('issue-fixed', issueId: $this->issue->id);
                
            } else {
                $this->error = $result['message'];
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to apply fix: ' . $e->getMessage();
            Log::error('Fix application failed for issue ' . $this->issue->id . ': ' . $e->getMessage());
        } finally {
            $this->isApplying = false;
        }
    }

    /**
     * Restore from backup
     */
    public function restoreFromBackup()
    {
        if (!$this->autoFixService) {
            $this->error = 'Auto-fix service not available.';
            return;
        }

        try {
            $success = $this->autoFixService->restoreFromBackup($this->issue);
            
            if ($success) {
                $this->fixApplied = false;
                $this->fixPreview = null;
                $this->showPreview = false;
                
                // Refresh the issue
                $this->issue->refresh();
                
                $this->dispatch('notification', [
                    'message' => 'File restored from backup successfully!',
                    'type' => 'success'
                ]);

                $this->dispatch('issue-restored', issueId: $this->issue->id);
                
            } else {
                $this->error = 'Failed to restore from backup. Backup file may not exist.';
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to restore from backup: ' . $e->getMessage();
            Log::error('Backup restoration failed for issue ' . $this->issue->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Copy fix code to clipboard
     */
    public function copyFixCode()
    {
        if ($this->recommendations && isset($this->recommendations['code_example'])) {
            $this->dispatch('copy-to-clipboard', text: $this->recommendations['code_example']);
            $this->dispatch('notification', [
                'message' => 'Fix code copied to clipboard!',
                'type' => 'success'
            ]);
        } elseif ($this->fixPreview && isset($this->fixPreview['fix_data']['code'])) {
            $this->dispatch('copy-to-clipboard', text: $this->fixPreview['fix_data']['code']);
            $this->dispatch('notification', [
                'message' => 'Fix code copied to clipboard!',
                'type' => 'success'
            ]);
        }
    }

    /**
     * Hide recommendations
     */
    public function hideRecommendations()
    {
        $this->showRecommendations = false;
        $this->recommendations = null;
        $this->error = null;
    }

    /**
     * Hide preview
     */
    public function hidePreview()
    {
        $this->showPreview = false;
        $this->fixPreview = null;
        $this->error = null;
    }

    /**
     * Reset component state
     */
    public function resetState()
    {
        $this->recommendations = null;
        $this->fixPreview = null;
        $this->showRecommendations = false;
        $this->showPreview = false;
        $this->error = null;
        $this->fixApplied = false;
        $this->backupPath = null;
    }

    /**
     * Get confidence color based on confidence level
     */
    public function getConfidenceColor($confidence): string
    {
        if ($confidence >= 0.8) return 'green';
        if ($confidence >= 0.6) return 'yellow';
        if ($confidence >= 0.4) return 'orange';
        return 'red';
    }

    /**
     * Get confidence text description
     */
    public function getConfidenceText($confidence): string
    {
        if ($confidence >= 0.8) return 'High Confidence';
        if ($confidence >= 0.6) return 'Medium Confidence';
        if ($confidence >= 0.4) return 'Low Confidence';
        return 'Very Low Confidence';
    }

    /**
     * Check if auto-fix is safe to apply
     */
    public function isSafeToAutoApply(): bool
    {
        return $this->fixPreview && 
               isset($this->fixPreview['fix_data']['safe_to_automate']) &&
               $this->fixPreview['fix_data']['safe_to_automate'] &&
               ($this->fixPreview['fix_data']['confidence'] ?? 0) >= 0.7;
    }

    /**
     * Get fix type description
     */
    public function getFixTypeDescription($type): string
    {
        return match($type) {
            'replace' => 'Replace existing code',
            'insert' => 'Insert new code',
            'delete' => 'Remove code',
            default => 'Modify code'
        };
    }
}
