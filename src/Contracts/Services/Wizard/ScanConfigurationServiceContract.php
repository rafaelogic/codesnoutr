<?php

namespace Rafaelogic\CodeSnoutr\Contracts\Services\Wizard;

/**
 * Scan Configuration Service Contract
 * 
 * Manages scan types, categories, targets, and configuration validation
 * for scan wizards.
 */
interface ScanConfigurationServiceContract
{
    /**
     * Get all available scan categories
     */
    public function getAllCategories(): array;

    /**
     * Get default categories for a scan type
     */
    public function getDefaultCategories(string $scanType): array;

    /**
     * Validate scan configuration
     */
    public function validateConfiguration(array $config): bool;

    /**
     * Get scan type description
     */
    public function getScanTypeDescription(string $scanType): string;

    /**
     * Update rule categories and count
     */
    public function updateRuleCategories(array $categories): array;

    /**
     * Select all available categories
     */
    public function selectAllCategories(): array;

    /**
     * Deselect all categories
     */
    public function deselectAllCategories(): array;

    /**
     * Get rules count for categories
     */
    public function getRulesCount(array $categories): int;
}