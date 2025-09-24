<?php

namespace Rafaelogic\CodeSnoutr\Services\Wizard;

use Illuminate\Support\Facades\File;
use Rafaelogic\CodeSnoutr\Contracts\Services\Wizard\FileBrowserServiceContract;

/**
 * File Browser Service
 * 
 * Handles file system navigation, directory browsing, and file selection.
 */
class FileBrowserService implements FileBrowserServiceContract
{
    protected array $allowedExtensions = ['php', 'js', 'vue', 'blade.php'];

    /**
     * Load items for the current directory
     */
    public function loadDirectoryItems(string $path): array
    {
        if (!$this->isValidPath($path)) {
            return [];
        }

        try {
            $items = [];
            $files = File::files($path);
            $directories = File::directories($path);

            // Add directories
            foreach ($directories as $directory) {
                $name = basename($directory);
                if (!str_starts_with($name, '.')) {
                    $items[] = [
                        'name' => $name,
                        'path' => $directory,
                        'type' => 'directory',
                        'size' => null,
                        'modified' => File::lastModified($directory)
                    ];
                }
            }

            // Add files
            foreach ($files as $file) {
                $name = basename($file);
                $extension = File::extension($file);
                
                if (!str_starts_with($name, '.') && in_array($extension, $this->allowedExtensions)) {
                    $items[] = [
                        'name' => $name,
                        'path' => $file,
                        'type' => 'file',
                        'size' => File::size($file),
                        'modified' => File::lastModified($file)
                    ];
                }
            }

            return $this->sortItems($items);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Navigate to a specific path
     */
    public function navigateTo(string $path): bool
    {
        return $this->isValidPath($path);
    }

    /**
     * Navigate up to parent directory
     */
    public function navigateUp(string $currentPath): string
    {
        $parentPath = dirname($currentPath);
        
        if ($parentPath !== $currentPath && $this->isValidPath($parentPath)) {
            return $parentPath;
        }
        
        return $currentPath;
    }

    /**
     * Check if a path is valid and accessible
     */
    public function isValidPath(string $path): bool
    {
        return File::exists($path) && File::isDirectory($path);
    }

    /**
     * Get allowed file extensions for scanning
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    /**
     * Filter items by type and visibility
     */
    public function filterItems(array $items): array
    {
        return array_filter($items, function ($item) {
            // Skip hidden files/directories
            if (str_starts_with($item['name'], '.')) {
                return false;
            }
            
            // For files, check allowed extensions
            if ($item['type'] === 'file') {
                $extension = pathinfo($item['name'], PATHINFO_EXTENSION);
                return in_array($extension, $this->allowedExtensions);
            }
            
            return true;
        });
    }

    /**
     * Sort directory items
     */
    public function sortItems(array $items): array
    {
        usort($items, function ($a, $b) {
            // Directories first
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'directory' ? -1 : 1;
            }
            
            // Then alphabetically
            return strcasecmp($a['name'], $b['name']);
        });

        return $items;
    }

    /**
     * Set allowed file extensions
     */
    public function setAllowedExtensions(array $extensions): void
    {
        $this->allowedExtensions = $extensions;
    }

    /**
     * Format file size for display
     */
    public function formatFileSize(?int $bytes): string
    {
        if ($bytes === null) {
            return '';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}