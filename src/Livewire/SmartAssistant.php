<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Contracts\AI\ConversationServiceInterface;
use Rafaelogic\CodeSnoutr\Contracts\AI\SuggestionServiceInterface;
use Rafaelogic\CodeSnoutr\Contracts\UI\AssistantStateServiceInterface;
use Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Illuminate\Support\Facades\Log;

class SmartAssistant extends Component
{
    // UI State Properties
    public $isOpen = false;
    public $currentContext = 'general';
    public $isLoading = false;
    public $showQuickActions = true;
    
    // Chat Properties
    public $userQuestion = '';
    public $chatHistory = [];
    
    // Data Properties
    public $suggestions = [];
    public $tips = [];
    public $aiAvailable = false;

    // Services
    protected ConversationServiceInterface $conversationService;
    protected SuggestionServiceInterface $suggestionService;
    protected AssistantStateServiceInterface $stateService;
    protected AiAssistantService $aiService;
    
    protected $listeners = [
        'open-assistant' => 'openAssistant',
        'close-assistant' => 'closeAssistant',
        'set-context' => 'setContext',
        'ask-ai' => 'askAI',
        'settings-saved' => 'refreshAiStatus',
        'setting-saved' => 'handleSettingSaved',
    ];

    public function mount()
    {
        $this->initializeServices();
        $this->loadInitialState();
        $this->refreshAiStatus();
    }

    /**
     * Initialize injected services
     */
    protected function initializeServices(): void
    {
        $this->conversationService = app(ConversationServiceInterface::class);
        $this->suggestionService = app(SuggestionServiceInterface::class);
        $this->stateService = app(AssistantStateServiceInterface::class);
        $this->aiService = app(AiAssistantService::class);
    }

    /**
     * Load initial state and data
     */
    protected function loadInitialState(): void
    {
        // Load state from services
        $state = $this->stateService->getStateData();
        $this->isOpen = $state['is_open'];
        $this->currentContext = $state['current_context'];
        $this->showQuickActions = $state['show_quick_actions'];

        // Load initial data
        $this->loadContextualData();
        $this->loadChatHistory();
    }

    /**
     * Load contextual data based on current context
     */
    protected function loadContextualData(): void
    {
        try {
            $this->suggestions = $this->suggestionService->getContextualSuggestions($this->currentContext);
            $this->tips = $this->suggestionService->getContextualTips($this->currentContext);
        } catch (\Exception $e) {
            Log::warning('SmartAssistant: Failed to load contextual data', [
                'context' => $this->currentContext,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Load chat history from session
     */
    protected function loadChatHistory(): void
    {
        $this->chatHistory = $this->conversationService->getChatHistory();
    }

    /**
     * Refresh AI availability status
     */
    public function refreshAiStatus(): void
    {
        try {
            $this->aiAvailable = $this->aiService->isAvailable();
        } catch (\Exception $e) {
            $this->aiAvailable = false;
            Log::warning('SmartAssistant: AI service unavailable', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test AI connection
     */
    public function testAiConnection(): array
    {
        try {
            $result = $this->aiService->testConnection();
            $this->aiAvailable = $result['success'] ?? false;
            return $result;
        } catch (\Exception $e) {
            $this->aiAvailable = false;
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle setting saved event
     */
    public function handleSettingSaved($key): void
    {
        if (str_starts_with($key, 'ai_') || str_starts_with($key, 'openai_')) {
            $this->refreshAiStatus();
        }
    }

    /**
     * Force AI status refresh
     */
    public function forceAiRefresh(): void
    {
        $this->refreshAiStatus();
        $this->dispatch('ai-status-updated', $this->aiAvailable);
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('codesnoutr::livewire.smart-assistant');
    }

    // === UI State Management ===

    /**
     * Open assistant with optional context
     */
    public function openAssistant($context = null): void
    {
        $this->stateService->openAssistant($context);
        $this->isOpen = true;
        
        if ($context && $context !== $this->currentContext) {
            $this->setContext($context);
        }
    }

    /**
     * Close assistant
     */
    public function closeAssistant(): void
    {
        $this->stateService->closeAssistant();
        $this->isOpen = false;
    }

    /**
     * Toggle assistant open/closed
     */
    public function toggleAssistant(): void
    {
        $this->stateService->toggleAssistant();
        $this->isOpen = $this->stateService->isOpen();
    }

    /**
     * Set current context
     */
    public function setContext($context): void
    {
        $this->stateService->setContext($context);
        $this->currentContext = $context;
        $this->loadContextualData();
    }

    // === Conversation Management ===

    /**
     * Ask AI a question
     */
    public function askAI(): void
    {
        if (empty(trim($this->userQuestion))) {
            return;
        }

        if (!$this->aiAvailable) {
            $this->dispatch('show-notification', [
                'type' => 'warning',
                'message' => 'AI service is not available. Please check your settings.'
            ]);
            return;
        }

        $this->setLoading(true);

        try {
            $result = $this->conversationService->sendMessage($this->userQuestion, $this->currentContext);
            
            if ($result['success']) {
                $this->userQuestion = '';
                $this->loadChatHistory();
                $this->dispatch('chat-updated');
            } else {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'message' => $result['error'] ?? 'Failed to get AI response'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('SmartAssistant: Failed to ask AI', [
                'question' => $this->userQuestion,
                'context' => $this->currentContext,
                'error' => $e->getMessage()
            ]);
            
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Failed to process your question. Please try again.'
            ]);
        } finally {
            $this->setLoading(false);
        }
    }

    /**
     * Clear chat history
     */
    public function clearChat(): void
    {
        $this->conversationService->clearChatHistory();
        $this->chatHistory = [];
        $this->dispatch('chat-cleared');
    }

    /**
     * Ask specific pre-defined question
     */
    public function askSpecificQuestion($question): void
    {
        $this->userQuestion = $question;
        $this->askAI();
    }

    // === Suggestions and Tips ===

    /**
     * Get scan suggestions
     */
    public function getScanSuggestions(): array
    {
        return $this->suggestionService->getScanSuggestions();
    }

    /**
     * Get contextual tips
     */
    public function getContextualTips(): array
    {
        return $this->tips;
    }

    /**
     * Apply a suggestion
     */
    public function applySuggestion($suggestionIndex): void
    {
        try {
            $result = $this->suggestionService->applySuggestion($suggestionIndex);
            
            $this->dispatch('show-notification', [
                'type' => $result['success'] ? 'success' : 'warning',
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Failed to apply suggestion: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get quick actions for current context
     */
    public function getQuickActions(): array
    {
        return $this->suggestionService->getQuickActions($this->currentContext);
    }

    /**
     * Get code examples for current context
     */
    public function getCodeExamples(): array
    {
        return $this->suggestionService->getCodeExamples($this->currentContext);
    }

    // === Context-Specific Methods ===

    /**
     * Get security examples
     */
    public function getSecurityExamples(): array
    {
        return $this->suggestionService->getCodeExamples('security');
    }

    /**
     * Get best practices
     */
    public function getBestPractices(): array
    {
        return $this->suggestionService->getContextualTips($this->currentContext);
    }

    /**
     * Get performance tips
     */
    public function getPerformanceTips(): array
    {
        return $this->suggestionService->getCodeExamples('performance');
    }

    // === Utility Methods ===

    /**
     * Set loading state
     */
    protected function setLoading(bool $loading): void
    {
        $this->isLoading = $loading;
        $this->stateService->setLoading($loading);
    }

    /**
     * Get context icon
     */
    public function getContextIcon($context): string
    {
        return $this->stateService->getContextIcon($context);
    }

    /**
     * Get context display name
     */
    public function getContextName($context): string
    {
        return $this->stateService->getContextName($context);
    }

    /**
     * Get debug information
     */
    public function getDebugInfo(): array
    {
        return [
            'ai_available' => $this->aiAvailable,
            'current_context' => $this->currentContext,
            'is_open' => $this->isOpen,
            'chat_history_count' => count($this->chatHistory),
            'suggestions_count' => count($this->suggestions),
            'tips_count' => count($this->tips)
        ];
    }

    /**
     * Check AI status
     */
    public function checkAiStatus(): array
    {
        return [
            'available' => $this->aiAvailable,
            'last_check' => now()->toISOString(),
            'context' => $this->currentContext
        ];
    }
}