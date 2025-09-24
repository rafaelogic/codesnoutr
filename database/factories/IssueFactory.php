<?php

namespace Rafaelogic\CodeSnoutr\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Scan;

class IssueFactory extends Factory
{
    protected $model = Issue::class;

    public function definition()
    {
        $severities = ['critical', 'warning', 'info'];
        $categories = ['security', 'performance', 'quality', 'laravel'];
        
        return [
            'scan_id' => Scan::factory(),
            'file_path' => $this->faker->filePath() . '.php',
            'line_number' => $this->faker->numberBetween(1, 500),
            'column_number' => $this->faker->numberBetween(1, 120),
            'category' => $this->faker->randomElement($categories),
            'severity' => $this->faker->randomElement($severities),
            'rule_name' => $this->faker->words(3, true),
            'rule_id' => $this->faker->slug(2, '_'),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'suggestion' => $this->faker->paragraph(),
            'context' => [
                'code' => $this->generateCodeSnippet(),
                'before' => $this->faker->sentences(2),
                'after' => $this->faker->sentences(2),
            ],
            'ai_fix' => null,
            'ai_explanation' => null,
            'ai_confidence' => null,
            'fixed' => false,
            'fixed_at' => null,
            'fix_method' => null,
            'metadata' => [
                'impact' => $this->faker->randomElement(['low', 'medium', 'high']),
                'complexity' => $this->faker->randomElement(['simple', 'moderate', 'complex']),
            ],
        ];
    }

    public function critical()
    {
        return $this->state(function (array $attributes) {
            return [
                'severity' => 'critical',
                'category' => 'security',
            ];
        });
    }

    public function warning()
    {
        return $this->state(function (array $attributes) {
            return [
                'severity' => 'warning',
            ];
        });
    }

    public function security()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'security',
                'severity' => $this->faker->randomElement(['critical', 'warning']),
                'rule_id' => $this->faker->randomElement([
                    'sql_injection',
                    'xss_vulnerability',
                    'csrf_missing',
                    'weak_encryption',
                    'path_traversal'
                ]),
            ];
        });
    }

    public function performance()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'performance',
                'severity' => $this->faker->randomElement(['warning', 'info']),
                'rule_id' => $this->faker->randomElement([
                    'n_plus_one',
                    'slow_query',
                    'memory_leak',
                    'inefficient_loop',
                    'large_response'
                ]),
            ];
        });
    }

    public function fixed()
    {
        return $this->state(function (array $attributes) {
            return [
                'fixed' => true,
                'fix_method' => $this->faker->randomElement(['manual', 'ai_auto', 'bulk']),
                'fixed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            ];
        });
    }

    public function aiFixed()
    {
        return $this->state(function (array $attributes) {
            return [
                'fixed' => true,
                'fix_method' => 'ai_auto',
                'fixed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
                'metadata' => array_merge($attributes['metadata'] ?? [], [
                    'ai_confidence' => $this->faker->randomFloat(2, 0.5, 1.0),
                    'ai_fix_applied' => true,
                    'backup_created' => true,
                ]),
            ];
        });
    }

    private function generateCodeSnippet()
    {
        $snippets = [
            '<?php
class Example {
    public function test() {
        // Problematic code here
        return $data;
    }
}',
            'function processData($input) {
    $query = "SELECT * FROM users WHERE id = " . $input;
    return DB::select($query);
}',
            'for ($i = 0; $i < count($items); $i++) {
    // Inefficient loop
    $result[] = processItem($items[$i]);
}',
            'echo $_GET["data"]; // XSS vulnerability',
            'if ($user_input) {
    include $user_input . ".php"; // Path traversal
}',
        ];

        return $this->faker->randomElement($snippets);
    }
}