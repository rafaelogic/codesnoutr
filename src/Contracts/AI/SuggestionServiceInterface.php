<?php

namespace Rafaelogic\CodeSnoutr\Contracts\AI;

interface SuggestionServiceInterface
{
    /**
     * Get contextual suggestions based on current context
     */
    public function getContextualSuggestions(string $context): array;

    /**
     * Get scan-specific suggestions
     */
    public function getScanSuggestions(): array;

    /**
     * Get contextual tips for current context
     */
    public function getContextualTips(string $context): array;

    /**
     * Apply a specific suggestion
     */
    public function applySuggestion(int $suggestionIndex): array;

    /**
     * Get quick actions for current context
     */
    public function getQuickActions(string $context): array;

    /**
     * Get code examples for context
     */
    public function getCodeExamples(string $context): array;
}