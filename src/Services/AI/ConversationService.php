<?php

namespace Rafaelogic\CodeSnoutr\Services\AI;

use Rafaelogic\CodeSnoutr\Contracts\AI\ConversationServiceInterface;
use Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ConversationService implements ConversationServiceInterface
{
    protected AiAssistantService $aiService;
    protected string $sessionKey = 'smart_assistant_chat_history';

    public function __construct(AiAssistantService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Send a message to AI and get response
     */
    public function sendMessage(string $message, string $context = 'general'): array
    {
        try {
            // Add user message to history
            $this->addMessageToHistory('user', $message, [
                'context' => $context,
                'timestamp' => now()->toISOString()
            ]);

            // Get AI response
            $response = $this->getAIResponse($message, $context);

            // Add AI response to history
            $this->addMessageToHistory('assistant', $response, [
                'context' => $context,
                'timestamp' => now()->toISOString()
            ]);

            return [
                'success' => true,
                'response' => $response,
                'context' => $context
            ];

        } catch (\Exception $e) {
            Log::error('ConversationService: Failed to send message', [
                'message' => $message,
                'context' => $context,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to get AI response: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get chat history for current session
     */
    public function getChatHistory(): array
    {
        return Session::get($this->sessionKey, []);
    }

    /**
     * Clear chat history
     */
    public function clearChatHistory(): void
    {
        Session::forget($this->sessionKey);
    }

    /**
     * Process and format AI response
     */
    public function processResponse(string $response): string
    {
        // Handle JSON responses
        if ($this->isJsonResponse($response)) {
            return $this->processComplexResponse($response);
        }

        // Format basic text response
        return $this->formatTextResponse($response);
    }

    /**
     * Add message to chat history
     */
    public function addMessageToHistory(string $type, string $content, array $metadata = []): void
    {
        $history = $this->getChatHistory();

        $history[] = [
            'type' => $type,
            'content' => $content,
            'metadata' => $metadata,
            'timestamp' => now()->toISOString()
        ];

        // Keep only last 50 messages to prevent session bloat
        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }

        Session::put($this->sessionKey, $history);
    }

    /**
     * Get AI response with context
     */
    protected function getAIResponse(string $question, string $context): string
    {
        $contextualPrompt = $this->buildContextualPrompt($question, $context);
        
        $response = $this->aiService->askAI($contextualPrompt, 500);

        if (!$response || !isset($response['response'])) {
            throw new \Exception('No response from AI service');
        }

        return $this->processResponse($response['response']);
    }

    /**
     * Build contextual prompt based on context
     */
    protected function buildContextualPrompt(string $question, string $context): string
    {
        $contextPrompts = [
            'security' => "You are a Laravel security expert. Focus on security best practices, vulnerabilities, and secure coding patterns. Question: ",
            'performance' => "You are a Laravel performance optimization expert. Focus on performance improvements, caching, database optimization, and efficiency. Question: ",
            'quality' => "You are a code quality expert. Focus on clean code, refactoring, design patterns, and maintainability. Question: ",
            'laravel' => "You are a Laravel framework expert. Focus on Laravel best practices, features, and conventions. Question: ",
            'general' => "You are a helpful coding assistant specializing in Laravel and PHP development. Question: "
        ];

        $prompt = $contextPrompts[$context] ?? $contextPrompts['general'];
        return $prompt . $question;
    }

    /**
     * Check if response is JSON
     */
    protected function isJsonResponse(string $response): bool
    {
        json_decode($response);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Process complex JSON response
     */
    protected function processComplexResponse(string $response): string
    {
        try {
            $data = json_decode($response, true);
            
            if (isset($data['formatted_response'])) {
                return $data['formatted_response'];
            }
            
            if (isset($data['content'])) {
                return $this->formatResponseRecursively($data['content']);
            }
            
            return $this->formatResponseRecursively($data);
            
        } catch (\Exception $e) {
            Log::warning('ConversationService: Failed to process complex response', [
                'response' => $response,
                'error' => $e->getMessage()
            ]);
            
            return $response; // Return original if processing fails
        }
    }

    /**
     * Format text response with basic formatting
     */
    protected function formatTextResponse(string $response): string
    {
        // Add basic formatting
        $response = trim($response);
        
        // Format code blocks
        $response = preg_replace('/```(\w+)\n(.*?)\n```/s', '<pre><code class="language-$1">$2</code></pre>', $response);
        $response = preg_replace('/`([^`]+)`/', '<code>$1</code>', $response);
        
        // Format line breaks
        $response = nl2br($response);
        
        return $response;
    }

    /**
     * Format response data recursively
     */
    protected function formatResponseRecursively($data, int $level = 0): string
    {
        if (is_string($data)) {
            return $this->formatTextResponse($data);
        }
        
        if (is_array($data)) {
            $formatted = '';
            $indent = str_repeat('  ', $level);
            
            foreach ($data as $key => $value) {
                if (is_numeric($key)) {
                    $formatted .= $indent . "• " . $this->formatResponseRecursively($value, $level + 1) . "\n";
                } else {
                    $formatted .= $indent . "**{$key}**: " . $this->formatResponseRecursively($value, $level + 1) . "\n";
                }
            }
            
            return trim($formatted);
        }
        
        return (string) $data;
    }
}