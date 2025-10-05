<?php

namespace Rafaelogic\CodeSnoutr\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Rafaelogic\CodeSnoutr\Jobs\FixAllIssuesJob;
use Rafaelogic\CodeSnoutr\Models\Issue;

class VerifyBackgroundSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'codesnoutr:verify-background-setup';

    /**
     * The console command description.
     */
    protected $description = 'Verify that the background Fix All setup is working correctly';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Verifying CodeSnoutr Background Fix All Setup...');
        $this->newLine();

        $allChecks = [
            'routes' => $this->checkRoutes(),
            'cache' => $this->checkCache(),
            'job_class' => $this->checkJobClass(),
            'livewire_component' => $this->checkLivewireComponent(),
            'view_template' => $this->checkViewTemplate(),
            'queue_config' => $this->checkQueueConfig(),
            'test_issues' => $this->checkTestIssues(),
        ];

        $this->newLine();
        $this->displaySummary($allChecks);

        return $this->hasFailures($allChecks) ? 1 : 0;
    }

    /**
     * Check if routes are properly registered
     */
    protected function checkRoutes(): array
    {
        $this->info('📍 Checking Routes...');
        
        try {
            $routeExists = Route::has('codesnoutr.fix-all.progress');
            
            if ($routeExists) {
                $this->line('  ✅ Fix All progress route is registered');
                return ['status' => 'pass', 'message' => 'Routes configured correctly'];
            } else {
                $this->line('  ❌ Fix All progress route is missing');
                return ['status' => 'fail', 'message' => 'Route codesnoutr.fix-all.progress not found'];
            }
        } catch (\Exception $e) {
            $this->line('  ❌ Error checking routes: ' . $e->getMessage());
            return ['status' => 'fail', 'message' => 'Route check failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check cache functionality
     */
    protected function checkCache(): array
    {
        $this->info('💾 Checking Cache...');
        
        try {
            $testKey = 'codesnoutr_background_test_' . uniqid();
            $testData = ['test' => true, 'timestamp' => now()->toISOString()];
            
            // Test cache put
            Cache::put($testKey, $testData, now()->addMinutes(5));
            
            // Test cache get
            $retrieved = Cache::get($testKey);
            
            if ($retrieved && $retrieved['test'] === true) {
                $this->line('  ✅ Cache read/write working correctly');
                Cache::forget($testKey); // Clean up
                return ['status' => 'pass', 'message' => 'Cache is functional'];
            } else {
                $this->line('  ❌ Cache read/write failed');
                return ['status' => 'fail', 'message' => 'Cache functionality not working'];
            }
        } catch (\Exception $e) {
            $this->line('  ❌ Cache error: ' . $e->getMessage());
            return ['status' => 'fail', 'message' => 'Cache check failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check job class exists and is valid
     */
    protected function checkJobClass(): array
    {
        $this->info('⚙️ Checking Job Class...');
        
        try {
            $jobClass = FixAllIssuesJob::class;
            
            if (class_exists($jobClass)) {
                $this->line('  ✅ FixAllIssuesJob class exists');
                
                // Check if job can be instantiated
                $testJob = new $jobClass('test-session-id');
                $this->line('  ✅ Job can be instantiated');
                
                return ['status' => 'pass', 'message' => 'Job class is ready'];
            } else {
                $this->line('  ❌ FixAllIssuesJob class not found');
                return ['status' => 'fail', 'message' => 'Job class missing'];
            }
        } catch (\Exception $e) {
            $this->line('  ❌ Job class error: ' . $e->getMessage());
            return ['status' => 'fail', 'message' => 'Job class check failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check Livewire component
     */
    protected function checkLivewireComponent(): array
    {
        $this->info('⚡ Checking Livewire Component...');
        
        try {
            if (!class_exists('Livewire\Livewire')) {
                $this->line('  ⚠️ Livewire not installed');
                return ['status' => 'warning', 'message' => 'Livewire not available'];
            }

            $componentClass = 'Rafaelogic\CodeSnoutr\Livewire\FixAllProgress';
            
            if (class_exists($componentClass)) {
                $this->line('  ✅ FixAllProgress Livewire component exists');
                
                // Check if component can be instantiated
                $component = new $componentClass();
                $this->line('  ✅ Component can be instantiated');
                
                return ['status' => 'pass', 'message' => 'Livewire component is ready'];
            } else {
                $this->line('  ❌ FixAllProgress component not found');
                return ['status' => 'fail', 'message' => 'Livewire component missing'];
            }
        } catch (\Exception $e) {
            $this->line('  ❌ Livewire component error: ' . $e->getMessage());
            return ['status' => 'fail', 'message' => 'Component check failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check view template
     */
    protected function checkViewTemplate(): array
    {
        $this->info('👁️ Checking View Template...');
        
        try {
            $viewPath = __DIR__ . '/../../resources/views/livewire/fix-all-progress.blade.php';
            
            if (file_exists($viewPath)) {
                $this->line('  ✅ Progress view template exists');
                
                $content = file_get_contents($viewPath);
                if (strpos($content, 'wire:poll') !== false) {
                    $this->line('  ✅ Template has Livewire polling');
                } else {
                    $this->line('  ⚠️ Template missing Livewire polling');
                }
                
                return ['status' => 'pass', 'message' => 'View template is ready'];
            } else {
                $this->line('  ❌ Progress view template not found');
                return ['status' => 'fail', 'message' => 'View template missing'];
            }
        } catch (\Exception $e) {
            $this->line('  ❌ View template error: ' . $e->getMessage());
            return ['status' => 'fail', 'message' => 'View check failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check queue configuration
     */
    protected function checkQueueConfig(): array
    {
        $this->info('🔄 Checking Queue Configuration...');
        
        try {
            $queueConfig = config('codesnoutr.queue');
            
            if (!$queueConfig || !$queueConfig['enabled']) {
                $this->line('  ⚠️ CodeSnoutr queue is disabled in config');
                return ['status' => 'warning', 'message' => 'Queue disabled - jobs will run synchronously'];
            }
            
            $this->line('  ✅ CodeSnoutr queue is enabled');
            $this->line('  📝 Queue connection: ' . ($queueConfig['connection'] ?? 'default'));
            $this->line('  📝 Queue name: ' . ($queueConfig['name'] ?? 'default'));
            $this->line('  📝 Timeout: ' . ($queueConfig['timeout'] ?? 300) . ' seconds');
            
            return ['status' => 'pass', 'message' => 'Queue configuration is ready'];
        } catch (\Exception $e) {
            $this->line('  ❌ Queue config error: ' . $e->getMessage());
            return ['status' => 'fail', 'message' => 'Queue config check failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check for test issues
     */
    protected function checkTestIssues(): array
    {
        $this->info('📋 Checking Test Data...');
        
        try {
            if (!class_exists(Issue::class)) {
                $this->line('  ❌ Issue model not found');
                return ['status' => 'fail', 'message' => 'Issue model missing'];
            }

            $totalIssues = Issue::count();
            $unfixedIssues = Issue::where('fixed', false)->count();
            
            $this->line("  📊 Total issues: {$totalIssues}");
            $this->line("  📊 Unfixed issues: {$unfixedIssues}");
            
            if ($unfixedIssues > 0) {
                $this->line('  ✅ Test data available for Fix All testing');
                return ['status' => 'pass', 'message' => "Ready to test with {$unfixedIssues} unfixed issues"];
            } else {
                $this->line('  ⚠️ No unfixed issues found for testing');
                return ['status' => 'warning', 'message' => 'No test data available - create some issues first'];
            }
        } catch (\Exception $e) {
            $this->line('  ❌ Test data error: ' . $e->getMessage());
            return ['status' => 'fail', 'message' => 'Test data check failed: ' . $e->getMessage()];
        }
    }

    /**
     * Display summary of all checks
     */
    protected function displaySummary(array $checks): void
    {
        $this->info('📋 VERIFICATION SUMMARY');
        $this->info(str_repeat('=', 50));

        $passed = 0;
        $warnings = 0;
        $failed = 0;

        foreach ($checks as $checkName => $result) {
            $icon = match($result['status']) {
                'pass' => '✅',
                'warning' => '⚠️',
                'fail' => '❌'
            };

            $this->line("{$icon} " . ucwords(str_replace('_', ' ', $checkName)) . ": {$result['message']}");
            
            match($result['status']) {
                'pass' => $passed++,
                'warning' => $warnings++,
                'fail' => $failed++
            };
        }

        $this->newLine();
        
        if ($failed > 0) {
            $this->error("❌ {$failed} checks failed - Fix All background processing may not work correctly");
        } elseif ($warnings > 0) {
            $this->warn("⚠️ {$warnings} warnings - Fix All should work but with limitations");
        } else {
            $this->info("🎉 All checks passed! Fix All background processing is ready to use");
        }

        if ($passed > 0 || $warnings > 0) {
            $this->newLine();
            $this->info('🚀 NEXT STEPS:');
            $this->line('1. Test the functionality: php artisan codesnoutr:test-fix-all');
            $this->line('2. Monitor progress: php artisan codesnoutr:monitor-fix-all {session-id}');
            $this->line('3. Ensure queue worker is running: php artisan queue:work');
            $this->line('4. Access progress page: /codesnoutr/fix-all/{session-id}');
        }
    }

    /**
     * Check if there are any failures
     */
    protected function hasFailures(array $checks): bool
    {
        foreach ($checks as $result) {
            if ($result['status'] === 'fail') {
                return true;
            }
        }
        return false;
    }
}