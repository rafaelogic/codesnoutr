<?php

namespace Rafaelogic\CodeSnoutr\Contracts\AI;

interface ConversationServiceInterface
{
    /**
     * Send a message to AI and get response
     */
    public function sendMessage(string $message, string $context = 'general'): array;

    /**
     * Get chat history for current session
     */
    public function getChatHistory(): array;

    /**
     * Clear chat history
     */
    public function clearChatHistory(): void;

    /**
     * Process and format AI response
     */
    public function processResponse(string $response): string;

    /**
     * Add message to chat history
     */
    public function addMessageToHistory(string $type, string $content, array $metadata = []): void;
}