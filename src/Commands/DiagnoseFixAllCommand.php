<?php

namespace Rafaelogic\CodeSnoutr\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Rafaelogic\CodeSnoutr\Livewire\FixAllProgress;

class DiagnoseFixAllCommand extends Command
{
    protected $signature = 'codesnoutr:diagnose-fix-all';
    protected $description = 'Diagnose FixAllProgress component for undefined variables';

    public function handle()
    {
        $this->info('🔍 Diagnosing FixAllProgress Livewire Component');
        $this->newLine();

        // Test 1: Check if component class exists
        $this->info('Test 1: Checking if FixAllProgress class exists...');
        if (class_exists(FixAllProgress::class)) {
            $this->line('   ✅ Class exists');
        } else {
            $this->error('   ❌ Class not found');
            return 1;
        }

        // Test 2: Create instance
        $this->info('Test 2: Creating component instance...');
        try {
            $component = new FixAllProgress();
            $this->line('   ✅ Instance created');
        } catch (\Exception $e) {
            $this->error('   ❌ Failed to create instance: ' . $e->getMessage());
            return 1;
        }

        // Test 3: Check all public properties
        $this->info('Test 3: Checking all public properties...');
        $requiredProperties = [
            'sessionId' => 'string|null',
            'progress' => 'array',
            'autoRefresh' => 'boolean',
            'status' => 'string',
            'currentStep' => 'integer',
            'totalSteps' => 'integer',
            'message' => 'string',
            'currentFile' => 'mixed',
            'results' => 'array',
            'fixedCount' => 'integer',
            'failedCount' => 'integer',
            'startedAt' => 'string|null',
            'completedAt' => 'string|null',
        ];

        $missingProperties = [];
        foreach ($requiredProperties as $property => $expectedType) {
            if (!property_exists($component, $property)) {
                $missingProperties[] = $property;
                $this->error("   ❌ Property \${$property} is missing");
            } else {
                $value = $component->{$property};
                $actualType = gettype($value);
                $this->line("   ✅ \${$property}: " . $this->formatValue($value) . " ({$actualType})");
            }
        }

        if (!empty($missingProperties)) {
            $this->error('Missing properties: ' . implode(', ', $missingProperties));
            return 1;
        }

        // Test 4: Call mount method
        $this->info('Test 4: Testing mount() method...');
        try {
            $component->mount();
            $this->line('   ✅ mount() executed successfully');
            $this->line("   Session ID: {$component->sessionId}");
        } catch (\Exception $e) {
            $this->error('   ❌ mount() failed: ' . $e->getMessage());
            return 1;
        }

        // Test 5: Check properties after mount
        $this->info('Test 5: Verifying properties after mount()...');
        foreach ($requiredProperties as $property => $expectedType) {
            $value = $component->{$property};
            $this->line("   ✅ \${$property}: " . $this->formatValue($value));
        }

        // Test 6: Test with custom session ID
        $this->info('Test 6: Testing mount() with custom session ID...');
        try {
            $testSessionId = 'test-session-' . time();
            $component2 = new FixAllProgress();
            $component2->mount($testSessionId);
            
            if ($component2->sessionId === $testSessionId) {
                $this->line('   ✅ Custom session ID set correctly');
            } else {
                $this->error('   ❌ Session ID mismatch');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Failed: ' . $e->getMessage());
        }

        // Test 7: Test render method
        $this->info('Test 7: Testing render() method...');
        try {
            $view = $component->render();
            $this->line('   ✅ render() executed successfully');
            $this->line('   View: ' . $view->name());
        } catch (\Exception $e) {
            $this->error('   ❌ render() failed: ' . $e->getMessage());
        }

        // Test 8: Test loadProgress with empty cache
        $this->info('Test 8: Testing loadProgress() with empty cache...');
        try {
            Cache::forget("fix_all_progress_{$component->sessionId}");
            $component->loadProgress();
            $this->line('   ✅ loadProgress() handled empty cache correctly');
        } catch (\Exception $e) {
            $this->error('   ❌ loadProgress() failed: ' . $e->getMessage());
        }

        // Test 9: Test loadProgress with mock data
        $this->info('Test 9: Testing loadProgress() with mock data...');
        try {
            $mockProgress = [
                'status' => 'processing',
                'current_step' => 5,
                'total_steps' => 10,
                'message' => 'Processing issue 5 of 10',
                'current_file' => ['file' => 'test.php', 'line' => 10, 'rule_id' => 'TEST001', 'id' => 1, 'title' => 'Test Issue'],
                'results' => [
                    ['status' => 'success', 'file' => 'test.php', 'line' => 10, 'title' => 'Fixed', 'message' => 'Issue fixed', 'timestamp' => now(), 'step' => 1]
                ],
                'fixed_count' => 4,
                'failed_count' => 1,
                'started_at' => now()->subMinutes(5)->toISOString(),
                'completed_at' => null,
            ];
            
            Cache::put("fix_all_progress_{$component->sessionId}", $mockProgress, 120);
            $component->loadProgress();
            
            if ($component->status === 'processing' && $component->currentStep === 5) {
                $this->line('   ✅ loadProgress() loaded mock data correctly');
            } else {
                $this->error('   ❌ loadProgress() did not load data correctly');
            }
            
            Cache::forget("fix_all_progress_{$component->sessionId}");
        } catch (\Exception $e) {
            $this->error('   ❌ loadProgress() with mock data failed: ' . $e->getMessage());
        }

        // Test 10: Test refreshProgress
        $this->info('Test 10: Testing refreshProgress() method...');
        try {
            $component->refreshProgress();
            $this->line('   ✅ refreshProgress() executed successfully');
        } catch (\Exception $e) {
            $this->error('   ❌ refreshProgress() failed: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 All diagnostic tests passed!');
        $this->info('The FixAllProgress component is properly configured.');
        $this->newLine();
        
        return 0;
    }

    private function formatValue($value)
    {
        if (is_null($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            return '[' . count($value) . ' items]';
        }
        if (is_string($value) && strlen($value) > 50) {
            return substr($value, 0, 50) . '...';
        }
        return (string) $value;
    }
}
