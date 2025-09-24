<?php

namespace Rafaelogic\CodeSnoutr\Services\UI;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CodeDisplayService
{
    /**
     * Get code snippet around a specific line
     */
    public function getCodeSnippet(string $filePath, int $lineNumber, int $contextLines = 2): ?array
    {
        try {
            if (!File::exists($filePath)) {
                return null;
            }

            $lines = File::lines($filePath);
            $totalLines = $lines->count();
            
            if ($lineNumber < 1 || $lineNumber > $totalLines) {
                return null;
            }

            $startLine = max(1, $lineNumber - $contextLines);
            $endLine = min($totalLines, $lineNumber + $contextLines);
            
            $snippet = [];
            for ($i = $startLine; $i <= $endLine; $i++) {
                $snippet[] = [
                    'number' => $i,
                    'content' => $lines->get($i - 1) ?? '',
                    'is_target' => $i === $lineNumber,
                    'highlighted' => $i === $lineNumber
                ];
            }

            return [
                'lines' => $snippet,
                'target_line' => $lineNumber,
                'start_line' => $startLine,
                'end_line' => $endLine,
                'file_path' => $filePath
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get code snippet: ' . $e->getMessage(), [
                'file_path' => $filePath,
                'line_number' => $lineNumber
            ]);
            return null;
        }
    }

    /**
     * Get file information
     */
    public function getFileInfo(string $filePath): ?array
    {
        try {
            if (!File::exists($filePath)) {
                return null;
            }

            $fileInfo = [
                'path' => $filePath,
                'name' => basename($filePath),
                'directory' => dirname($filePath),
                'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
                'size' => File::size($filePath),
                'last_modified' => File::lastModified($filePath),
                'is_readable' => File::isReadable($filePath),
                'mime_type' => File::mimeType($filePath)
            ];

            // Add line count for text files
            if ($this->isTextFile($filePath)) {
                $fileInfo['line_count'] = File::lines($filePath)->count();
            }

            return $fileInfo;

        } catch (\Exception $e) {
            Log::error('Failed to get file info: ' . $e->getMessage(), [
                'file_path' => $filePath
            ]);
            return null;
        }
    }

    /**
     * Get severity display information
     */
    public function getSeverityInfo(string $severity): array
    {
        $severityMap = [
            'critical' => [
                'name' => 'Critical',
                'color' => 'red',
                'icon' => 'exclamation-circle',
                'priority' => 5,
                'description' => 'Critical issues that need immediate attention'
            ],
            'high' => [
                'name' => 'High',
                'color' => 'orange',
                'icon' => 'exclamation-triangle',
                'priority' => 4,
                'description' => 'High priority issues that should be addressed soon'
            ],
            'medium' => [
                'name' => 'Medium',
                'color' => 'yellow',
                'icon' => 'exclamation',
                'priority' => 3,
                'description' => 'Medium priority issues for improvement'
            ],
            'low' => [
                'name' => 'Low',
                'color' => 'blue',
                'icon' => 'info-circle',
                'priority' => 2,
                'description' => 'Low priority suggestions and improvements'
            ],
            'info' => [
                'name' => 'Info',
                'color' => 'gray',
                'icon' => 'info',
                'priority' => 1,
                'description' => 'Informational notices'
            ]
        ];

        return $severityMap[$severity] ?? $severityMap['info'];
    }

    /**
     * Get category display information
     */
    public function getCategoryInfo(string $category): array
    {
        $categoryMap = [
            'security' => [
                'name' => 'Security',
                'color' => 'red',
                'icon' => 'shield-exclamation',
                'description' => 'Security vulnerabilities and risks'
            ],
            'performance' => [
                'name' => 'Performance',
                'color' => 'orange',
                'icon' => 'lightning-bolt',
                'description' => 'Performance optimizations and improvements'
            ],
            'quality' => [
                'name' => 'Code Quality',
                'color' => 'blue',
                'icon' => 'code',
                'description' => 'Code quality and maintainability issues'
            ],
            'best_practices' => [
                'name' => 'Best Practices',
                'color' => 'green',
                'icon' => 'check-circle',
                'description' => 'Laravel and PHP best practices'
            ],
            'blade' => [
                'name' => 'Blade Templates',
                'color' => 'purple',
                'icon' => 'template',
                'description' => 'Blade template issues and optimizations'
            ],
            'accessibility' => [
                'name' => 'Accessibility',
                'color' => 'indigo',
                'icon' => 'universal-access',
                'description' => 'Web accessibility improvements'
            ]
        ];

        return $categoryMap[$category] ?? [
            'name' => ucfirst(str_replace('_', ' ', $category)),
            'color' => 'gray',
            'icon' => 'tag',
            'description' => 'General code issues'
        ];
    }

    /**
     * Format file size for display
     */
    public function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Check if file is a text file
     */
    protected function isTextFile(string $filePath): bool
    {
        $textExtensions = [
            'php', 'js', 'css', 'html', 'blade.php', 'vue', 'ts', 'jsx', 'tsx',
            'json', 'xml', 'yaml', 'yml', 'md', 'txt', 'env', 'sql', 'sh'
        ];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Handle .blade.php files
        if (str_ends_with(strtolower($filePath), '.blade.php')) {
            return true;
        }

        return in_array($extension, $textExtensions);
    }

    /**
     * Get syntax highlighting language for file
     */
    public function getSyntaxLanguage(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Handle .blade.php files
        if (str_ends_with(strtolower($filePath), '.blade.php')) {
            return 'php';
        }

        $languageMap = [
            'php' => 'php',
            'js' => 'javascript',
            'ts' => 'typescript',
            'jsx' => 'jsx',
            'tsx' => 'tsx',
            'vue' => 'vue',
            'css' => 'css',
            'scss' => 'scss',
            'sass' => 'sass',
            'less' => 'less',
            'html' => 'html',
            'xml' => 'xml',
            'json' => 'json',
            'yaml' => 'yaml',
            'yml' => 'yaml',
            'md' => 'markdown',
            'sql' => 'sql',
            'sh' => 'bash'
        ];

        return $languageMap[$extension] ?? 'text';
    }

    /**
     * Truncate long text for display
     */
    public function truncateText(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - strlen($suffix)) . $suffix;
    }
}