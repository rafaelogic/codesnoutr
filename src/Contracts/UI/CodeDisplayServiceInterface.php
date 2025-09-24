<?php

namespace Rafaelogic\CodeSnoutr\Contracts\UI;

interface CodeDisplayServiceInterface
{
    /**
     * Get code snippet with context around a specific line
     */
    public function getCodeSnippet(string $filePath, int $lineNumber, int $contextLines = 3): array;

    /**
     * Get severity information (color, icon, etc.)
     */
    public function getSeverityInfo(string $severity): array;

    /**
     * Get category information (color, icon, etc.)
     */
    public function getCategoryInfo(string $category): array;

    /**
     * Format code for display with syntax highlighting
     */
    public function formatCodeForDisplay(string $code, string $language = 'php'): string;

    /**
     * Get supported file extensions and their languages
     */
    public function getSupportedLanguages(): array;
}