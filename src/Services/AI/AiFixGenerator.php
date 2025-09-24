<?php

namespace Rafaelogic\CodeSnoutr\Services\AI;

use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiFixGenerator
{
    /**
     * Generate AI fix for the given issue
     */
    public function generateFix(Issue $issue): array
    {
        try {
            $apiKey = Setting::getOpenAiApiKey();
            
            if (!$this->isValidOpenAiApiKey($apiKey)) {
                return [
                    'success' => false,
                    'error' => 'Invalid OpenAI API key format'
                ];
            }

            // Get the code context
            $codeSnippet = $this->extractCodeSnippet($issue);
            
            if (empty($codeSnippet)) {
                return [
                    'success' => false,
                    'error' => 'Could not extract code context for this issue'
                ];
            }

            // Prepare the AI prompt
            $prompt = $this->buildAutoFixPrompt($issue, $codeSnippet);
            
            // Call OpenAI API
            return $this->callOpenAiApi($apiKey, $prompt);
            
        } catch (\Exception $e) {
            Log::error('AI fix generation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred while generating AI fix: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Build the AI prompt for auto-fix generation
     */
    private function buildAutoFixPrompt(Issue $issue, $codeSnippet): string
    {
        $codeContext = is_array($codeSnippet) 
            ? implode("\n", array_map(fn($line) => sprintf("%3d: %s", $line['number'], $line['content']), $codeSnippet))
            : $codeSnippet;

        return "You are an expert PHP code analyzer. Analyze this specific code issue and provide a precise fix.

**Issue Information:**
- Category: {$issue->category}
- Severity: {$issue->severity}
- Rule: {$issue->rule_name}
- Issue: {$issue->description}
- File: {$issue->file_path}
- Line: {$issue->line_number}

**Code Context:**
```php
{$codeContext}
```

**Instructions:**
1. Analyze the specific issue in the code
2. Provide a clear, concise fix explanation
3. Show the exact code changes needed
4. Include any important considerations or warnings

**Response Format:**
Provide your response in this exact format:

EXPLANATION:
[Clear explanation of the issue and why it needs to be fixed]

FIX:
```php
[Show the corrected code]
```

CONSIDERATIONS:
[Any important notes, warnings, or additional steps needed]";
    }

    /**
     * Extract code snippet around the issue
     */
    private function extractCodeSnippet(Issue $issue)
    {
        try {
            $filePath = $issue->file_path;
            if (!file_exists($filePath)) {
                return null;
            }

            $lines = file($filePath, FILE_IGNORE_NEW_LINES);
            $lineNumber = $issue->line_number;
            
            // Get context lines (5 before and 5 after the issue line)
            $startLine = max(1, $lineNumber - 5);
            $endLine = min(count($lines), $lineNumber + 5);
            
            $snippet = [];
            for ($i = $startLine; $i <= $endLine; $i++) {
                $snippet[] = [
                    'number' => $i,
                    'content' => $lines[$i - 1] ?? ''
                ];
            }

            return $snippet;
        } catch (\Exception $e) {
            Log::error('Failed to extract code snippet: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAiApi(string $apiKey, string $prompt): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert PHP code analyzer focused on providing precise, actionable fixes for code issues.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.3,
            ]);

            if ($response->failed()) {
                $error = $response->json('error.message') ?? 'OpenAI API request failed';
                return [
                    'success' => false,
                    'error' => $error
                ];
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';

            if (empty($content)) {
                return [
                    'success' => false,
                    'error' => 'Empty response from OpenAI API'
                ];
            }

            return [
                'success' => true,
                'content' => $content,
                'confidence' => 0.75 // Default confidence
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'API call failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate OpenAI API key format
     */
    private function isValidOpenAiApiKey(?string $apiKey): bool
    {
        return !empty($apiKey) && 
               strlen($apiKey) > 20 && 
               str_starts_with($apiKey, 'sk-');
    }
}