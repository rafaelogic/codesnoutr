<?php

namespace Rafaelogic\CodeSnoutr\Services\Wizard;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Rafaelogic\CodeSnoutr\Contracts\Services\Wizard\WizardAiServiceContract;
use Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService;

/**
 * Wizard AI Service
 * 
 * Provides AI-powered suggestions, recommendations, and contextual help for scan wizards.
 */
class WizardAiService implements WizardAiServiceContract
{
    protected ?AiAssistantService $aiService;
    protected bool $available = false;

    public function __construct()
    {
        try {
            $this->aiService = new AiAssistantService();
            $this->available = $this->aiService ? $this->aiService->isAvailable() : false;
        } catch (\Exception $e) {
            $this->aiService = null;
            $this->available = false;
            Log::warning('Failed to initialize AI service for wizard: ' . $e->getMessage());
        }
    }

    /**
     * Check if AI service is available
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * Load AI suggestions for scan configuration
     */
    public function loadScanSuggestions(string $target): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        try {
            return $this->aiService->getScanSuggestions($target);
        } catch (\Exception $e) {
            Log::warning('Failed to load AI suggestions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Apply an AI suggestion to scan configuration
     */
    public function applySuggestion(array $suggestion, array $currentConfig): array
    {
        if (!is_array($suggestion)) {
            return $currentConfig;
        }

        $updatedConfig = $currentConfig;

        try {
            // Apply scan type if suggested
            if (isset($suggestion['scan_type'])) {
                $updatedConfig['scanType'] = $suggestion['scan_type'];
            }

            // Apply categories if suggested
            if (isset($suggestion['categories']) && is_array($suggestion['categories'])) {
                $validCategories = array_intersect(
                    $suggestion['categories'], 
                    ['security', 'performance', 'quality', 'laravel']
                );
                
                if (!empty($validCategories)) {
                    $updatedConfig['ruleCategories'] = $validCategories;
                }
            }

            // Apply target if suggested
            if (isset($suggestion['target']) && !empty($suggestion['target'])) {
                $updatedConfig['target'] = $suggestion['target'];
                $updatedConfig['scanPath'] = $suggestion['target'];
            }

            return $updatedConfig;

        } catch (\Exception $e) {
            Log::error('Failed to apply AI suggestion: ' . $e->getMessage());
            return $currentConfig;
        }
    }

    /**
     * Get smart recommendations based on context
     */
    public function getSmartRecommendations(array $context): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        $cacheKey = 'scan_wizard_recommendations_' . md5(json_encode($context));
        
        return Cache::remember($cacheKey, 300, function () use ($context) {
            try {
                return $this->aiService->getContextualHelp('scan_wizard', 'configuration');
            } catch (\Exception $e) {
                Log::warning('Failed to get smart recommendations: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get contextual AI insights
     */
    public function getAiInsights(array $config): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        $insights = [
            'scan_optimization' => $this->getOptimizationTips($config),
            'security_focus' => $this->getSecurityRecommendations($config),
            'performance_tips' => $this->getPerformanceTips($config),
        ];

        return array_filter($insights);
    }

    /**
     * Get optimization tips for current configuration
     */
    public function getOptimizationTips(array $config): array
    {
        $tips = [];
        $scanType = $config['scanType'] ?? '';
        $categories = $config['ruleCategories'] ?? [];
        
        if ($scanType === 'codebase' && count($categories) < 2) {
            $tips[] = [
                'type' => 'info',
                'title' => 'Add More Categories',
                'description' => 'For codebase scans, consider including all rule categories for comprehensive analysis.'
            ];
        }

        if ($scanType === 'file' && in_array('performance', $categories)) {
            $tips[] = [
                'type' => 'warning',
                'title' => 'Performance Rules for Single File',
                'description' => 'Performance issues are better detected at directory or codebase level.'
            ];
        }

        return $tips;
    }

    /**
     * Get security recommendations
     */
    public function getSecurityRecommendations(array $config): ?array
    {
        $categories = $config['ruleCategories'] ?? [];
        
        if (!in_array('security', $categories)) {
            return [
                'type' => 'warning',
                'title' => 'Security Scanning Recommended',
                'description' => 'Always include security rules to detect vulnerabilities and potential threats.'
            ];
        }

        return null;
    }

    /**
     * Get performance tips
     */
    public function getPerformanceTips(array $config): ?array
    {
        $scanType = $config['scanType'] ?? '';
        $categories = $config['ruleCategories'] ?? [];
        
        if ($scanType === 'codebase' && !in_array('performance', $categories)) {
            return [
                'type' => 'info',
                'title' => 'Performance Analysis',
                'description' => 'Include performance rules to identify N+1 queries and optimization opportunities.'
            ];
        }

        return null;
    }

    /**
     * Get category descriptions
     */
    public function getCategoryDescriptions(): array
    {
        return [
            'security' => [
                'title' => 'Security Issues',
                'description' => 'Detect vulnerabilities like SQL injection, XSS, and insecure code patterns',
                'icon' => 'shield-check',
                'color' => 'red'
            ],
            'performance' => [
                'title' => 'Performance Issues',
                'description' => 'Find N+1 queries, memory leaks, and inefficient algorithms',
                'icon' => 'lightning-bolt',
                'color' => 'yellow'
            ],
            'quality' => [
                'title' => 'Code Quality',
                'description' => 'Check coding standards, complexity, and maintainability issues',
                'icon' => 'star',
                'color' => 'blue'
            ],
            'laravel' => [
                'title' => 'Laravel Specific',
                'description' => 'Laravel best practices, Eloquent optimization, and framework patterns',
                'icon' => 'code',
                'color' => 'green'
            ]
        ];
    }
}