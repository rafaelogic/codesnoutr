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
     * Build the AI prompt for fix generation
     */
    private function buildAutoFixPrompt(Issue $issue, $codeSnippet): string
    {
        $codeContext = is_array($codeSnippet) 
            ? implode("\n", array_map(fn($line) => sprintf("%3d: %s", $line['number'], $line['content']), $codeSnippet))
            : $codeSnippet;

        return "You are an expert PHP code analyzer. Analyze this specific code issue and provide a precise fix.

ISSUE DETAILS:
- Category: {$issue->category}
- Severity: {$issue->severity}
- Rule: {$issue->rule_name}
- Issue: {$issue->description}
- File: {$issue->file_path}
- Line: {$issue->line_number}

CONTEXT CODE:
```php
{$codeContext}
```

CRITICAL: You must respond ONLY with valid JSON. No markdown, no explanations outside the JSON.

Required JSON format:
{
  \"code\": \"exact PHP code to replace/insert (no markdown formatting)\",
  \"explanation\": \"detailed explanation in markdown format for frontend display\",
  \"confidence\": 0.85,
  \"safe_to_automate\": true,
  \"affected_lines\": [" . $issue->line_number . "],
  \"type\": \"replace\"
}

IMPORTANT RULES:
- 'code' field: Pure PHP code only, no markdown formatting, no backticks
- 'explanation' field: Can contain markdown for frontend rendering  
- 'type': Use 'replace' for line replacement, 'insert' for adding code, 'delete' for removal
- Only suggest safe fixes that maintain functionality

LARAVEL/PHP SPECIFIC RULES:
- Class docblocks (/** @package, * Class Name */) should be placed BEFORE the class declaration
- Property/method docblocks should be placed BEFORE the property/method they document  
- Use 'public', 'protected', 'private' visibility for all class properties (Laravel convention)
- Maintain proper PSR-4 namespacing and class structure
- For class-level documentation, provide ONLY the docblock comment, NOT the class declaration
- When adding class docblocks, do NOT include 'class ClassName' - the system will place the docblock correctly

CRITICAL PLACEMENT RULES:
- Class docblocks: provide ONLY the /** ... */ comment block, never include the class declaration line
- Method docblocks: provide ONLY the /** ... */ comment block, never include the method declaration line  
- Property docblocks: provide ONLY the /** ... */ comment block, never include the property declaration line
- Method implementations: provide the complete method code including visibility, function declaration, and body
- The system will automatically place the code in the correct position and handle proper indentation

CODE FORMATTING RULES:
- For method refactoring: provide the complete method implementation from 'public/private/protected' to final '}'
- Maintain proper PSR-12 formatting with consistent indentation
- When replacing methods, include the full method signature and body";
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

            // Store the raw AI response for later parsing by AutoFixService
            return [
                'success' => true,
                'content' => $content,
                'confidence' => 0.75 // Default confidence, will be parsed from JSON later
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