<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CodeSnoutr Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the CodeSnoutr
    | Laravel code scanner package.
    |
    */

    'enabled' => env('CODESNOUTR_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Control how CodeSnoutr routes are loaded and integrated
    |
    */
    'auto_load_routes' => env('CODESNOUTR_AUTO_LOAD_ROUTES', true),
    
    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which middleware should be applied to CodeSnoutr routes
    |
    */
    'middleware' => [
        'web' => true,      // Include web middleware (required for CSRF)
        'auth' => false,    // Require authentication
        'throttle' => '60,1', // Rate limiting
    ],

    /*
    |--------------------------------------------------------------------------
    | Scanning Configuration
    |--------------------------------------------------------------------------
    */
    'scan' => [
        'paths' => [
            'app',
            'config',
            'routes',
            'database/migrations',
            'resources/views',
        ],
        'exclude_paths' => [
            'vendor',
            'node_modules',
            'storage',
            'bootstrap/cache',
            'public',
        ],
        'file_extensions' => [
            'php',
            'blade.php',
        ],
        'max_file_size' => 1024 * 1024, // 1MB
        'timeout' => 300, // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Scanner Rules Configuration
    |--------------------------------------------------------------------------
    */
    'scanners' => [
        'security' => [
            'enabled' => true,
            'rules' => [
                'sql_injection' => true,
                'xss_protection' => true,
                'mass_assignment' => true,
                'csrf_protection' => true,
                'weak_passwords' => true,
            ],
        ],
        'performance' => [
            'enabled' => true,
            'rules' => [
                'n_plus_one_queries' => true,
                'missing_indexes' => true,
                'cache_opportunities' => true,
                'file_operations' => true,
                'memory_usage' => true,
            ],
        ],
        'quality' => [
            'enabled' => true,
            'rules' => [
                'dead_code' => true,
                'code_complexity' => true,
                'naming_conventions' => true,
                'documentation' => true,
                'unused_imports' => true,
            ],
        ],
        'laravel' => [
            'enabled' => true,
            'rules' => [
                'eloquent_best_practices' => true,
                'route_optimization' => true,
                'migration_quality' => true,
                'validation_rules' => true,
                'service_container' => true,
            ],
        ],
        'blade' => [
            'enabled' => true,
            'rules' => [
                'xss_vulnerabilities' => true,
                'csrf_protection' => true,
                'template_complexity' => true,
                'performance_optimization' => true,
                'accessibility_compliance' => true,
                'seo_optimization' => true,
                'best_practices' => true,
                'code_quality' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Severity Levels
    |--------------------------------------------------------------------------
    */
    'severity_levels' => [
        'critical' => [
            'color' => 'red',
            'priority' => 1,
            'threshold' => 0, // Always show critical issues
        ],
        'warning' => [
            'color' => 'yellow',
            'priority' => 2,
            'threshold' => 0,
        ],
        'info' => [
            'color' => 'blue',
            'priority' => 3,
            'threshold' => 10, // Only show if less than 10 info issues
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Integration
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'enabled' => env('CODESNOUTR_AI_ENABLED', false),
        'provider' => env('CODESNOUTR_AI_PROVIDER', 'openai'),
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('CODESNOUTR_AI_MODEL', 'gpt-4'),
            'max_tokens' => 1000,
            'temperature' => 0.1,
        ],
        'auto_fix' => [
            'enabled' => env('CODESNOUTR_AI_AUTO_FIX_ENABLED', false),
            'backup_disk' => env('CODESNOUTR_AUTO_FIX_BACKUP_DISK', 'local'),
            'min_confidence' => env('CODESNOUTR_AUTO_FIX_MIN_CONFIDENCE', 80),
            'safe_mode' => env('CODESNOUTR_AUTO_FIX_SAFE_MODE', true),
            'require_confirmation' => env('CODESNOUTR_AUTO_FIX_REQUIRE_CONFIRMATION', true),
            'create_backup' => true,
            'max_file_size' => 50 * 1024, // 50KB
        ],
        'cost_tracking' => [
            'enabled' => true,
            'monthly_limit' => env('CODESNOUTR_AI_MONTHLY_LIMIT', 50.00), // $50
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'theme' => [
            'default' => env('CODESNOUTR_DEFAULT_THEME', 'system'), // 'light', 'dark', 'system'
            'persist' => true,
        ],
        'pagination' => [
            'per_page' => 25,
            'max_per_page' => 100,
        ],
        'code_preview' => [
            'context_lines' => 5,
            'max_line_length' => 120,
            'syntax_highlighting' => true,
        ],
        'notifications' => [
            'enabled' => true,
            'duration' => 5000, // 5 seconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Debugbar Integration
    |--------------------------------------------------------------------------
    */
    'debugbar' => [
        'enabled' => env('CODESNOUTR_DEBUGBAR', true),
        'show_counter' => true,
        'max_issues_display' => 5,
        'show_only_critical' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Generation
    |--------------------------------------------------------------------------
    */
    'reports' => [
        'formats' => ['html', 'pdf', 'json', 'csv'],
        'include_code' => true,
        'include_suggestions' => true,
        'include_ai_fixes' => true,
        'storage_disk' => 'local',
        'storage_path' => 'codesnoutr/reports',
        'retention_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'memory_limit' => '512M',
        'time_limit' => 300, // 5 minutes
        'chunk_size' => 50, // Files to process in each chunk
        'parallel_processing' => false, // Enable for large codebases
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'scan_complete' => true,
        'critical_issues_found' => true,
        'ai_fix_applied' => true,
        'channels' => ['database'], // 'mail', 'slack', 'database'
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for background job processing
    |
    */
    'queue' => [
        'enabled' => env('CODESNOUTR_QUEUE_ENABLED', true),
        'name' => env('CODESNOUTR_QUEUE_NAME', 'default'),
        'connection' => env('CODESNOUTR_QUEUE_CONNECTION', config('queue.default')),
        'timeout' => env('CODESNOUTR_QUEUE_TIMEOUT', 300),
        'memory' => env('CODESNOUTR_QUEUE_MEMORY', 512),
        'sleep' => env('CODESNOUTR_QUEUE_SLEEP', 3),
        'tries' => env('CODESNOUTR_QUEUE_TRIES', 3),
        'auto_start' => env('CODESNOUTR_QUEUE_AUTO_START', true),
        'monitor_interval' => env('CODESNOUTR_QUEUE_MONITOR_INTERVAL', 30), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'key_prefix' => 'codesnoutr',
        'store' => env('CODESNOUTR_CACHE_STORE', 'file'),
    ],
];
