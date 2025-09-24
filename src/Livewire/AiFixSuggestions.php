<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService;
use Illuminate\Support\Facades\Log;

class AiFixSuggestions extends Component
{
    public Issue $issue;
    public $fixSuggestion = null;
    public $isLoading = false;
    public $showSuggestion = false;
    public $aiAvailable = false;
    public $error = null;

    protected $aiService;

    public function mount(Issue $issue)
    {
        $this->issue = $issue;
        try {
            $this->aiService = new AiAssistantService();
            $this->aiAvailable = $this->aiService ? $this->aiService->isAvailable() : false;
        } catch (\Exception $e) {
            $this->aiService = null;
            $this->aiAvailable = false;
            Log::warning('Failed to initialize AI service in AiFixSuggestions: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('codesnoutr::livewire.ai-fix-suggestions');
    }

    public function getFixSuggestion()
    {
        if (!$this->aiAvailable || !$this->aiService) {
            $this->error = 'AI Assistant is not available. Please configure AI integration in settings.';
            return;
        }

        $this->isLoading = true;
        $this->error = null;

        try {
            $this->fixSuggestion = $this->aiService->getFixSuggestion($this->issue);
            $this->showSuggestion = true;

            if (!$this->fixSuggestion) {
                $this->error = 'Unable to generate fix suggestion for this issue.';
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to get AI suggestion: ' . $e->getMessage();
            Log::error('AI fix suggestion failed for issue ' . $this->issue->id . ': ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function applyAutomatedFix()
    {
        if (!$this->aiAvailable || !$this->aiService || !$this->fixSuggestion) {
            return;
        }

        try {
            $result = $this->aiService->autoApplyFix($this->issue);
            
            if ($result) {
                $this->dispatch('fix-applied', issueId: $this->issue->id);
                $this->dispatch('notification', [
                    'message' => 'Fix applied successfully!',
                    'type' => 'success'
                ]);
            } else {
                $this->dispatch('notification', [
                    'message' => 'Automated fix is not available for this issue.',
                    'type' => 'warning'
                ]);
            }

        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'message' => 'Failed to apply fix: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function copyFixToClipboard()
    {
        if ($this->fixSuggestion && isset($this->fixSuggestion['code_example'])) {
            $this->dispatch('copy-to-clipboard', text: $this->fixSuggestion['code_example']);
            $this->dispatch('notification', [
                'message' => 'Fix code copied to clipboard!',
                'type' => 'success'
            ]);
        }
    }

    public function hideSuggestion()
    {
        $this->showSuggestion = false;
        $this->fixSuggestion = null;
        $this->error = null;
    }

    public function getConfidenceColor()
    {
        if (!$this->fixSuggestion || !isset($this->fixSuggestion['confidence'])) {
            return 'gray';
        }

        $confidence = $this->fixSuggestion['confidence'];

        if ($confidence >= 0.8) return 'green';
        if ($confidence >= 0.6) return 'yellow';
        if ($confidence >= 0.4) return 'orange';
        return 'red';
    }

    public function getConfidenceText()
    {
        if (!$this->fixSuggestion || !isset($this->fixSuggestion['confidence'])) {
            return 'Unknown';
        }

        $confidence = $this->fixSuggestion['confidence'];

        if ($confidence >= 0.8) return 'High Confidence';
        if ($confidence >= 0.6) return 'Medium Confidence';
        if ($confidence >= 0.4) return 'Low Confidence';
        return 'Very Low Confidence';
    }
}
