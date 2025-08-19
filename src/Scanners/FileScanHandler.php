<?php

namespace Rafaelogic\CodeSnoutr\Scanners;

use Illuminate\Foundation\Application;

class FileScanHandler extends AbstractScanner
{
    /**
     * Scan a single file with progress tracking
     */
    public function scanWithProgress(?string $path, array $categories, array $options = [], ?callable $progressCallback = null): array
    {
        if ($progressCallback) {
            $progressCallback(10, "Preparing to scan file: " . basename($path), [
                'current_file' => $path,
                'total_files' => 1
            ]);
        }
        
        if ($progressCallback) {
            $progressCallback(30, "Scanning file: " . basename($path), [
                'current_file' => $path
            ]);
        }
        
        $result = $this->scan($path, $categories, $options);
        
        if ($progressCallback) {
            $issueCount = count($result['issues'] ?? []);
            $progressCallback(90, "Found {$issueCount} issues in " . basename($path), [
                'issues_found' => $issueCount,
                'current_file' => $path
            ]);
        }
        
        return $result;
    }

    /**
     * Scan a single file
     */
    public function scan(?string $path, array $categories, array $options = []): array
    {
        if (!$path || !file_exists($path)) {
            throw new \InvalidArgumentException("File path is required and must exist: {$path}");
        }

        if ($this->shouldSkipFile($path)) {
            throw new \InvalidArgumentException("File is excluded from scanning: {$path}");
        }

        // Validate and filter categories
        $categories = $this->validateCategories($categories);
        if (empty($categories)) {
            throw new \InvalidArgumentException("At least one valid category must be specified");
        }

        $this->updateStats('scan_type', 'file');
        $this->updateStats('files_scanned', 1);
        $this->updateStats('started_at', now());

        try {
            // Scan the file
            $result = $this->scanFile($path, $categories);
            
            // Extract issues
            $issues = $result['issues'];
            
            // Update statistics
            $this->updateStats('total_issues', count($issues));
            $this->updateStats('issues_by_severity', $this->groupIssuesBySeverity($issues));
            $this->updateStats('issues_by_category', $this->groupIssuesByCategory($issues));
            $this->updateStats('file_size', $result['file_size']);
            $this->updateStats('line_count', $result['line_count']);
            $this->updateStats('completed_at', now());

            return [
                'issues' => $issues,
                'stats' => $this->getStats(),
                'paths_scanned' => [$path],
                'summary' => [
                    'scan_type' => 'file',
                    'target_path' => $path,
                    'categories_scanned' => $categories,
                    'total_files' => 1,
                    'total_issues' => count($issues),
                    'file_info' => [
                        'size_bytes' => $result['file_size'],
                        'line_count' => $result['line_count'],
                        'file_type' => $this->getFileType($path),
                    ],
                ],
            ];

        } catch (\Exception $e) {
            $this->updateStats('error', $e->getMessage());
            $this->updateStats('completed_at', now());
            
            throw new \RuntimeException("Failed to scan file {$path}: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Group issues by severity
     */
    protected function groupIssuesBySeverity(array $issues): array
    {
        $grouped = ['critical' => 0, 'warning' => 0, 'info' => 0];
        
        foreach ($issues as $issue) {
            $severity = $issue['severity'] ?? 'info';
            if (isset($grouped[$severity])) {
                $grouped[$severity]++;
            }
        }
        
        return $grouped;
    }

    /**
     * Group issues by category
     */
    protected function groupIssuesByCategory(array $issues): array
    {
        $grouped = [];
        
        foreach ($issues as $issue) {
            $category = $issue['category'] ?? 'unknown';
            $grouped[$category] = ($grouped[$category] ?? 0) + 1;
        }
        
        return $grouped;
    }

    /**
     * Get file type based on extension
     */
    protected function getFileType(string $path): string
    {
        if ($this->isBladeFile($path)) {
            return 'blade_template';
        } elseif ($this->isPhpFile($path)) {
            return 'php';
        } else {
            return 'unknown';
        }
    }
}
