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
            // Auto-generate fix if it doesn't exist
            if (empty($issue->ai_fix)) {
                Log::info("No AI fix found for issue {$issue->id}, auto-generating...");
                
                $generateResult = $this->autoGenerateFix($issue);
                if (!$generateResult['success']) {
                    return $generateResult;
                }
                
                // Refresh the issue to get the newly generated fix
                $issue->refresh();
            }

            // Parse the AI fix data
            $fixData = $this->parseAiFixData($issue->ai_fix);
            if (!$fixData) {
                return [
                    'success' => false,
                    'message' => 'Could not parse AI fix data. Please try again.',
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
        // Remove leading markdown formatting (**, *, etc.)
        $text = preg_replace('/^\*+\s*/', '', $text);
        
        // Try to extract code between ```php and ``` or ``` and ```
        if (preg_match('/```(?:php)?\s*\n(.*?)\n```/s', $text, $matches)) {
            return trim($matches[1]);
        }
        
        // Look for code blocks without language specifier
        if (preg_match('/```\s*(.*?)\s*```/s', $text, $matches)) {
            $code = trim($matches[1]);
            // Check if it looks like PHP code
            if (str_starts_with($code, '<?php') || str_contains($code, 'Route::') || str_contains($code, '->')) {
                return $code;
            }
        }
        
        // Look for inline code after "php" keyword
        if (preg_match('/\bphp\s*(.*?)(?:\n|$)/s', $text, $matches)) {
            $code = trim($matches[1]);
            // Clean up common markdown artifacts
            $code = preg_replace('/^[\*\-\s]*/', '', $code);
            if (!empty($code)) {
                return $code;
            }
        }

        // If no code blocks found, try to extract PHP-looking content
        $lines = explode("\n", $text);
        $codeLines = [];
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            // Skip empty lines and markdown formatting
            if (empty($trimmed) || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '**') || str_starts_with($trimmed, '*')) {
                continue;
            }
            
            // Skip explanation lines that start with common prefixes
            if (preg_match('/^(Here|The|This|To fix|You should|I suggest|To address)/i', $trimmed)) {
                continue;
            }
            
            // Look for PHP-like patterns
            if (str_contains($trimmed, '<?php') || 
                str_contains($trimmed, 'Route::') || 
                str_contains($trimmed, '->') ||
                str_contains($trimmed, 'function') ||
                str_contains($trimmed, 'class ') ||
                str_contains($trimmed, 'public ') ||
                str_contains($trimmed, 'private ') ||
                str_contains($trimmed, 'protected ')) {
                $codeLines[] = $trimmed;
            }
        }

        $code = implode("\n", $codeLines);
        return !empty(trim($code)) ? $code : null;
    }

    /**
     * Auto-generate AI fix for the issue
     */
    protected function autoGenerateFix(Issue $issue): array
    {
        try {
            // Use the same service that GenerateAiFixAction uses
            $aiFixGenerator = app(\Rafaelogic\CodeSnoutr\Services\AI\AiFixGenerator::class);
            
            $result = $aiFixGenerator->generateFix($issue);
            
            if ($result['success']) {
                // Update the issue with the generated fix
                $issue->update([
                    'ai_fix' => $result['data']['ai_fix'],
                    'ai_confidence' => $result['data']['confidence'] ?? null
                ]);
                
                Log::info("Successfully auto-generated AI fix for issue {$issue->id}");
                
                return [
                    'success' => true,
                    'message' => 'AI fix generated and ready to apply',
                    'data' => $result['data']
                ];
            } else {
                Log::warning("Failed to auto-generate AI fix for issue {$issue->id}: {$result['message']}");
                
                return [
                    'success' => false,
                    'message' => 'Failed to generate AI fix: ' . $result['message'],
                    'data' => null
                ];
            }
            
        } catch (\Exception $e) {
            Log::error("Exception during auto-generation for issue {$issue->id}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to auto-generate AI fix: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}