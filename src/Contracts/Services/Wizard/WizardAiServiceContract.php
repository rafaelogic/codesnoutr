<?php

namespace Rafaelogic\CodeSnoutr\Contracts\Services\Wizard;

/**
 * Wizard AI Service Contract
 * 
 * Provides AI-powered suggestions, recommendations, and contextual help
 * for scan wizards.
 */
interface WizardAiServiceContract
{
    /**
     * Check if AI service is available
     */
    public function isAvailable(): bool;

    /**
     * Load AI suggestions for scan configuration
     */
    public function loadScanSuggestions(string $target): array;

    /**
     * Apply an AI suggestion to scan configuration
     */
    public function applySuggestion(array $suggestion, array $currentConfig): array;

    /**
     * Get smart recommendations based on context
     */
    public function getSmartRecommendations(array $context): array;

    /**
     * Get contextual AI insights
     */
    public function getAiInsights(array $config): array;

    /**
     * Get optimization tips for current configuration
     */
    public function getOptimizationTips(array $config): array;

    /**
     * Get security recommendations
     */
    public function getSecurityRecommendations(array $config): ?array;

    /**
     * Get performance tips
     */
    public function getPerformanceTips(array $config): ?array;
}