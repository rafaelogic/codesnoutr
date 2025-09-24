<?php

namespace Rafaelogic\CodeSnoutr\Services\AI;

use Rafaelogic\CodeSnoutr\Contracts\AI\SuggestionServiceInterface;
use Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService;
use Illuminate\Support\Facades\Cache;

class SuggestionService implements SuggestionServiceInterface
{
    protected AiAssistantService $aiService;

    public function __construct(AiAssistantService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Get contextual suggestions based on current context
     */
    public function getContextualSuggestions(string $context): array
    {
        $cacheKey = "smart_assistant_suggestions_{$context}";
        
        return Cache::remember($cacheKey, 1800, function () use ($context) {
            return $this->generateContextualSuggestions($context);
        });
    }

    /**
     * Get scan-specific suggestions
     */
    public function getScanSuggestions(): array
    {
        try {
            $suggestions = $this->aiService->getScanSuggestions();
            return $this->formatSuggestions($suggestions);
        } catch (\Exception $e) {
            return $this->getFallbackScanSuggestions();
        }
    }

    /**
     * Get contextual tips for current context
     */
    public function getContextualTips(string $context): array
    {
        $tips = [
            'general' => [
                "Use CodeSnoutr regularly to maintain code quality",
                "Set up automated scans in your CI/CD pipeline",
                "Focus on critical and high-priority issues first",
                "Review suggestions before applying automatic fixes"
            ],
            'security' => [
                "Always validate and sanitize user input",
                "Use Laravel's built-in CSRF protection",
                "Implement proper authentication and authorization",
                "Keep dependencies up to date for security patches",
                "Use prepared statements for database queries"
            ],
            'performance' => [
                "Use database indexing for frequently queried columns",
                "Implement caching for expensive operations",
                "Optimize N+1 queries with eager loading",
                "Consider using queues for heavy background tasks",
                "Monitor and profile your application regularly"
            ],
            'quality' => [
                "Follow PSR coding standards",
                "Write meaningful comments and documentation",
                "Use type hints and return types",
                "Keep functions and classes focused on single responsibilities",
                "Write tests for your code"
            ],
            'laravel' => [
                "Use Eloquent relationships efficiently",
                "Leverage Laravel's built-in validation rules",
                "Implement proper error handling and logging",
                "Use Laravel's caching and session features",
                "Follow Laravel naming conventions"
            ]
        ];

        return $tips[$context] ?? $tips['general'];
    }

    /**
     * Apply a specific suggestion
     */
    public function applySuggestion(int $suggestionIndex): array
    {
        // This would typically interact with the codebase
        // For now, return a placeholder response
        return [
            'success' => true,
            'message' => "Suggestion {$suggestionIndex} has been queued for application",
            'action_required' => true
        ];
    }

    /**
     * Get quick actions for current context
     */
    public function getQuickActions(string $context): array
    {
        $actions = [
            'general' => [
                ['label' => 'Run Full Scan', 'action' => 'scan-codebase', 'icon' => 'magnifying-glass'],
                ['label' => 'View Recent Scans', 'action' => 'view-scans', 'icon' => 'clock'],
                ['label' => 'Generate Report', 'action' => 'generate-report', 'icon' => 'document-text'],
                ['label' => 'Settings', 'action' => 'open-settings', 'icon' => 'cog-6-tooth']
            ],
            'security' => [
                ['label' => 'Security Scan', 'action' => 'scan-security', 'icon' => 'shield-check'],
                ['label' => 'Check Dependencies', 'action' => 'check-deps', 'icon' => 'cube'],
                ['label' => 'Auth Review', 'action' => 'review-auth', 'icon' => 'key'],
                ['label' => 'Security Report', 'action' => 'security-report', 'icon' => 'document-text']
            ],
            'performance' => [
                ['label' => 'Performance Scan', 'action' => 'scan-performance', 'icon' => 'bolt'],
                ['label' => 'DB Query Analysis', 'action' => 'analyze-queries', 'icon' => 'circle-stack'],
                ['label' => 'Cache Check', 'action' => 'check-cache', 'icon' => 'server'],
                ['label' => 'Optimize Images', 'action' => 'optimize-images', 'icon' => 'photo']
            ],
            'quality' => [
                ['label' => 'Code Quality Scan', 'action' => 'scan-quality', 'icon' => 'star'],
                ['label' => 'Style Check', 'action' => 'check-style', 'icon' => 'pencil'],
                ['label' => 'Complexity Analysis', 'action' => 'analyze-complexity', 'icon' => 'chart-bar'],
                ['label' => 'Generate Docs', 'action' => 'generate-docs', 'icon' => 'document']
            ],
            'laravel' => [
                ['label' => 'Laravel Best Practices', 'action' => 'check-laravel', 'icon' => 'check-badge'],
                ['label' => 'Route Analysis', 'action' => 'analyze-routes', 'icon' => 'arrows-right-left'],
                ['label' => 'Migration Review', 'action' => 'review-migrations', 'icon' => 'arrow-up-circle'],
                ['label' => 'Config Check', 'action' => 'check-config', 'icon' => 'cog-8-tooth']
            ]
        ];

        return $actions[$context] ?? $actions['general'];
    }

    /**
     * Get code examples for context
     */
    public function getCodeExamples(string $context): array
    {
        $examples = [
            'security' => [
                [
                    'title' => 'Input Validation',
                    'code' => "// Validate user input\n\$validated = \$request->validate([\n    'email' => 'required|email|max:255',\n    'name' => 'required|string|max:100'\n]);",
                    'description' => 'Always validate user input using Laravel validation rules'
                ],
                [
                    'title' => 'SQL Injection Prevention', 
                    'code' => "// Use parameter binding\n\$users = DB::table('users')\n    ->where('email', \$email)\n    ->get();\n\n// Or Eloquent\n\$user = User::where('email', \$email)->first();",
                    'description' => 'Use parameter binding or Eloquent to prevent SQL injection'
                ]
            ],
            'performance' => [
                [
                    'title' => 'Eager Loading',
                    'code' => "// Bad - N+1 Query\n\$users = User::all();\nforeach (\$users as \$user) {\n    echo \$user->posts->count();\n}\n\n// Good - Eager Loading\n\$users = User::with('posts')->get();",
                    'description' => 'Use eager loading to prevent N+1 query problems'
                ],
                [
                    'title' => 'Caching',
                    'code' => "// Cache expensive operations\n\$value = Cache::remember('stats', 3600, function () {\n    return DB::table('users')->count();\n});",
                    'description' => 'Cache expensive database operations and computations'
                ]
            ],
            'quality' => [
                [
                    'title' => 'Type Hints',
                    'code' => "public function processUser(User \$user): UserResource\n{\n    return new UserResource(\$user);\n}",
                    'description' => 'Use type hints and return types for better code clarity'
                ],
                [
                    'title' => 'Single Responsibility',
                    'code' => "class UserService\n{\n    public function createUser(array \$data): User\n    {\n        // Only handle user creation logic\n        return User::create(\$data);\n    }\n}",
                    'description' => 'Keep classes focused on a single responsibility'
                ]
            ]
        ];

        return $examples[$context] ?? [];
    }

    /**
     * Generate contextual suggestions based on context
     */
    protected function generateContextualSuggestions(string $context): array
    {
        try {
            $contextualHelp = $this->aiService->getContextualHelp($context);
            return $this->formatSuggestions($contextualHelp);
        } catch (\Exception $e) {
            return $this->getFallbackSuggestions($context);
        }
    }

    /**
     * Format suggestions for consistent structure
     */
    protected function formatSuggestions(array $suggestions): array
    {
        $formatted = [];
        
        foreach ($suggestions as $suggestion) {
            if (is_string($suggestion)) {
                $formatted[] = [
                    'title' => $suggestion,
                    'description' => '',
                    'priority' => 'medium',
                    'applicable' => true
                ];
            } elseif (is_array($suggestion)) {
                $formatted[] = array_merge([
                    'title' => $suggestion['title'] ?? 'Suggestion',
                    'description' => $suggestion['description'] ?? '',
                    'priority' => $suggestion['priority'] ?? 'medium',
                    'applicable' => $suggestion['applicable'] ?? true
                ], $suggestion);
            }
        }
        
        return $formatted;
    }

    /**
     * Get fallback scan suggestions when AI is unavailable
     */
    protected function getFallbackScanSuggestions(): array
    {
        return [
            [
                'title' => 'Run Security Scan',
                'description' => 'Check for common security vulnerabilities in your codebase',
                'priority' => 'high',
                'applicable' => true
            ],
            [
                'title' => 'Performance Analysis',
                'description' => 'Identify performance bottlenecks and optimization opportunities',
                'priority' => 'medium',
                'applicable' => true
            ],
            [
                'title' => 'Code Quality Review',
                'description' => 'Review code quality and adherence to best practices',
                'priority' => 'medium',
                'applicable' => true
            ]
        ];
    }

    /**
     * Get fallback suggestions for specific context
     */
    protected function getFallbackSuggestions(string $context): array
    {
        $fallbacks = [
            'security' => [
                ['title' => 'Review authentication implementation', 'priority' => 'high'],
                ['title' => 'Check for XSS vulnerabilities', 'priority' => 'high'],
                ['title' => 'Validate CSRF protection', 'priority' => 'medium']
            ],
            'performance' => [
                ['title' => 'Optimize database queries', 'priority' => 'high'],
                ['title' => 'Implement caching strategy', 'priority' => 'medium'],
                ['title' => 'Review asset optimization', 'priority' => 'low']
            ],
            'quality' => [
                ['title' => 'Follow PSR coding standards', 'priority' => 'medium'],
                ['title' => 'Add type hints and documentation', 'priority' => 'low'],
                ['title' => 'Refactor complex methods', 'priority' => 'medium']
            ]
        ];

        return $this->formatSuggestions($fallbacks[$context] ?? []);
    }
}