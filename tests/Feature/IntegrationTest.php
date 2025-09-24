<?php

namespace Rafaelogic\CodeSnoutr\Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Rafaelogic\CodeSnoutr\Livewire\Dashboard;
use Rafaelogic\CodeSnoutr\Livewire\ScanWizard;
use Rafaelogic\CodeSnoutr\Livewire\ScanResults;
use Rafaelogic\CodeSnoutr\Livewire\AiAutoFix;
use Rafaelogic\CodeSnoutr\Jobs\ScanCodebaseJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up basic settings for testing
        Setting::set('ai_enabled', true);
        Setting::set('ai_auto_fix_enabled', true);
        Setting::set('openai_api_key', 'sk-test-key');
        Setting::set('notification_enabled', true);
    }

    /** @test */
    public function complete_scan_workflow_with_ai_fixes()
    {
        Queue::fake();

        // Step 1: Start from Dashboard - should show no scans initially
        $dashboard = Livewire::test(Dashboard::class)
            ->assertSee('No scans yet')
            ->assertSet('totalScans', 0)
            ->assertSet('totalIssues', 0);

        // Step 2: Use Scan Wizard to create a new scan
        $wizard = Livewire::test(ScanWizard::class)
            ->assertSet('currentStep', 'project')
            ->set('scanType', 'codebase')
            ->set('targetPath', base_path('tests/fixtures'))
            ->call('nextStep')
            ->assertSet('currentStep', 'options')
            ->set('selectedScanners', ['security', 'performance'])
            ->call('nextStep')
            ->assertSet('currentStep', 'review')
            ->call('startScan')
            ->assertDispatched('scan-started');

        // Verify scan job was queued
        Queue::assertPushed(ScanCodebaseJob::class);

        // Step 3: Simulate scan completion and create test results
        $scan = Scan::create([
            'type' => 'codebase',
            'target' => base_path('tests/fixtures'),
            'status' => 'completed',
            'files_scanned' => 5,
            'issues_found' => 3,
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
        ]);

        // Create test issues
        $criticalIssue = Issue::create([
            'scan_id' => $scan->id,
            'severity' => 'critical',
            'category' => 'security',
            'rule_name' => 'SQL Injection Detection',
            'rule_id' => 'sql_injection',
            'title' => 'SQL Injection Vulnerability',
            'description' => 'Direct concatenation of user input into SQL query',
            'suggestion' => 'Use parameterized queries to prevent SQL injection',
            'context' => ['code' => ['$query = "SELECT * FROM users WHERE id = " . $request->get("id");', 'return DB::select($query);']],
            'file_path' => 'tests/fixtures/TestController.php',
            'line_number' => 15,
            'column_number' => 20,
            'fixed' => false,
        ]);

        $warningIssue = Issue::create([
            'scan_id' => $scan->id,
            'severity' => 'warning',
            'category' => 'performance',
            'rule_name' => 'N+1 Query Detection',
            'rule_id' => 'n_plus_one',
            'title' => 'N+1 Query Problem',
            'description' => 'Potential N+1 query detected in loop',
            'suggestion' => 'Use eager loading to avoid N+1 queries',
            'context' => ['code' => ['for ($i = 0; $i < count($users); $i++) {', '    $users[$i]->posts; // N+1 query', '}']],
            'file_path' => 'tests/fixtures/UserService.php',
            'line_number' => 25,
            'column_number' => 10,
            'fixed' => false,
        ]);

        $infoIssue = Issue::create([
            'scan_id' => $scan->id,
            'severity' => 'info',
            'category' => 'quality',
            'rule_name' => 'Naming Convention',
            'rule_id' => 'naming_convention',
            'title' => 'Naming Convention',
            'description' => 'Variable name does not follow camelCase convention',
            'suggestion' => 'Use camelCase for variable names',
            'context' => ['code' => ['$user_name = $request->input("name");', '// Should be: $userName']],
            'file_path' => 'tests/fixtures/Helper.php',
            'line_number' => 8,
            'column_number' => 5,
            'fixed' => false,
        ]);

        // Step 4: Check Dashboard reflects new scan results
        $dashboard->call('$refresh')
            ->assertSet('totalScans', 1)
            ->assertSet('totalIssues', 3)
            ->assertSee('Recent Scans')
            ->assertSee('SQL Injection Vulnerability');

        // Step 5: View scan results with filtering
        $results = Livewire::test(ScanResults::class, ['scanId' => $scan->id])
            ->assertSee('3 issues found')
            ->assertSee('SQL Injection Vulnerability')
            ->assertSee('N+1 Query Problem')
            ->assertSee('Naming Convention');

        // Test severity filtering
        $results->set('selectedSeverities', ['critical'])
            ->call('applyFilters')
            ->assertSee('SQL Injection Vulnerability')
            ->assertDontSee('N+1 Query Problem');

        // Test category filtering
        $results->set('selectedSeverities', [])
            ->set('selectedCategories', ['security'])
            ->call('applyFilters')
            ->assertSee('SQL Injection Vulnerability')
            ->assertDontSee('N+1 Query Problem');

        // Step 6: Use AI Auto-Fix on critical issue
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'fix_suggestion' => 'Use parameterized queries to prevent SQL injection',
                                'explanation' => 'Replace string concatenation with prepared statements',
                                'code_example' => 'DB::select("SELECT * FROM users WHERE id = ?", [$id])',
                                'confidence' => 0.9,
                                'automated_fix' => true
                            ])
                        ]
                    ]
                ]
            ], 200)
        ]);

        $aiAutoFix = Livewire::test(AiAutoFix::class, ['issueId' => $criticalIssue->id])
            ->call('analyzeIssue')
            ->assertSet('showRecommendations', true)
            ->assertSee('Use parameterized queries');

        // Generate and apply auto-fix
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'code' => 'DB::select("SELECT * FROM users WHERE id = ?", [$request->get("id")])',
                                'explanation' => 'Replaced string concatenation with parameterized query',
                                'confidence' => 0.85,
                                'safe_to_automate' => true,
                                'affected_lines' => [15],
                                'type' => 'replace'
                            ])
                        ]
                    ]
                ]
            ], 200)
        ]);

        $aiAutoFix->call('generateAutoFix')
            ->assertSet('showPreview', true)
            ->call('applyFix')
            ->assertSet('fixApplied', true)
            ->assertDispatched('issue-fixed');

        // Step 7: Verify issue was marked as fixed
        $criticalIssue->refresh();
        $this->assertTrue($criticalIssue->fixed);
        $this->assertEquals('ai_auto', $criticalIssue->fix_method);

        // Step 8: Check Dashboard reflects fixed issue
        $dashboard->call('$refresh')
            ->assertSet('totalIssues', 3) // Still 3 total
            ->assertSet('fixedIssues', 1) // But 1 is fixed
            ->assertSee('1 Fixed');

        // Step 9: Mark another issue as manually fixed
        $results->call('markAsFixed', $warningIssue->id)
            ->assertDispatched('issue-updated');

        $warningIssue->refresh();
        $this->assertTrue($warningIssue->fixed);
        $this->assertEquals('manual', $warningIssue->fix_method);

        // Step 10: Export results
        $results->set('selectedIssues', [$infoIssue->id])
            ->call('exportSelected')
            ->assertDispatched('export-started');

        // Step 11: Final dashboard check
        $dashboard->call('$refresh')
            ->assertSet('totalScans', 1)
            ->assertSet('totalIssues', 3)
            ->assertSet('fixedIssues', 2)
            ->assertSee('2 Fixed');
    }

    /** @test */
    public function scan_wizard_handles_different_scan_types()
    {
        Queue::fake();

        // Test file scan
        $wizard = Livewire::test(ScanWizard::class)
            ->set('scanType', 'file')
            ->set('targetPath', base_path('tests/fixtures/TestFile.php'))
            ->call('nextStep')
            ->assertSet('currentStep', 'options')
            ->call('nextStep')
            ->assertSet('currentStep', 'review')
            ->assertSee('File Scan')
            ->call('startScan');

        Queue::assertPushed(ScanCodebaseJob::class);

        // Test directory scan
        $wizard = Livewire::test(ScanWizard::class)
            ->set('scanType', 'directory')
            ->set('targetPath', base_path('tests/fixtures'))
            ->call('nextStep')
            ->assertSet('currentStep', 'options')
            ->call('nextStep')
            ->assertSet('currentStep', 'review')
            ->assertSee('Directory Scan')
            ->call('startScan');

        Queue::assertPushed(ScanCodebaseJob::class, 2);
    }

    /** @test */
    public function ai_integration_works_across_components()
    {
        // Create test issue
        $scan = Scan::factory()->create(['status' => 'completed']);
        $issue = Issue::factory()->create([
            'scan_id' => $scan->id,
            'severity' => 'critical',
            'fixed' => false
        ]);

        // Mock OpenAI responses
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'fix_suggestion' => 'Test AI recommendation',
                                'confidence' => 0.8,
                                'automated_fix' => true
                            ])
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Test AI recommendations in scan results
        $results = Livewire::test(ScanResults::class, ['scanId' => $scan->id])
            ->call('getAiRecommendation', $issue->id)
            ->assertDispatched('ai-recommendation-ready');

        // Test AI auto-fix component
        $autoFix = Livewire::test(AiAutoFix::class, ['issueId' => $issue->id])
            ->call('analyzeIssue')
            ->assertSet('showRecommendations', true);

        // Verify OpenAI was called
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.openai.com');
        });
    }

    /** @test */
    public function bulk_operations_work_correctly()
    {
        // Create test scan with multiple issues
        $scan = Scan::factory()->create(['status' => 'completed']);
        $issues = Issue::factory()->count(5)->create([
            'scan_id' => $scan->id,
            'fixed' => false
        ]);

        $results = Livewire::test(ScanResults::class, ['scanId' => $scan->id]);

        // Test bulk selection
        $results->call('toggleSelectAll')
            ->assertSet('allSelected', true);

        // Test bulk mark as fixed
        $results->call('bulkMarkAsFixed')
            ->assertDispatched('bulk-operation-completed');

        // Verify all issues were marked as fixed
        foreach ($issues as $issue) {
            $issue->refresh();
            $this->assertTrue($issue->fixed);
        }

        // Test bulk export
        $results->call('bulkExport')
            ->assertDispatched('export-started');
    }

    /** @test */
    public function real_time_updates_work_across_components()
    {
        $scan = Scan::factory()->create(['status' => 'completed']);
        $issue = Issue::factory()->create([
            'scan_id' => $scan->id,
            'fixed' => false
        ]);

        // Set up components
        $dashboard = Livewire::test(Dashboard::class);
        $results = Livewire::test(ScanResults::class, ['scanId' => $scan->id]);

        // Mark issue as fixed in results component
        $results->call('markAsFixed', $issue->id)
            ->assertDispatched('issue-updated');

        // Dashboard should reflect the change
        $dashboard->call('$refresh');
        $this->assertEquals(1, $dashboard->get('fixedIssues'));
    }

    /** @test */
    public function error_handling_works_across_workflow()
    {
        // Test scan with invalid path
        $wizard = Livewire::test(ScanWizard::class)
            ->set('scanType', 'directory')
            ->set('targetPath', '/nonexistent/path')
            ->call('nextStep')
            ->assertHasErrors(['targetPath']);

        // Test AI with invalid API key
        Setting::set('openai_api_key', 'invalid-key');
        
        Http::fake([
            'api.openai.com/*' => Http::response([
                'error' => ['message' => 'Invalid API key']
            ], 401)
        ]);

        $issue = Issue::factory()->create();
        $autoFix = Livewire::test(AiAutoFix::class, ['issueId' => $issue->id])
            ->call('analyzeIssue')
            ->assertSet('error', 'Failed to analyze issue: API call failed: HTTP request returned status code 401');
    }

    /** @test */
    public function performance_with_large_datasets()
    {
        // Create a scan with many issues
        $scan = Scan::factory()->create(['status' => 'completed']);
        Issue::factory()->count(100)->create(['scan_id' => $scan->id]);

        // Test results pagination
        $results = Livewire::test(ScanResults::class, ['scanId' => $scan->id])
            ->assertSet('perPage', 25) // Should paginate
            ->call('loadMore')
            ->assertSee('Load More'); // Should have more results

        // Test dashboard with large dataset
        $dashboard = Livewire::test(Dashboard::class)
            ->assertSet('totalIssues', 100);
    }
}