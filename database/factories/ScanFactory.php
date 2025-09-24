<?php

namespace Rafaelogic\CodeSnoutr\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rafaelogic\CodeSnoutr\Models\Scan;

class ScanFactory extends Factory
{
    protected $model = Scan::class;

    public function definition()
    {
        return [
            'type' => $this->faker->randomElement(['codebase', 'directory', 'file']),
            'target' => $this->faker->filePath(),
            'status' => $this->faker->randomElement(['pending', 'running', 'completed', 'failed']),
            'scan_options' => [
                'scanners' => $this->faker->randomElements(['security', 'performance', 'style', 'bugs'], $this->faker->numberBetween(1, 4)),
                'exclude_paths' => $this->faker->randomElements(['vendor/', 'node_modules/', '.git/'], $this->faker->numberBetween(0, 3)),
            ],
            'paths_scanned' => [
                $this->faker->filePath(),
                $this->faker->filePath(),
            ],
            'total_files' => $this->faker->numberBetween(1, 100),
            'total_issues' => $this->faker->numberBetween(0, 50),
            'critical_issues' => $this->faker->numberBetween(0, 10),
            'warning_issues' => $this->faker->numberBetween(0, 20),
            'info_issues' => $this->faker->numberBetween(0, 20),
            'started_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'completed_at' => function (array $attributes) {
                return in_array($attributes['status'], ['completed', 'failed']) 
                    ? $this->faker->dateTimeBetween($attributes['started_at'], 'now')
                    : null;
            },
            'duration_seconds' => function (array $attributes) {
                return in_array($attributes['status'], ['completed', 'failed']) 
                    ? $this->faker->numberBetween(10, 3600)
                    : null;
            },
            'summary' => [
                'files_processed' => $this->faker->numberBetween(1, 100),
                'rules_applied' => $this->faker->numberBetween(10, 50),
            ],
            'error_message' => function (array $attributes) {
                return $attributes['status'] === 'failed' ? $this->faker->sentence() : null;
            },
            'ai_cost' => $this->faker->randomFloat(4, 0, 5.00),
        ];
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'completed_at' => $this->faker->dateTimeBetween($attributes['started_at'] ?? '-1 hour', 'now'),
            ];
        });
    }

    public function running()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'running',
                'completed_at' => null,
            ];
        });
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
                'completed_at' => $this->faker->dateTimeBetween($attributes['started_at'] ?? '-1 hour', 'now'),
                'error_message' => $this->faker->sentence(),
            ];
        });
    }
}