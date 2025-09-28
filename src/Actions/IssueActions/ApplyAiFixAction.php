<?php

namespace Rafaelogic\CodeSnoutr\Actions\IssueActions;

use Rafaelogic\CodeSnoutr\Contracts\Issues\IssueActionInterface;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Services\AI\AutoFixService;
use Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService;
use Illuminate\Support\Facades\Log;

class ApplyAiFixAction implements IssueActionInterface
{
    protected AutoFixService $autoFixService;

    public function __construct()
    {
        // Initialize services
        $aiService = new AiAssistantService();
        $this->autoFixService = new AutoFixService($aiService);
    }

    /**
     * Check if action can be executed
     */
    public function canExecute(Issue $issue): bool
    {
        return !empty($issue->ai_fix) && 
               !$issue->fixed && 
               $this->autoFixService->isAutoFixEnabled();
    }

    /**
     * Execute the action
     */
    public function execute(Issue $issue): array
    {
        try {
            if (empty($issue->ai_fix)) {
                return [
                    'success' => false,
                    'message' => 'No AI fix available for this issue. Please generate a fix first.',
                    'data' => null
                ];
            }

            // Parse the AI fix data
            $fixData = $this->parseAiFixData($issue->ai_fix);
            if (!$fixData) {
                return [
                    'success' => false,
                    'message' => 'Could not parse AI fix data.',
                    'data' => null
                ];
            }

            // Apply the fix using AutoFixService
            $result = $this->autoFixService->applyFix($issue, $fixData);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'AI fix has been applied successfully! A backup was created.',
                    'data' => [
                        'backup_path' => $result['backup_path'],
                        'preview' => $result['preview']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message'],
                    'data' => null
                ];
            }

        } catch (\Exception $e) {
            Log::error("Failed to apply AI fix for issue {$issue->id}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to apply AI fix: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get action description
     */
    public function getDescription(): string
    {
        return 'Apply AI-generated fix to the code';
    }

    /**
     * Parse AI fix data from stored format
     */
    protected function parseAiFixData(string $aiFixData): ?array
    {
        // Try to decode as JSON first
        $decoded = json_decode($aiFixData, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Fallback: create fix data from raw code
        $code = $this->extractCodeFromText($aiFixData);
        if ($code) {
            return [
                'code' => $code,
                'type' => 'replace',
                'confidence' => 0.7,
                'safe_to_automate' => true,
                'explanation' => 'AI-generated fix',
                'affected_lines' => []
            ];
        }

        return null;
    }

    /**
     * Extract code from text response
     */
    protected function extractCodeFromText(string $text): ?string
    {
        // Try to extract code between ```php and ``` or ``` and ```
        if (preg_match('/```(?:php)?\s*\n(.*?)\n```/s', $text, $matches)) {
            return trim($matches[1]);
        }

        // If no code blocks found, assume the entire response is code
        $lines = explode("\n", $text);
        $codeLines = [];
        
        foreach ($lines as $line) {
            // Skip explanation lines that start with common prefixes
            if (preg_match('/^(Here|The|This|To fix|You should|I suggest)/i', trim($line))) {
                continue;
            }
            $codeLines[] = $line;
        }

        $code = implode("\n", $codeLines);
        return !empty(trim($code)) ? $code : null;
    }
}