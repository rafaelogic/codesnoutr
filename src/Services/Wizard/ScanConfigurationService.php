<?php

namespace Rafaelogic\CodeSnoutr\Services\Wizard;

use Illuminate\Support\Facades\Validator;
use Rafaelogic\CodeSnoutr\Contracts\Services\Wizard\ScanConfigurationServiceContract;

/**
 * Scan Configuration Service
 * 
 * Handles scan settings, rule categories, and configuration management.
 */
class ScanConfigurationService implements ScanConfigurationServiceContract
{
    protected array $categories;
    protected array $scanTypes;

    public function __construct()
    {
        $this->categories = [
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

        $this->scanTypes = [
            'file' => [
                'title' => 'Single File',
                'description' => 'Analyze a single PHP file for security vulnerabilities, code quality issues, and best practices.',
                'recommended_categories' => ['security', 'quality']
            ],
            'directory' => [
                'title' => 'Directory Scan',
                'description' => 'Scan all PHP files within a specific directory, perfect for analyzing modules or specific components.',
                'recommended_categories' => ['security', 'quality', 'performance']
            ],
            'codebase' => [
                'title' => 'Full Codebase',
                'description' => 'Comprehensive analysis of your entire Laravel application including all PHP files, views, and configuration.',
                'recommended_categories' => ['security', 'performance', 'quality', 'laravel']
            ]
        ];
    }

    /**
     * Get all available scan categories
     */
    public function getAllCategories(): array
    {
        return $this->categories;
    }

    /**
     * Get default categories for a scan type
     */
    public function getDefaultCategories(string $scanType): array
    {
        return $this->scanTypes[$scanType]['recommended_categories'] ?? ['security', 'quality'];
    }

    /**
     * Validate scan configuration
     */
    public function validateConfiguration(array $config): bool
    {
        $rules = [
            'scanType' => 'required|in:file,directory,codebase',
            'target' => 'required_unless:scanType,codebase',
            'ruleCategories' => 'required|array|min:1',
            'ruleCategories.*' => 'in:security,performance,quality,laravel'
        ];

        $validator = Validator::make($config, $rules);
        
        return !$validator->fails();
    }

    /**
     * Get scan type description
     */
    public function getScanTypeDescription(string $scanType): string
    {
        return $this->scanTypes[$scanType]['description'] ?? 'Select a scan type to see the description.';
    }

    /**
     * Update rule categories and count
     */
    public function updateRuleCategories(array $categories): array
    {
        $validCategories = array_intersect($categories, array_keys($this->categories));
        
        return [
            'categories' => $validCategories,
            'count' => count($validCategories)
        ];
    }

    /**
     * Select all available categories
     */
    public function selectAllCategories(): array
    {
        return array_keys($this->categories);
    }

    /**
     * Deselect all categories
     */
    public function deselectAllCategories(): array
    {
        return [];
    }

    /**
     * Get rules count for categories
     */
    public function getRulesCount(array $categories): int
    {
        return count($categories);
    }

    /**
     * Get available scan types
     */
    public function getScanTypes(): array
    {
        return $this->scanTypes;
    }

    /**
     * Get category by key
     */
    public function getCategory(string $key): ?array
    {
        return $this->categories[$key] ?? null;
    }

    /**
     * Get scan type by key
     */
    public function getScanType(string $key): ?array
    {
        return $this->scanTypes[$key] ?? null;
    }

    /**
     * Check if scan type is valid
     */
    public function isValidScanType(string $scanType): bool
    {
        return array_key_exists($scanType, $this->scanTypes);
    }

    /**
     * Check if category is valid
     */
    public function isValidCategory(string $category): bool
    {
        return array_key_exists($category, $this->categories);
    }
}