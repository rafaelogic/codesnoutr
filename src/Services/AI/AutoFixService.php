<?php

namespace Rafaelogic\CodeSnoutr\Services\AI;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Setting;

class AutoFixService
{
    protected $aiService;
    protected $backupDisk;

    public function __construct(AiAssistantService $aiService)
    {
        $this->aiService = $aiService;
        $this->backupDisk = config('codesnoutr.ai.auto_fix.backup_disk', 'local');
    }

    /**
     * Generate an AI-powered fix for an issue
     */
    public function generateFix(Issue $issue): ?array
    {
        if (!$this->aiService->isAvailable()) {
            return null;
        }

        try {
            $prompt = $this->buildAutoFixPrompt($issue);
            $response = $this->aiService->askAI($prompt, 800);

            if (!$response) {
                return null;
            }

            return $this->parseFixResponse($response);

        } catch (\Exception $e) {
            Log::error('Auto-fix generation failed for issue ' . $issue->id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Apply an automatic fix to a file
     */
    public function applyFix(Issue $issue, array $fixData): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'backup_path' => null,
            'preview' => null
        ];

        try {
            // Validate fix data
            if (!$this->validateFixData($fixData)) {
                $result['message'] = 'Invalid fix data provided';
                return $result;
            }

            // Check if file exists and is writable
            if (!File::exists($issue->file_path) || !File::isWritable($issue->file_path)) {
                $result['message'] = 'File does not exist or is not writable';
                return $result;
            }

            // Create backup
            $backupPath = $this->createBackup($issue->file_path);
            if (!$backupPath) {
                $result['message'] = 'Failed to create backup';
                return $result;
            }
            $result['backup_path'] = $backupPath;

            // Read current file content
            $originalContent = File::get($issue->file_path);
            $lines = explode("\n", $originalContent);

            // Apply the fix
            $modifiedContent = $this->applyFixToContent($lines, $issue, $fixData);

            if ($modifiedContent === null) {
                $result['message'] = 'Failed to apply fix to content';
                return $result;
            }

            // Validate the modified content
            if (!$this->validateModifiedContent($modifiedContent, $issue->file_path)) {
                $result['message'] = 'Modified content failed validation';
                return $result;
            }

            // Write the modified content
            File::put($issue->file_path, $modifiedContent);

            // Mark issue as fixed
            $issue->update([
                'fixed' => true,
                'fixed_at' => now(),
                'fix_method' => 'ai_auto',
                'ai_fix' => $fixData['code'] ?? null,
                'ai_explanation' => $fixData['explanation'] ?? null,
                'ai_confidence' => $fixData['confidence'] ?? 0,
                'metadata' => array_merge($issue->metadata ?? [], [
                    'auto_fix_applied' => true,
                    'backup_path' => $backupPath,
                    'fix_timestamp' => now()->toISOString()
                ])
            ]);

            $result['success'] = true;
            $result['message'] = 'Fix applied successfully';
            $result['preview'] = $this->generatePreview($originalContent, $modifiedContent);

            Log::info('Auto-fix applied successfully', [
                'issue_id' => $issue->id,
                'file_path' => $issue->file_path,
                'backup_path' => $backupPath
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Auto-fix application failed', [
                'issue_id' => $issue->id,
                'error' => $e->getMessage()
            ]);

            $result['message'] = 'Failed to apply fix: ' . $e->getMessage();
            return $result;
        }
    }

    /**
     * Preview what a fix would look like without applying it
     */
    public function previewFix(Issue $issue, array $fixData): ?array
    {
        try {
            if (!File::exists($issue->file_path)) {
                return null;
            }

            $originalContent = File::get($issue->file_path);
            $lines = explode("\n", $originalContent);

            $modifiedContent = $this->applyFixToContent($lines, $issue, $fixData);

            if ($modifiedContent === null) {
                return null;
            }

            return $this->generatePreview($originalContent, $modifiedContent);

        } catch (\Exception $e) {
            Log::error('Fix preview failed', [
                'issue_id' => $issue->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Restore a file from backup
     */
    public function restoreFromBackup(Issue $issue): bool
    {
        try {
            $backupPath = $issue->metadata['backup_path'] ?? null;

            if (!$backupPath || !Storage::disk($this->backupDisk)->exists($backupPath)) {
                return false;
            }

            $backupContent = Storage::disk($this->backupDisk)->get($backupPath);
            File::put($issue->file_path, $backupContent);

            // Update issue status
            $issue->update([
                'fixed' => false,
                'fixed_at' => null,
                'fix_method' => null,
                'metadata' => array_merge($issue->metadata ?? [], [
                    'restored_from_backup' => true,
                    'restore_timestamp' => now()->toISOString()
                ])
            ]);

            Log::info('File restored from backup', [
                'issue_id' => $issue->id,
                'file_path' => $issue->file_path,
                'backup_path' => $backupPath
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Backup restoration failed', [
                'issue_id' => $issue->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Build the prompt for auto-fix generation
     */
    protected function buildAutoFixPrompt(Issue $issue): string
    {
        $fileContent = File::exists($issue->file_path) ? File::get($issue->file_path) : '';
        $contextLines = $this->getContextLines($fileContent, $issue->line_number, 10);

        return "Generate an automatic code fix for this issue:\n\n" .
               "File: {$issue->file_path}\n" .
               "Line: {$issue->line_number}\n" .
               "Category: {$issue->category}\n" .
               "Severity: {$issue->severity}\n" .
               "Issue: {$issue->description}\n" .
               "Rule: {$issue->rule_name}\n\n" .
               "Context Code:\n```php\n{$contextLines}\n```\n\n" .
               "Please provide:\n" .
               "1. The exact code replacement for the problematic line(s)\n" .
               "2. A clear explanation of what was changed and why\n" .
               "3. Your confidence level (0.0-1.0) in this fix\n" .
               "4. Whether this fix can be safely automated\n\n" .
               "Respond with JSON:\n" .
               "{\n" .
               "  \"code\": \"exact replacement code\",\n" .
               "  \"explanation\": \"detailed explanation\",\n" .
               "  \"confidence\": 0.0-1.0,\n" .
               "  \"safe_to_automate\": true/false,\n" .
               "  \"affected_lines\": [line_numbers],\n" .
               "  \"type\": \"replace|insert|delete\"\n" .
               "}\n\n" .
               "Only suggest fixes that are safe, correct, and maintain the original functionality.";
    }

    /**
     * Parse the AI response for fix data
     */
    protected function parseFixResponse($response): ?array
    {
        if (is_string($response)) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $response = $decoded;
            } else {
                return null;
            }
        }

        if (!is_array($response)) {
            return null;
        }

        return [
            'code' => $response['code'] ?? null,
            'explanation' => $response['explanation'] ?? null,
            'confidence' => (float) ($response['confidence'] ?? 0),
            'safe_to_automate' => (bool) ($response['safe_to_automate'] ?? false),
            'affected_lines' => $response['affected_lines'] ?? [],
            'type' => $response['type'] ?? 'replace'
        ];
    }

    /**
     * Validate fix data
     */
    protected function validateFixData(array $fixData): bool
    {
        return isset($fixData['code']) && 
               isset($fixData['type']) && 
               isset($fixData['confidence']) &&
               $fixData['confidence'] >= 0.3 && // Minimum confidence threshold
               in_array($fixData['type'], ['replace', 'insert', 'delete']);
    }

    /**
     * Apply fix to file content
     */
    protected function applyFixToContent(array $lines, Issue $issue, array $fixData): ?string
    {
        try {
            $targetLine = $issue->line_number - 1; // Convert to 0-based index
            $affectedLines = $fixData['affected_lines'] ?? [$issue->line_number];

            switch ($fixData['type']) {
                case 'replace':
                    return $this->applyReplacement($lines, $targetLine, $fixData['code'], $affectedLines);

                case 'insert':
                    return $this->applyInsertion($lines, $targetLine, $fixData['code']);

                case 'delete':
                    return $this->applyDeletion($lines, $affectedLines);

                default:
                    return null;
            }

        } catch (\Exception $e) {
            Log::error('Content modification failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Apply a replacement fix
     */
    protected function applyReplacement(array $lines, int $targetLine, string $newCode, array $affectedLines): string
    {
        // Convert affected lines to 0-based indices
        $affectedIndices = array_map(fn($line) => $line - 1, $affectedLines);
        sort($affectedIndices);

        // Replace the lines
        $modifiedLines = $lines;
        
        // Remove affected lines from the end to the beginning to maintain indices
        $reversedIndices = array_reverse($affectedIndices);
        foreach ($reversedIndices as $index) {
            if (isset($modifiedLines[$index])) {
                unset($modifiedLines[$index]);
            }
        }

        // Insert new code at the first affected line position
        $firstIndex = min($affectedIndices);
        $beforeLines = array_slice($modifiedLines, 0, $firstIndex, true);
        $afterLines = array_slice($modifiedLines, $firstIndex, null, true);

        // Preserve indentation from the original line
        $originalIndent = '';
        if (isset($lines[$targetLine])) {
            preg_match('/^(\s*)/', $lines[$targetLine], $matches);
            $originalIndent = $matches[1] ?? '';
        }

        // Apply indentation to new code if it doesn't already have it
        $newCodeLines = explode("\n", $newCode);
        if (count($newCodeLines) > 1) {
            $newCodeLines = array_map(function($line, $index) use ($originalIndent) {
                if ($index === 0) return $line; // First line keeps original position
                return empty(trim($line)) ? $line : $originalIndent . ltrim($line);
            }, $newCodeLines, array_keys($newCodeLines));
            $newCode = implode("\n", $newCodeLines);
        }

        $result = array_merge($beforeLines, [$newCode], $afterLines);
        return implode("\n", $result);
    }

    /**
     * Apply an insertion fix
     */
    protected function applyInsertion(array $lines, int $targetLine, string $newCode): string
    {
        // Preserve indentation from the target line
        $originalIndent = '';
        if (isset($lines[$targetLine])) {
            preg_match('/^(\s*)/', $lines[$targetLine], $matches);
            $originalIndent = $matches[1] ?? '';
        }

        // Apply indentation to new code
        $indentedCode = $originalIndent . ltrim($newCode);

        $beforeLines = array_slice($lines, 0, $targetLine + 1);
        $afterLines = array_slice($lines, $targetLine + 1);

        $result = array_merge($beforeLines, [$indentedCode], $afterLines);
        return implode("\n", $result);
    }

    /**
     * Apply a deletion fix
     */
    protected function applyDeletion(array $lines, array $affectedLines): string
    {
        $modifiedLines = $lines;
        
        // Convert to 0-based indices and sort in reverse order
        $affectedIndices = array_map(fn($line) => $line - 1, $affectedLines);
        rsort($affectedIndices);

        // Remove lines from end to beginning to maintain indices
        foreach ($affectedIndices as $index) {
            if (isset($modifiedLines[$index])) {
                unset($modifiedLines[$index]);
            }
        }

        return implode("\n", array_values($modifiedLines));
    }

    /**
     * Create a backup of the file
     */
    protected function createBackup(string $filePath): ?string
    {
        try {
            $relativePath = str_replace(base_path() . '/', '', $filePath);
            $backupPath = 'backups/' . date('Y/m/d') . '/' . time() . '_' . str_replace('/', '_', $relativePath);

            $content = File::get($filePath);
            Storage::disk($this->backupDisk)->put($backupPath, $content);

            return $backupPath;

        } catch (\Exception $e) {
            Log::error('Backup creation failed', ['file' => $filePath, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Validate modified content (basic syntax check for PHP files)
     */
    protected function validateModifiedContent(string $content, string $filePath): bool
    {
        // Only validate PHP files
        if (!Str::endsWith($filePath, '.php')) {
            return true;
        }

        // Basic syntax check
        $tempFile = tempnam(sys_get_temp_dir(), 'codesnoutr_validation_');
        file_put_contents($tempFile, $content);

        $output = [];
        $returnCode = 0;
        exec("php -l {$tempFile} 2>&1", $output, $returnCode);

        unlink($tempFile);

        return $returnCode === 0;
    }

    /**
     * Generate a preview of changes
     */
    protected function generatePreview(string $original, string $modified): array
    {
        $originalLines = explode("\n", $original);
        $modifiedLines = explode("\n", $modified);

        $diff = [];
        $maxLines = max(count($originalLines), count($modifiedLines));

        for ($i = 0; $i < $maxLines; $i++) {
            $originalLine = $originalLines[$i] ?? '';
            $modifiedLine = $modifiedLines[$i] ?? '';

            if ($originalLine !== $modifiedLine) {
                $diff[] = [
                    'line' => $i + 1,
                    'type' => 'changed',
                    'original' => $originalLine,
                    'modified' => $modifiedLine
                ];
            }
        }

        return [
            'total_changes' => count($diff),
            'diff' => $diff,
            'original_lines' => count($originalLines),
            'modified_lines' => count($modifiedLines)
        ];
    }

    /**
     * Get context lines around a specific line number
     */
    protected function getContextLines(string $content, int $lineNumber, int $contextSize = 5): string
    {
        $lines = explode("\n", $content);
        $startLine = max(0, $lineNumber - $contextSize - 1);
        $endLine = min(count($lines) - 1, $lineNumber + $contextSize - 1);

        $contextLines = [];
        for ($i = $startLine; $i <= $endLine; $i++) {
            $contextLines[] = ($i + 1) . ': ' . ($lines[$i] ?? '');
        }

        return implode("\n", $contextLines);
    }

    /**
     * Check if auto-fix is enabled
     */
    public function isAutoFixEnabled(): bool
    {
        return Setting::getValue('ai_auto_fix_enabled', false) && $this->aiService->isAvailable();
    }

    /**
     * Get auto-fix statistics
     */
    public function getAutoFixStats(): array
    {
        try {
            $totalAutoFixed = Issue::where('fix_method', 'ai_auto')->count();
            $successRate = 0;

            if ($totalAutoFixed > 0) {
                $successful = Issue::where('fix_method', 'ai_auto')
                    ->whereJsonDoesntContain('metadata->restore_timestamp', null)
                    ->count();
                $successRate = ($totalAutoFixed - $successful) / $totalAutoFixed * 100;
            }

            return [
                'total_auto_fixed' => $totalAutoFixed,
                'success_rate' => round($successRate, 2),
                'average_confidence' => Issue::where('fix_method', 'ai_auto')
                    ->whereNotNull('ai_confidence')
                    ->avg('ai_confidence') ?? 0
            ];

        } catch (\Exception $e) {
            return [
                'total_auto_fixed' => 0,
                'success_rate' => 0,
                'average_confidence' => 0
            ];
        }
    }
}
