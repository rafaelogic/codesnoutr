<?php

/**
 * AI Training Data for CodeSnoutr Auto Fix
 * 
 * This file contains training examples for different issue types to help
 * the AI generate better fixes. Each example shows WRONG and RIGHT approaches.
 */

return [
    
    /**
     * QUALITY ISSUES - Long Lines
     */
    'quality.long_line' => [
        'description' => 'Break long lines while preserving exact functionality',
        'examples' => [
            [
                'issue_line' => 'return $this->nearbyPlaces()->where(\'locale\', $locale)->orderBy(\'distance\', \'asc\')->with([\'category\', \'reviews\'])->get();',
                'wrong_fix' => '$this->nearbyPlaces()->with(\'locale\')->get();', // Missing return, changed where() to with()
                'correct_fix' => "return \$this->nearbyPlaces()\n    ->where('locale', \$locale)\n    ->orderBy('distance', 'asc')\n    ->with(['category', 'reviews'])\n    ->get();",
                'validation_rules' => [
                    'must_contain_return' => true,
                    'must_preserve_method_calls' => ['where', 'orderBy', 'with', 'get'],
                    'must_not_change_logic' => true,
                    'max_line_length' => 120
                ]
            ],
            [
                'issue_line' => 'return $this->orders()->whereHas(\'items\', function($q) { $q->where(\'status\', \'completed\'); })->whereBetween(\'created_at\', [now()->startOfMonth(), now()->endOfMonth()])->with([\'customer\', \'items.product\'])->orderBy(\'total\', \'desc\')->get();',
                'wrong_fix' => 'return $this->orders()->has(\'items\')->between(\'created_at\', [now()->startOfMonth(), now()->endOfMonth()])->load([\'customer\', \'items.product\'])->orderBy(\'total\', \'desc\')->get();',
                'correct_fix' => "return \$this->orders()\n    ->whereHas('items', function(\$q) {\n        \$q->where('status', 'completed');\n    })\n    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])\n    ->with(['customer', 'items.product'])\n    ->orderBy('total', 'desc')\n    ->get();",
                'validation_rules' => [
                    'must_preserve_whereHas' => true,
                    'must_preserve_closure' => true,
                    'must_not_change_with_to_load' => true
                ]
            ]
        ]
    ],

    /**
     * DOCUMENTATION ISSUES - Missing Docblocks
     */
    'quality.missing_method_docblock' => [
        'description' => 'Add proper docblocks before methods using insert type',
        'examples' => [
            [
                'issue_line' => 'public function getFullNameAttribute()',
                'method_context' => "public function getFullNameAttribute()\n{\n    return \$this->name . ' (' . \$this->email . ')';\n}",
                'correct_fix' => "/**\n * Get the user's full name with email\n *\n * @return string\n */",
                'fix_type' => 'insert',
                'validation_rules' => [
                    'must_be_insert_type' => true,
                    'must_contain_description' => true,
                    'must_have_return_annotation' => true,
                    'must_start_with_docblock' => '/**'
                ]
            ],
            [
                'issue_line' => 'public function scopeActive($query)',
                'method_context' => "public function scopeActive(\$query)\n{\n    return \$query->where('status', 'active');\n}",
                'correct_fix' => "/**\n * Scope a query to only include active records\n *\n * @param \\Illuminate\\Database\\Eloquent\\Builder \$query\n * @return \\Illuminate\\Database\\Eloquent\\Builder\n */",
                'fix_type' => 'insert'
            ]
        ]
    ],

    /**
     * SECURITY ISSUES - SQL Injection
     */
    'security.sql_injection' => [
        'description' => 'Replace raw SQL concatenation with parameter binding',
        'examples' => [
            [
                'issue_line' => 'DB::select("SELECT * FROM posts WHERE title LIKE \'%{$query}%\'");',
                'wrong_fix' => 'DB::select("SELECT * FROM posts WHERE title LIKE \'%$query%\'");', // Still vulnerable
                'correct_fix' => 'DB::select("SELECT * FROM posts WHERE title LIKE ?", [\'%\'.$query.\'%\']);',
                'validation_rules' => [
                    'must_use_parameter_binding' => true,
                    'must_not_concatenate_user_input' => true,
                    'must_contain_question_mark' => true
                ]
            ],
            [
                'issue_line' => '$users = DB::select("SELECT * FROM users WHERE name = \'" . $request->name . "\'");',
                'correct_fix' => '$users = DB::select("SELECT * FROM users WHERE name = ?", [$request->name]);'
            ]
        ]
    ],

    /**
     * LARAVEL ISSUES - Missing Validation
     */
    'laravel.missing_validation' => [
        'description' => 'Add request validation before database operations',
        'examples' => [
            [
                'issue_line' => 'User::create([',
                'context_before' => "public function store(Request \$request)\n{\n    \$user = User::create([",
                'correct_fix' => "\$request->validate([\n    'name' => 'required|string|max:255',\n    'email' => 'required|email|unique:users',\n    'password' => 'required|string|min:8'\n]);\n\n\$user = User::create([",
                'fix_type' => 'insert',
                'validation_rules' => [
                    'must_call_validate' => true,
                    'must_include_required_rules' => true,
                    'must_be_before_create' => true
                ]
            ]
        ]
    ],

    /**
     * PERFORMANCE ISSUES - N+1 Queries
     */
    'performance.n_plus_one' => [
        'description' => 'Add eager loading to prevent N+1 query problems',
        'examples' => [
            [
                'issue_line' => '$posts = Post::all();',
                'context_after' => "foreach (\$posts as \$post) {\n    echo \$post->user->name;\n    echo \$post->category->title;\n}",
                'correct_fix' => '$posts = Post::with([\'user\', \'category\'])->get();',
                'validation_rules' => [
                    'must_include_with_clause' => true,
                    'must_identify_relationships' => ['user', 'category'],
                    'should_change_all_to_get' => true
                ]
            ]
        ]
    ],

    /**
     * QUALITY ISSUES - Trailing Whitespace
     */
    'quality.trailing_whitespace' => [
        'description' => 'Remove trailing whitespace characters',
        'examples' => [
            [
                'issue_line' => '    public function test()   ',
                'correct_fix' => '    public function test()',
                'validation_rules' => [
                    'must_preserve_leading_whitespace' => true,
                    'must_remove_trailing_whitespace' => true,
                    'must_not_change_content' => true
                ]
            ]
        ]
    ],

    /**
     * COMMON VALIDATION MISTAKES TO AVOID
     */
    'common_mistakes' => [
        'changing_method_purposes' => [
            'where() -> with()' => 'where() filters records, with() loads relationships',
            'whereHas() -> has()' => 'whereHas() filters by relationship, has() just checks existence',
            'with() -> load()' => 'with() eager loads, load() lazy loads on existing models'
        ],
        'removing_essential_parts' => [
            'return_statements' => 'Never remove return statements from methods that return values',
            'method_parameters' => 'Never remove or change method parameters',
            'closure_parameters' => 'Preserve closure parameter names and logic'
        ],
        'incorrect_transformations' => [
            'all_to_first' => 'Don\'t change ->all() to ->first() without understanding context',
            'get_to_first' => 'Don\'t change ->get() to ->first() without understanding expected return type'
        ]
    ]
];