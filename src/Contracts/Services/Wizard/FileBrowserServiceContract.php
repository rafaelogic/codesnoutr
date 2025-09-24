<?php

namespace Rafaelogic\CodeSnoutr\Contracts\Services\Wizard;

/**
 * File Browser Service Contract
 * 
 * Handles file system navigation, directory browsing,
 * and file selection functionality.
 */
interface FileBrowserServiceContract
{
    /**
     * Load items for the current directory
     */
    public function loadDirectoryItems(string $path): array;

    /**
     * Navigate to a specific path
     */
    public function navigateTo(string $path): bool;

    /**
     * Navigate up to parent directory
     */
    public function navigateUp(string $currentPath): string;

    /**
     * Check if a path is valid and accessible
     */
    public function isValidPath(string $path): bool;

    /**
     * Get allowed file extensions for scanning
     */
    public function getAllowedExtensions(): array;

    /**
     * Filter items by type and visibility
     */
    public function filterItems(array $items): array;

    /**
     * Sort directory items
     */
    public function sortItems(array $items): array;
}