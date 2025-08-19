<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Services\AiAssistantService;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmartAssistant extends Component
{
    public $isOpen = false;
    public $currentContext = 'general';
    public $suggestions = [];
    public $tips = [];
    public $isLoading = false;
    public $aiAvailable = false;
    public $userQuestion = '';
    public $chatHistory = [];
    public $showQuickActions = true;

    protected $aiService;
    
    protected $listeners = [
        'open-assistant' => 'openAssistant',
        'close-assistant' => 'closeAssistant',
        'set-context' => 'setContext',
        'ask-ai' => 'askAI',
        'settings-saved' => 'refreshAiStatus',
        'setting-saved' => 'handleSettingSaved',
        'ai-settings-updated' => 'forceAiRefresh',
    ];

    public function mount()
    {
        $this->refreshAiStatus();
        $this->loadInitialData();
    }

    /**
     * Get or create AI service instance
     */
    protected function getAiService()
    {
        if (!$this->aiService) {
            try {
                $this->aiService = new AiAssistantService();
            } catch (\Exception $e) {
                Log::error('Failed to create AI service: ' . $e->getMessage());
                $this->aiService = null;
            }
        }
        return $this->aiService;
    }

    public function getDebugInfo()
    {
        $service = $this->getAiService();
        
        if (!$service) {
            return [
                'service_created' => false,
                'error' => 'AI Service could not be created'
            ];
        }

        return [
            'service_created' => true,
            'ai_enabled_setting' => Setting::getValue('ai_enabled', false),
            'api_key_exists' => !empty(Setting::getValue('openai_api_key')),
            'api_key_length' => strlen(Setting::getValue('openai_api_key', '') ?: ''),
            'model' => Setting::getValue('openai_model', 'gpt-3.5-turbo'),
            'is_available' => $service->isAvailable(),
        ];
    }

    public function refreshAiStatus()
    {
        try {
            $service = $this->getAiService();
            $this->aiAvailable = $service ? $service->isAvailable() : false;
            
            if ($this->aiAvailable) {
                $this->loadInitialData();
                
                // Add success message to chat if there's an active conversation
                if (!empty($this->chatHistory)) {
                    $this->chatHistory[] = [
                        'type' => 'assistant',
                        'message' => 'AI Assistant status refreshed and is now available!',
                        'timestamp' => now()->format('H:i')
                    ];
                }
            } else {
                // Add info message about AI not being available
                if (!empty($this->chatHistory)) {
                    $this->chatHistory[] = [
                        'type' => 'error',
                        'message' => 'AI Assistant is still not available. Please check your AI integration settings.',
                        'timestamp' => now()->format('H:i')
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->aiService = null;
            $this->aiAvailable = false;
            
            if (!empty($this->chatHistory)) {
                $this->chatHistory[] = [
                    'type' => 'error',
                    'message' => 'Error refreshing AI status: ' . $e->getMessage(),
                    'timestamp' => now()->format('H:i')
                ];
            }
        }
    }

    public function testAiConnection()
    {
        $service = $this->getAiService();
        
        if (!$service) {
            $this->chatHistory[] = [
                'type' => 'error',
                'message' => 'AI Service could not be created. Check your settings.',
                'timestamp' => now()->format('H:i')
            ];
            return;
        }

        $this->isLoading = true;
        
        try {
            $result = $service->testConnection();
            
            $this->chatHistory[] = [
                'type' => $result['success'] ? 'assistant' : 'error',
                'message' => $result['message'] . ' ' . ($result['details'] ?? ''),
                'timestamp' => now()->format('H:i')
            ];

            if ($result['success']) {
                // Force refresh the AI service and availability status
                $this->aiService = $service;
                $this->aiAvailable = $service->isAvailable();
                
                if ($this->aiAvailable) {
                    $this->loadInitialData();
                    
                    // Add a welcome message
                    $this->chatHistory[] = [
                        'type' => 'assistant',
                        'message' => 'Great! AI Assistant is now ready. You can ask me questions about code scanning, get suggestions, or request tips for better code quality.',
                        'timestamp' => now()->format('H:i')
                    ];
                }
            } else {
                // If test failed, ensure availability is false
                $this->aiAvailable = false;
            }
        } catch (\Exception $e) {
            $this->chatHistory[] = [
                'type' => 'error',
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'timestamp' => now()->format('H:i')
            ];
            $this->aiAvailable = false;
        } finally {
            $this->isLoading = false;
        }
    }

    public function handleSettingSaved($key)
    {
        // Refresh AI status when AI-related settings are saved
        if (str_starts_with($key, 'ai_') || str_starts_with($key, 'openai_')) {
            $this->refreshAiStatus();
        }
    }

    public function forceAiRefresh()
    {
        try {
            // Clear related caches first
            Cache::forget('ai_initial_suggestions');
            Cache::tags(['ai_assistant'])->flush();
            
            // Force recreation of AI service
            $this->aiService = null;
            $this->aiAvailable = false;
            
            // Force a complete refresh of AI service
            $this->refreshAiStatus();
            
            // Test the connection to ensure it's actually working
            $service = $this->getAiService();
            if ($service) {
                $testResult = $service->testConnection();
                if ($testResult['success']) {
                    $this->aiAvailable = true;
                } else {
                    $this->aiAvailable = false;
                }
            }
            
            // Add feedback to chat if there's an active conversation
            if (!empty($this->chatHistory)) {
                $this->chatHistory[] = [
                    'type' => 'assistant',
                    'message' => 'AI Assistant has been completely refreshed. Status: ' . ($this->aiAvailable ? 'Available' : 'Not Available') . 
                                 ($this->aiAvailable ? '' : ' - Please check your settings.'),
                    'timestamp' => now()->format('H:i')
                ];
            }
            
            Log::info('Force AI refresh completed', [
                'aiAvailable' => $this->aiAvailable,
                'aiService' => $service ? 'exists' : 'null'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Force AI refresh failed', ['error' => $e->getMessage()]);
            
            if (!empty($this->chatHistory)) {
                $this->chatHistory[] = [
                    'type' => 'error',
                    'message' => 'Failed to refresh AI Assistant: ' . $e->getMessage(),
                    'timestamp' => now()->format('H:i')
                ];
            }
        }
    }

    public function render()
    {
        return view('codesnoutr::livewire.smart-assistant');
    }

    public function openAssistant($context = null)
    {
        $this->isOpen = true;
        if ($context) {
            $this->setContext($context);
        }
    }

    public function closeAssistant()
    {
        $this->isOpen = false;
        $this->userQuestion = '';
    }

    public function toggleAssistant()
    {
        $this->isOpen = !$this->isOpen;
        if ($this->isOpen) {
            $this->loadContextualData();
        }
    }

    public function setContext($context)
    {
        $this->currentContext = $context;
        $this->loadContextualData();
    }

    public function askAI()
    {
        // Debug logging
        Log::info('askAI called', [
            'userQuestion' => $this->userQuestion,
            'aiAvailable' => $this->aiAvailable,
            'aiService' => $this->aiService ? 'exists' : 'null'
        ]);

        if (empty($this->userQuestion)) {
            $this->chatHistory[] = [
                'type' => 'error',
                'message' => 'Please enter a question first.',
                'timestamp' => now()->format('H:i')
            ];
            return;
        }

        if (!$this->aiAvailable) {
            $this->chatHistory[] = [
                'type' => 'error',
                'message' => 'AI assistant is not available. Please check your AI integration settings.',
                'timestamp' => now()->format('H:i')
            ];
            return;
        }

        $service = $this->getAiService();
        if (!$service) {
            $this->chatHistory[] = [
                'type' => 'error',
                'message' => 'AI service is not initialized. Please refresh the assistant.',
                'timestamp' => now()->format('H:i')
            ];
            return;
        }

        $this->isLoading = true;
        $question = $this->userQuestion;
        $this->userQuestion = '';

        // Add user question to chat history
        $this->chatHistory[] = [
            'type' => 'user',
            'message' => $question,
            'timestamp' => now()->format('H:i')
        ];

        try {
            $response = $this->getAIResponse($question);
            
            // Add AI response to chat history
            $this->chatHistory[] = [
                'type' => 'assistant',
                'message' => $response,
                'timestamp' => now()->format('H:i')
            ];

        } catch (\Exception $e) {
            Log::error('askAI error', ['error' => $e->getMessage()]);
            $this->chatHistory[] = [
                'type' => 'error',
                'message' => 'Sorry, I encountered an error: ' . $e->getMessage(),
                'timestamp' => now()->format('H:i')
            ];
        } finally {
            $this->isLoading = false;
        }

        // Dispatch event to auto-scroll chat
        $this->dispatch('chatUpdated');
    }    public function clearChat()
    {
        $this->chatHistory = [];
    }

    public function getScanSuggestions()
    {
        $this->isLoading = true;
        
        try {
            if ($this->aiAvailable) {
                $service = $this->getAiService();
                if ($service) {
                    $this->suggestions = $service->getScanSuggestions();
                } else {
                    $this->suggestions = [];
                }
            } else {
                $this->suggestions = [];
            }
        } catch (\Exception $e) {
            $this->suggestions = [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function getContextualTips()
    {
        $this->isLoading = true;
        
        try {
            if ($this->aiAvailable) {
                $service = $this->getAiService();
                if ($service) {
                    $this->tips = $service->getContextualHelp($this->currentContext);
                } else {
                    $this->tips = [];
                }
            } else {
                $this->tips = [];
            }
        } catch (\Exception $e) {
            $this->tips = [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function applySuggestion($suggestionIndex)
    {
        if (isset($this->suggestions[$suggestionIndex])) {
            $suggestion = $this->suggestions[$suggestionIndex];
            
            // Emit event to apply suggestion (e.g., to scan wizard)
            $this->dispatch('apply-scan-suggestion', $suggestion);
            
            // Add to chat history
            $this->chatHistory[] = [
                'type' => 'action',
                'message' => 'Applied suggestion: ' . $suggestion['title'],
                'timestamp' => now()->format('H:i')
            ];
        }
    }

    public function getQuickActions()
    {
        return [
            [
                'title' => 'Code Examples',
                'action' => 'getCodeExamples',
                'icon' => 'code',
                'description' => 'Show common code patterns'
            ],
            [
                'title' => 'Security Examples',
                'action' => 'getSecurityExamples',
                'icon' => 'shield',
                'description' => 'Security best practices with code'
            ],
            [
                'title' => 'Get Scan Suggestions',
                'action' => 'getScanSuggestions',
                'icon' => 'search',
                'description' => 'Get AI-powered scan recommendations'
            ],
            [
                'title' => 'Best Practices',
                'action' => 'getBestPractices',
                'icon' => 'star',
                'description' => 'Learn coding best practices'
            ]
        ];
    }

    public function getCodeExamples()
    {
        $this->askSpecificQuestion('Show me PHP code examples for common security issues and how to fix them with proper code.');
    }

    public function getSecurityExamples()
    {
        $this->askSpecificQuestion('Give me PHP code examples demonstrating SQL injection prevention, XSS protection, and input validation.');
    }

    public function getBestPractices()
    {
        $this->askSpecificQuestion('What are the most important PHP/Laravel best practices I should follow?');
    }

    public function getPerformanceTips()
    {
        $this->askSpecificQuestion('What are the key performance optimization techniques for Laravel applications?');
    }

    public function askSpecificQuestion($question)
    {
        $this->userQuestion = $question;
        $this->askAI();
    }

    protected function loadInitialData()
    {
        if ($this->aiAvailable) {
            $service = $this->getAiService();
            if ($service) {
                // Load some default suggestions
                $this->suggestions = Cache::remember('ai_initial_suggestions', 300, function () use ($service) {
                    try {
                        return $service->getScanSuggestions();
                    } catch (\Exception $e) {
                        return [];
                    }
                });
            }
        }
    }

    protected function loadContextualData()
    {
        if (!$this->aiAvailable) {
            return;
        }

        $service = $this->getAiService();
        if (!$service) {
            return;
        }

        $this->isLoading = true;
        
        try {
            // Load contextual tips based on current context
            $this->tips = $service->getContextualHelp($this->currentContext);
            
            // Load context-specific suggestions
            if ($this->currentContext === 'scan_wizard') {
                $this->suggestions = $service->getScanSuggestions();
            }

        } catch (\Exception $e) {
            // Handle gracefully
        } finally {
            $this->isLoading = false;
        }
    }

    protected function getAIResponse($question): string
    {
        $service = $this->getAiService();
        
        if (!$service || !$this->aiAvailable) {
            return "AI assistant is not available. Please check your AI integration settings.";
        }

        // Build a contextual prompt with code example instructions
        $codeKeywords = ['example', 'code', 'function', 'class', 'method', 'snippet', 'how to', 'show me'];
        $isCodeRequest = false;
        
        foreach ($codeKeywords as $keyword) {
            if (stripos($question, $keyword) !== false) {
                $isCodeRequest = true;
                break;
            }
        }

        $prompt = "User question in CodeSnoutr context (current context: {$this->currentContext}): {$question}\n\n";
        
        if ($isCodeRequest) {
            $prompt .= "The user is asking for code examples. Please provide:\n" .
                      "1. A clear explanation\n" .
                      "2. Code examples wrapped in ```php code blocks\n" .
                      "3. Brief comments explaining the code\n" .
                      "4. Best practices related to PHP/Laravel code scanning\n\n" .
                      "Format any code using markdown code blocks with ```php and ``` delimiters.";
        } else {
            $prompt .= "Provide a helpful, specific answer related to PHP/Laravel code scanning and best practices. " .
                      "If code examples would help, include them using ```php code blocks. " .
                      "Keep it concise but informative.";
        }

        try {
            $response = $service->askAI($prompt, $isCodeRequest ? 600 : 400);
            
            Log::info('AI Response received', ['response_type' => gettype($response), 'response' => $response]);
            
            // Handle different response formats
            if (is_array($response)) {
                return $this->processComplexResponse($response);
            }
            
            // If it's already a string, return it
            if (is_string($response)) {
                return $response;
            }
            
            // If response is null or unrecognized format
            return "I'm sorry, I couldn't process your question right now. Please try again or check the AI integration settings.";
            
        } catch (\Exception $e) {
            Log::error('getAIResponse error', ['error' => $e->getMessage()]);
            return "I'm sorry, I couldn't process your question right now. Please try again or check the AI integration settings.";
        }
    }

    protected function processComplexResponse($response): string
    {
        logger('Processing complex response structure:', $response);
        
        // Handle string response directly
        if (is_string($response)) {
            return $response;
        }
        
        // Start recursive processing
        $formattedResponse = $this->formatResponseRecursively($response);
        
        // Fallback to JSON if no structure matches
        if (empty($formattedResponse)) {
            $formattedResponse = "```json\n" . json_encode($response, JSON_PRETTY_PRINT) . "\n```";
        }
        
        return $formattedResponse;
    }
    
    protected function formatResponseRecursively($data, $level = 0)
    {
        $result = '';
        
        if (is_string($data)) {
            return $data . "\n\n";
        }
        
        if (!is_array($data)) {
            return '';
        }
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Handle common keys with special formatting
                switch (strtolower($key)) {
                    case 'explanation':
                    case 'response':
                    case 'message':
                    case 'text':
                    case 'content':
                        $result .= $value . "\n\n";
                        break;
                    case 'code':
                    case 'code_example':
                        $cleanCode = str_replace(['```php', '```', 'php\n'], '', $value);
                        $result .= "```php\n" . trim($cleanCode) . "\n```\n\n";
                        break;
                    case 'description':
                    case 'comment':
                    case 'title':
                        $result .= "**" . $value . "**\n\n";
                        break;
                    default:
                        // Auto-detect code blocks
                        if (strpos($value, '<?php') !== false || 
                            strpos($value, 'function') !== false ||
                            strpos($value, 'class ') !== false ||
                            strpos($value, '$') !== false && strlen($value) > 20) {
                            $cleanCode = str_replace(['```php', '```', 'php\n'], '', $value);
                            $result .= "```php\n" . trim($cleanCode) . "\n```\n\n";
                        } else {
                            $result .= $value . "\n\n";
                        }
                }
            } elseif (is_array($value)) {
                // Handle arrays with special formatting based on key names
                $keyLower = strtolower($key);
                
                if (in_array($keyLower, ['code_examples', 'examples'])) {
                    $result .= "## Code Examples\n\n";
                    foreach ($value as $example) {
                        $result .= $this->formatCodeExample($example);
                    }
                } elseif (in_array($keyLower, ['best_practices', 'practices', 'tips', 'recommendations'])) {
                    $result .= "## " . ucfirst(str_replace('_', ' ', $key)) . "\n\n";
                    foreach ($value as $item) {
                        if (is_string($item)) {
                            $result .= "• " . $item . "\n";
                        } elseif (is_array($item)) {
                            $nested = $this->formatResponseRecursively($item, $level + 1);
                            $result .= "• " . trim($nested) . "\n";
                        }
                    }
                    $result .= "\n";
                } else {
                    // For other arrays, recursively process
                    $nested = $this->formatResponseRecursively($value, $level + 1);
                    if (!empty($nested)) {
                        if ($level === 0 && is_string($key) && !is_numeric($key)) {
                            $result .= "## " . ucfirst(str_replace('_', ' ', $key)) . "\n\n";
                        }
                        $result .= $nested;
                    }
                }
            }
        }
        
        return $result;
    }
    
    protected function formatCodeExample($example)
    {
        $result = '';
        
        if (is_string($example)) {
            if (strpos($example, '<?php') !== false || strpos($example, 'function') !== false) {
                $cleanCode = str_replace(['```php', '```', 'php\n'], '', $example);
                $result .= "```php\n" . trim($cleanCode) . "\n```\n\n";
            } else {
                $result .= $example . "\n\n";
            }
        } elseif (is_array($example)) {
            // Handle structured code examples
            if (isset($example['comment']) || isset($example['description'])) {
                $desc = $example['comment'] ?? $example['description'] ?? '';
                $result .= "**" . $desc . "**\n\n";
            }
            
            if (isset($example['code'])) {
                $cleanCode = str_replace(['```php', '```', 'php\n'], '', $example['code']);
                $result .= "```php\n" . trim($cleanCode) . "\n```\n\n";
            }
            
            // Handle any other fields in the example
            foreach ($example as $subKey => $subValue) {
                if (!in_array($subKey, ['comment', 'description', 'code']) && is_string($subValue)) {
                    if (strpos($subValue, '<?php') !== false || strpos($subValue, 'function') !== false) {
                        $cleanCode = str_replace(['```php', '```', 'php\n'], '', $subValue);
                        $result .= "```php\n" . trim($cleanCode) . "\n```\n\n";
                    } else {
                        $result .= $subValue . "\n\n";
                    }
                }
            }
        }
        
        return $result;
    }

    public function checkAiStatus()
    {
        $service = $this->getAiService();
        
        $status = [
            'ai_service_exists' => $service !== null,
            'ai_available' => $this->aiAvailable,
            'settings_check' => []
        ];
        
        try {
            $status['settings_check'] = [
                'ai_enabled' => Setting::getValue('ai_enabled', false),
                'api_key_exists' => !empty(Setting::getValue('openai_api_key')),
                'api_key_length' => strlen(Setting::getValue('openai_api_key', '') ?: ''),
                'model' => Setting::getValue('openai_model', 'gpt-3.5-turbo'),
            ];
            
            if ($service) {
                $status['service_available'] = $service->isAvailable();
                $testResult = $service->testConnection();
                $status['connection_test'] = $testResult;
            }
        } catch (\Exception $e) {
            $status['error'] = $e->getMessage();
        }
        
        $this->chatHistory[] = [
            'type' => 'assistant',
            'message' => 'AI Status Check: ' . json_encode($status, JSON_PRETTY_PRINT),
            'timestamp' => now()->format('H:i')
        ];
    }

    public function getContextIcon($context)
    {
        return match($context) {
            'scan_wizard' => 'search',
            'dashboard' => 'chart-bar',
            'results' => 'clipboard-list',
            'settings' => 'cog',
            default => 'chat'
        };
    }

    public function getContextName($context)
    {
        return match($context) {
            'scan_wizard' => 'Scan Wizard',
            'dashboard' => 'Dashboard',
            'results' => 'Scan Results',
            'settings' => 'Settings',
            default => 'General'
        };
    }
}
