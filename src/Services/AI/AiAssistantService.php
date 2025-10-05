<?php

namespace Rafaelogic\CodeSnoutr\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Scan;

class AiAssistantService
{
    protected $apiKey;
    protected $model;
    protected $maxTokens;
    protected $enabled;

    public function __construct()
    {
        try {
            $this->apiKey = Setting::getValue('openai_api_key');
            $this->model = Setting::getValue('openai_model', 'gpt-3.5-turbo');
            $this->maxTokens = Setting::getValue('max_tokens', 1000);
            $this->enabled = Setting::getValue('ai_enabled', false);
            
            // Debug logging
            Log::info('AI Service Constructor Debug:', [
                'api_key_exists' => !empty($this->apiKey),
                'api_key_length' => strlen($this->apiKey ?? ''),
                'enabled' => $this->enabled,
                'model' => $this->model,
                'is_available' => $this->enabled && !empty($this->apiKey)
            ]);
            
        } catch (\Exception $e) {
            // Handle database connection or migration issues gracefully
            $this->apiKey = null;
            $this->model = 'gpt-3.5-turbo';
            $this->maxTokens = 1000;
            $this->enabled = false;
            Log::warning('Failed to load AI settings from database: ' . $e->getMessage());
        }
    }

    /**
     * Check if AI assistant is available and configured
     */
    public function isAvailable(): bool
    {
        return $this->enabled && !empty($this->apiKey);
    }

    /**
     * Get smart scan suggestions based on project type and history
     */
    public function getScanSuggestions($projectPath = null): array
    {
        if (!$this->isAvailable()) {
            return $this->getFallbackSuggestions();
        }

        $cacheKey = 'ai_scan_suggestions_' . md5($projectPath ?: base_path());
        
        return Cache::remember($cacheKey, 300, function () use ($projectPath) {
            try {
                $context = $this->analyzeProjectContext($projectPath);
                $prompt = $this->buildScanSuggestionsPrompt($context);
                
                $response = $this->callOpenAI($prompt, 500);
                
                if ($response && isset($response['suggestions'])) {
                    return $response['suggestions'];
                }
            } catch (\Exception $e) {
                Log::warning('AI scan suggestions failed: ' . $e->getMessage());
            }
            
            return $this->getFallbackSuggestions();
        });
    }

    /**
     * Get AI-powered fix suggestions for a specific issue
     */
    public function getFixSuggestion(Issue $issue): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $cacheKey = 'ai_fix_suggestion_' . $issue->id;
        
        return Cache::remember($cacheKey, 600, function () use ($issue) {
            try {
                $prompt = $this->buildFixSuggestionPrompt($issue);
                $response = $this->callOpenAI($prompt, 800);
                
                if ($response) {
                    return [
                        'suggestion' => $response['fix_suggestion'] ?? null,
                        'explanation' => $response['explanation'] ?? null,
                        'code_example' => $response['code_example'] ?? null,
                        'confidence' => $response['confidence'] ?? 0.5,
                        'automated_fix' => $response['automated_fix'] ?? false,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('AI fix suggestion failed for issue ' . $issue->id . ': ' . $e->getMessage());
            }
            
            return null;
        });
    }

    /**
     * Generate a smart scan report summary
     */
    public function generateScanSummary(Scan $scan): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        try {
            $issues = $scan->issues()->get();
            $prompt = $this->buildScanSummaryPrompt($scan, $issues);
            
            $response = $this->callOpenAI($prompt, 600);
            
            if ($response) {
                return [
                    'summary' => $response['summary'] ?? null,
                    'priorities' => $response['priorities'] ?? [],
                    'recommendations' => $response['recommendations'] ?? [],
                    'risk_assessment' => $response['risk_assessment'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('AI scan summary failed for scan ' . $scan->id . ': ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get contextual help and tips based on current activity
     */
    public function getContextualHelp($context, $action = null): array
    {
        if (!$this->isAvailable()) {
            return $this->getFallbackHelp($context);
        }

        $cacheKey = 'ai_help_' . md5($context . '_' . $action);
        
        return Cache::remember($cacheKey, 900, function () use ($context, $action) {
            try {
                $prompt = $this->buildHelpPrompt($context, $action);
                $response = $this->callOpenAI($prompt, 400);
                
                if ($response && isset($response['tips'])) {
                    return $response['tips'];
                }
            } catch (\Exception $e) {
                Log::warning('AI contextual help failed: ' . $e->getMessage());
            }
            
            return $this->getFallbackHelp($context);
        });
    }

    /**
     * Auto-apply fix for an issue (if AI suggests it's safe)
     */
    public function autoApplyFix(Issue $issue): bool
    {
        if (!$this->isAvailable() || !Setting::getValue('ai_auto_fix', false)) {
            return false;
        }

        try {
            $fixSuggestion = $this->getFixSuggestion($issue);
            
            if ($fixSuggestion && 
                $fixSuggestion['automated_fix'] && 
                $fixSuggestion['confidence'] > 0.8) {
                
                // Apply the fix (implementation would depend on issue type)
                return $this->applyAutomatedFix($issue, $fixSuggestion);
            }
        } catch (\Exception $e) {
            Log::error('AI fix failed for issue ' . $issue->id . ': ' . $e->getMessage());
        }
        
        return false;
    }

    /**
     * Test the AI connection
     */
    public function testConnection(): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'AI integration is disabled.',
                'details' => 'Enable AI integration in settings to use AI features.'
            ];
        }

        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'OpenAI API key is not configured.',
                'details' => 'Please provide your OpenAI API key in the AI settings.'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Hello! This is a test connection for CodeSnoutr. Please respond with "Connection successful!"'
                    ]
                ],
                'max_tokens' => 50,
                'temperature' => 0.1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                
                if (stripos($content, 'connection successful') !== false) {
                    return [
                        'success' => true,
                        'message' => 'AI connection is working perfectly!',
                        'details' => 'Model: ' . $this->model . ', Response: ' . trim($content)
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Connection failed.',
                'details' => 'HTTP Status: ' . $response->status() . ', Response: ' . $response->body()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'details' => 'Please check your API key and internet connection.'
            ];
        }
    }

    /**
     * Make a call to OpenAI API
     */
    protected function callOpenAI(string $prompt, int $maxTokens = null): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert code analysis assistant for CodeSnoutr, a PHP/Laravel code scanning tool. Provide helpful, accurate, and actionable advice. Always respond with valid JSON.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $maxTokens ?: $this->maxTokens,
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                
                // Track usage costs if available
                if (isset($data['usage'])) {
                    $this->trackApiUsage($data['usage']);
                }
                
                // Try to parse JSON response
                $jsonData = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $jsonData;
                }
                
                // Fallback to text response
                return ['response' => $content];
            }

            Log::warning('OpenAI API call failed: ' . $response->status() . ' - ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('OpenAI API call exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Track API usage and costs
     */
    protected function trackApiUsage(array $usage): void
    {
        try {
            // Extract token usage from OpenAI response
            $promptTokens = $usage['prompt_tokens'] ?? 0;
            $completionTokens = $usage['completion_tokens'] ?? 0;
            $totalTokens = $usage['total_tokens'] ?? ($promptTokens + $completionTokens);
            
            // Calculate approximate cost based on OpenAI pricing
            // GPT-4: $0.03/1K prompt tokens, $0.06/1K completion tokens
            // GPT-3.5-turbo: $0.0015/1K prompt tokens, $0.002/1K completion tokens
            $cost = 0;
            
            if (str_contains($this->model, 'gpt-4')) {
                $cost = ($promptTokens * 0.03 / 1000) + ($completionTokens * 0.06 / 1000);
            } else {
                // Default to GPT-3.5-turbo pricing
                $cost = ($promptTokens * 0.0015 / 1000) + ($completionTokens * 0.002 / 1000);
            }
            
            // Add the cost to the current usage
            Setting::addAiUsage($cost);
            
            // Log usage for monitoring
            Log::info('AI API Usage Tracked', [
                'model' => $this->model,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'estimated_cost' => $cost,
                'current_usage' => Setting::get('ai_current_usage', 0)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to track AI usage: ' . $e->getMessage());
        }
    }

    /**
     * Get current AI usage statistics
     */
    public function getUsageStats(): array
    {
        return [
            'current_usage' => Setting::get('ai_current_usage', 0.00),
            'monthly_limit' => Setting::get('ai_monthly_limit', 50.00),
            'percentage_used' => $this->getUsagePercentage(),
            'enabled' => $this->enabled,
            'available' => $this->isAvailable()
        ];
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentage(): float
    {
        $currentUsage = (float) Setting::get('ai_current_usage', 0.00);
        $monthlyLimit = (float) Setting::get('ai_monthly_limit', 50.00);
        
        if ($monthlyLimit <= 0) {
            return 0;
        }
        
        return min(100, round(($currentUsage / $monthlyLimit) * 100, 2));
    }

    /**
     * Public method to make OpenAI calls (for components)
     */
    public function askAI(string $prompt, int $maxTokens = null): ?array
    {
        return $this->callOpenAI($prompt, $maxTokens);
    }

    /**
     * Analyze project context for better suggestions
     */
    protected function analyzeProjectContext($projectPath = null): array
    {
        $path = $projectPath ?: base_path();
        $context = [
            'project_type' => 'php',
            'framework' => 'unknown',
            'files_count' => 0,
            'recent_scans' => 0,
            'common_issues' => [],
        ];

        try {
            // Detect Laravel
            if (file_exists($path . '/artisan') && file_exists($path . '/composer.json')) {
                $context['framework'] = 'laravel';
                
                $composer = json_decode(file_get_contents($path . '/composer.json'), true);
                if (isset($composer['require']['laravel/framework'])) {
                    $context['laravel_version'] = $composer['require']['laravel/framework'];
                }
            }

            // Count PHP files
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            $phpFiles = 0;
            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $phpFiles++;
                }
            }
            $context['files_count'] = $phpFiles;

            // Get recent scan stats - with error handling
            try {
                $recentScans = Scan::where('created_at', '>=', now()->subDays(30))->count();
                $context['recent_scans'] = $recentScans;
            } catch (\Exception $e) {
                Log::warning('Failed to get recent scans: ' . $e->getMessage());
                $context['recent_scans'] = 0;
            }

            // Get common issue types - using correct column name
            try {
                $commonIssues = Issue::select('category')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->groupBy('category')
                    ->orderByRaw('COUNT(*) DESC')
                    ->limit(5)
                    ->pluck('category')
                    ->toArray();
                $context['common_issues'] = $commonIssues;
            } catch (\Exception $e) {
                Log::warning('Failed to get common issues: ' . $e->getMessage());
                $context['common_issues'] = ['security', 'performance', 'quality']; // fallback
            }

        } catch (\Exception $e) {
            Log::warning('Failed to analyze project context: ' . $e->getMessage());
        }

        return $context;
    }

    /**
     * Build prompt for scan suggestions
     */
    protected function buildScanSuggestionsPrompt(array $context): string
    {
        return "Based on this project context, provide scan suggestions:\n\n" .
               "Project Type: {$context['framework']}\n" .
               "Files Count: {$context['files_count']}\n" .
               "Recent Scans: {$context['recent_scans']}\n" .
               "Common Issues: " . implode(', ', $context['common_issues']) . "\n\n" .
               "Provide 3-5 specific scan recommendations with rationale. " .
               "Respond with JSON: {\"suggestions\": [{\"title\": \"\", \"description\": \"\", \"categories\": [], \"priority\": \"high|medium|low\"}]}";
    }

    /**
     * Build prompt for fix suggestions
     */
    protected function buildFixSuggestionPrompt(Issue $issue): string
    {
        return "Provide a fix suggestion for this code issue:\n\n" .
               "Category: {$issue->category}\n" .
               "Severity: {$issue->severity}\n" .
               "Description: {$issue->description}\n" .
               "File: {$issue->file_path}\n" .
               "Line: {$issue->line_number}\n" .
               "Code Context: {$issue->context}\n\n" .
               "Provide a detailed fix suggestion. " .
               "Respond with JSON: {\"fix_suggestion\": \"\", \"explanation\": \"\", \"code_example\": \"\", \"confidence\": 0.0-1.0, \"automated_fix\": true/false}";
    }

    /**
     * Build prompt for scan summary
     */
    protected function buildScanSummaryPrompt(Scan $scan, $issues): string
    {
        $issueCategories = $issues->groupBy('category')->map->count();
        $severityCounts = $issues->groupBy('severity')->map->count();
        
        return "Generate a comprehensive scan summary:\n\n" .
               "Scan Type: {$scan->type}\n" .
               "Total Issues: {$issues->count()}\n" .
               "Issue Categories: " . $issueCategories->toJson() . "\n" .
               "Severity Distribution: " . $severityCounts->toJson() . "\n" .
               "Categories Scanned: " . implode(', ', $scan->categories ?? []) . "\n\n" .
               "Provide analysis and recommendations. " .
               "Respond with JSON: {\"summary\": \"\", \"priorities\": [], \"recommendations\": [], \"risk_assessment\": \"\"}";
    }

    /**
     * Build prompt for contextual help
     */
    protected function buildHelpPrompt(string $context, ?string $action): string
    {
        return "Provide contextual help for CodeSnoutr users:\n\n" .
               "Context: {$context}\n" .
               "Current Action: " . ($action ?: 'general usage') . "\n\n" .
               "Provide 3-5 helpful tips and best practices. " .
               "Respond with JSON: {\"tips\": [{\"title\": \"\", \"description\": \"\", \"type\": \"info|warning|success\"}]}";
    }

    /**
     * Get fallback suggestions when AI is not available
     */
    protected function getFallbackSuggestions(): array
    {
        return [
            [
                'title' => 'Security Scan',
                'description' => 'Scan for common security vulnerabilities like SQL injection and XSS',
                'categories' => ['security'],
                'priority' => 'high'
            ],
            [
                'title' => 'Performance Review',
                'description' => 'Check for N+1 queries and performance bottlenecks',
                'categories' => ['performance'],
                'priority' => 'medium'
            ],
            [
                'title' => 'Code Quality Check',
                'description' => 'Review code standards and maintainability',
                'categories' => ['quality'],
                'priority' => 'medium'
            ]
        ];
    }

    /**
     * Get fallback help when AI is not available
     */
    protected function getFallbackHelp(string $context): array
    {
        $helpTips = [
            'scan_wizard' => [
                [
                    'title' => 'Choose the Right Scan Type',
                    'description' => 'Use file scans for quick checks, directory scans for modules, and codebase scans for comprehensive analysis.',
                    'type' => 'info'
                ],
                [
                    'title' => 'Select Relevant Categories',
                    'description' => 'Focus on security and performance for production code, add quality checks for maintenance.',
                    'type' => 'info'
                ]
            ],
            'settings' => [
                [
                    'title' => 'Configure Scan Timeout',
                    'description' => 'Increase timeout for large codebases to prevent incomplete scans.',
                    'type' => 'warning'
                ],
                [
                    'title' => 'Set Up Ignore Patterns',
                    'description' => 'Exclude vendor directories and compiled assets to focus on your code.',
                    'type' => 'info'
                ]
            ]
        ];

        return $helpTips[$context] ?? $helpTips['scan_wizard'];
    }

    /**
     * Apply automated fix (placeholder implementation)
     */
    protected function applyAutomatedFix(Issue $issue, array $fixSuggestion): bool
    {
        // This would contain the actual implementation for applying fixes
        // For now, we'll just mark it as a placeholder
        Log::info('Automated fix would be applied for issue ' . $issue->id);
        return false; // Disabled for safety
    }
}
