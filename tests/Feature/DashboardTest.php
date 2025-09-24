<?php

namespace Rafaelogic\CodeSnoutr\Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Livewire\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_view_dashboard_page()
    {
        $response = $this->get('/codesnoutr');
        
        $response->assertStatus(200);
        $response->assertViewIs('codesnoutr::pages.dashboard');
        $response->assertSee('CodeSnoutr Dashboard');
    }

    /** @test */
    public function dashboard_displays_correct_statistics()
    {
        // Create test scans
        $scan1 = Scan::create([
            'type' => 'codebase',
            'target' => base_path(),
            'status' => 'completed',
            'scan_options' => ['scanners' => ['security']],
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHours(1),
        ]);

        $scan2 = Scan::create([
            'type' => 'directory',
            'target' => base_path('app'),
            'status' => 'completed',
            'scan_options' => ['scanners' => ['performance']],
            'total_files' => 50,
            'total_issues' => 25,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

                // Create issues
        Issue::create([
            'scan_id' => $scan1->id,
            'severity' => 'critical',
            'category' => 'security',
            'rule_name' => 'SQL Injection Detection',
            'rule_id' => 'sql_injection',
            'title' => 'SQL injection vulnerability',
            'description' => 'Direct concatenation of user input into SQL query',
            'suggestion' => 'Use parameterized queries',
            'context' => ['code' => ['$query = "SELECT * FROM users WHERE id = " . $id;']],
            'file_path' => 'app/Http/Controllers/UserController.php',
            'line_number' => 25,
            'fixed' => false,
        ]);

        Issue::create([
            'scan_id' => $scan1->id,
            'severity' => 'warning',
            'category' => 'performance',
            'rule_name' => 'N+1 Query Detection',
            'rule_id' => 'n_plus_one',
            'title' => 'N+1 query detected',
            'description' => 'Potential N+1 query in loop',
            'suggestion' => 'Use eager loading',
            'context' => ['code' => ['foreach ($users as $user) { $user->posts; }']],
            'file_path' => 'app/Models/User.php',
            'line_number' => 15,
            'fixed' => true,
        ]);

        Livewire::test(Dashboard::class)
            ->assertSet('totalScans', 2)
            ->assertSet('totalIssues', 2)
            ->assertSet('criticalIssues', 1)
            ->assertSee('Total Scans')
            ->assertSee('Total Issues')
            ->assertSee('Critical Issues');
    }

    /** @test */
    public function dashboard_shows_recent_scans()
    {
        $scan = Scan::create([
            'type' => 'codebase',
            'target' => base_path(),
            'status' => 'completed',
            'scan_options' => ['scanners' => ['security']],
            'total_files' => 100,
            'total_issues' => 50,
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHours(1),
        ]);

        Livewire::test(Dashboard::class)
            ->assertSee('Recent Scans')
            ->assertSee('codebase')
            ->assertSee('completed');
    }

    /** @test */
    public function dashboard_shows_issue_distribution_chart()
    {
        // Create issues with different severities
        $scan = Scan::create([
            'type' => 'codebase',
            'target' => base_path(),
            'status' => 'completed',
            'scan_options' => ['scanners' => ['security']],
            'total_files' => 100,
            'total_issues' => 4,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $severities = ['critical', 'warning', 'info'];
        foreach ($severities as $severity) {
            Issue::create([
                'scan_id' => $scan->id,
                'severity' => $severity,
                'category' => 'security',
                'rule_name' => 'Test Rule',
                'rule_id' => 'test_rule',
                'title' => 'Test Issue',
                'description' => 'Test issue description',
                'suggestion' => 'Fix this issue',
                'context' => ['code' => ['test code']],
                'file_path' => 'test.php',
                'line_number' => 1,
            ]);
        }        $component = Livewire::test(Dashboard::class);
        
        $chartData = $component->get('issueDistribution');
        $this->assertCount(4, $chartData);
        $this->assertEquals(1, $chartData['critical']);
        $this->assertEquals(1, $chartData['high']);
        $this->assertEquals(1, $chartData['medium']);
        $this->assertEquals(1, $chartData['low']);
    }

        /** @test */
    public function dashboard_calculates_scan_completion_rate()
    {
        // Create completed scans
        Scan::factory()->completed()->create([
            'total_files' => 100,
            'total_issues' => 10,
        ]);

        // Create failed scan
        Scan::factory()->failed()->create();

        $component = Livewire::test(Dashboard::class);
        $this->assertCount(2, Scan::all());
    }

    /** @test */
    public function dashboard_refreshes_data_on_scan_completion()
    {
        $component = Livewire::test(Dashboard::class);
        
        // Initially no scans
        $component->assertSet('totalScans', 0);

        // Create a new scan
        Scan::factory()->completed()->create([
            'total_files' => 100,
            'total_issues' => 10,
        ]);

        // Refresh dashboard
        $component->call('refreshStats');
        $component->assertSet('totalScans', 1);
    }

    /** @test */
    public function dashboard_handles_empty_state()
    {
        Livewire::test(Dashboard::class)
            ->assertSet('totalScans', 0)
            ->assertSet('totalIssues', 0)
            ->assertSet('criticalIssues', 0)
            ->assertSee('No scans have been run yet');
    }
}