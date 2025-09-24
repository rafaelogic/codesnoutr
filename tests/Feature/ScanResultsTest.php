<?php

namespace Rafaelogic\CodeSnoutr\Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Livewire\ScanResults;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScanResultsTest extends TestCase
{
    use RefreshDatabase;

    private Scan $scan;
    private array $testIssues = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->scan = Scan::create([
            'type' => 'codebase',
            'target' => base_path(),
            'status' => 'completed',
            'files_scanned' => 10,
            'issues_found' => 5,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $this->createTestIssues();
    }

    private function createTestIssues()
    {
        $issuesData = [
            [
                'severity' => 'critical',
                'category' => 'security',
                'rule_id' => 'sql_injection',
                'title' => 'SQL Injection Vulnerability',
                'file_path' => 'app/Http/Controllers/UserController.php',
                'line_number' => 25,
            ],
            [
                'severity' => 'high',
                'category' => 'performance',
                'rule_id' => 'n_plus_one',
                'title' => 'N+1 Query Problem',
                'file_path' => 'app/Http/Controllers/UserController.php',
                'line_number' => 45,
            ],
            [
                'severity' => 'medium',
                'category' => 'quality',
                'rule_id' => 'complexity',
                'title' => 'High Cyclomatic Complexity',
                'file_path' => 'app/Services/UserService.php',
                'line_number' => 15,
            ],
            [
                'severity' => 'low',
                'category' => 'style',
                'rule_id' => 'naming',
                'title' => 'Variable Naming Convention',
                'file_path' => 'app/Models/User.php',
                'line_number' => 10,
            ],
            [
                'severity' => 'critical',
                'category' => 'security',
                'rule_id' => 'xss',
                'title' => 'XSS Vulnerability',
                'file_path' => 'resources/views/users/show.blade.php',
                'line_number' => 12,
            ],
        ];

        foreach ($issuesData as $data) {
            $this->testIssues[] = Issue::create(array_merge([
                'scan_id' => $this->scan->id,
                'description' => 'Test issue description for ' . $data['title'],
                'column_number' => 1,
                'fixed' => false,
            ], $data));
        }
    }

    /** @test */
    public function can_view_scan_results_page()
    {
        $response = $this->get("/codesnoutr/results/{$this->scan->id}");
        
        $response->assertStatus(200);
        $response->assertViewIs('codesnoutr::pages.scan-results.scan-results-view');
        $response->assertSee('Scan Results');
    }

    /** @test */
    public function scan_results_displays_correct_scan_information()
    {
        Livewire::test(ScanResults::class, ['scanId' => $this->scan->id])
            ->assertSet('scan.id', $this->scan->id)
            ->assertSet('scan.type', 'codebase')
            ->assertSet('scan.status', 'completed')
            ->assertSee('codebase')
            ->assertSee('completed');
    }

    /** @test */
    public function can_filter_issues_by_severity()
    {
        $component = Livewire::test(ScanResults::class, ['scanId' => $this->scan->id]);
        
        // Filter by critical severity
        $component->call('updateFilters', ['severity' => ['critical']])
            ->call('applyFilters');
        
        $filteredIssues = $component->get('issues');
        $this->assertCount(2, $filteredIssues); // 2 critical issues
        
        foreach ($filteredIssues as $issue) {
            $this->assertEquals('critical', $issue['severity']);
        }
    }

    /** @test */
    public function can_filter_issues_by_category()
    {
        $component = Livewire::test(ScanResults::class, ['scanId' => $this->scan->id]);
        
        // Filter by security category
        $component->call('updateFilters', ['category' => ['security']])
            ->call('applyFilters');
        
        $filteredIssues = $component->get('issues');
        $this->assertCount(2, $filteredIssues); // 2 security issues
        
        foreach ($filteredIssues as $issue) {
            $this->assertEquals('security', $issue['category']);
        }
    }

    /** @test */
    public function can_search_issues_by_file_path()
    {
        $component = Livewire::test(ScanResults::class, ['scanId' => $this->scan->id]);
        
        // Search for UserController issues
        $component->set('search', 'UserController')
            ->call('applyFilters');
        
        $filteredIssues = $component->get('issues');
        $this->assertCount(2, $filteredIssues); // 2 issues in UserController
        
        foreach ($filteredIssues as $issue) {
            $this->assertStringContainsString('UserController', $issue['file_path']);
        }
    }

    /** @test */
    public function can_sort_issues_by_different_criteria()
    {
        $component = Livewire::test(ScanResults::class, ['scanId' => $this->scan->id]);
        
        // Sort by severity (critical first)
        $component->call('sortBy', 'severity')
            ->call('applyFilters');
        
        $sortedIssues = $component->get('issues');
        $this->assertEquals('critical', $sortedIssues[0]['severity']);
        
        // Sort by file path
        $component->call('sortBy', 'file_path')
            ->call('applyFilters');
        
        $sortedIssues = $component->get('issues');
        // First issue should be from app/Http/Controllers (alphabetically first)
        $this->assertStringStartsWith('app/Http/Controllers', $sortedIssues[0]['file_path']);
    }

    /** @test */
    public function can_resolve_issue()
    {
        $issue = $this->testIssues[0];
        
        Livewire::test(ScanResults::class, ['scanId' => $this->scan->id])
            ->call('resolveIssue', $issue->id)
            ->assertDispatched('issue-resolved');
        
        $issue->refresh();
        $this->assertTrue($issue->fixed);
        $this->assertEquals('manual', $issue->fix_method);
        $this->assertNotNull($issue->fixed_at);
    }

    /** @test */
    public function can_mark_issue_as_false_positive()
    {
        $issue = $this->testIssues[0];
        
        Livewire::test(ScanResults::class, ['scanId' => $this->scan->id])
            ->call('markAsFalsePositive', $issue->id)
            ->assertDispatched('issue-resolved');
        
        $issue->refresh();
        $this->assertTrue($issue->fixed);
        $this->assertEquals('false_positive', $issue->fix_method);
    }

    /** @test */
    public function can_ignore_issue()
    {
        $issue = $this->testIssues[0];
        
        Livewire::test(ScanResults::class, ['scanId' => $this->scan->id])
            ->call('markAsIgnored', $issue->id)
            ->assertDispatched('issue-resolved');
        
        $issue->refresh();
        $this->assertTrue($issue->fixed);
        $this->assertEquals('ignored', $issue->fix_method);
    }

    /** @test */
    public function can_bulk_resolve_issues()
    {
        $issueIds = [$this->testIssues[0]->id, $this->testIssues[1]->id];
        
        Livewire::test(ScanResults::class, ['scanId' => $this->scan->id])
            ->set('selectedIssues', $issueIds)
            ->call('bulkAction', 'resolve')
            ->assertDispatched('bulk-action-completed');
        
        foreach ($issueIds as $issueId) {
            $issue = Issue::find($issueId);
            $this->assertTrue($issue->fixed);
            $this->assertEquals('manual', $issue->fix_method);
        }
    }

    /** @test */
    public function can_export_scan_results()
    {
        Livewire::test(ScanResults::class, ['scanId' => $this->scan->id])
            ->call('exportResults', 'json')
            ->assertDispatched('download-ready');
    }

    /** @test */
    public function displays_correct_issue_statistics()
    {
        $component = Livewire::test(ScanResults::class, ['scanId' => $this->scan->id]);
        
        $stats = $component->get('issueStats');
        
        $this->assertEquals(2, $stats['critical']);
        $this->assertEquals(1, $stats['high']);
        $this->assertEquals(1, $stats['medium']);
        $this->assertEquals(1, $stats['low']);
        $this->assertEquals(5, $stats['total']);
    }

    /** @test */
    public function can_load_issues_for_specific_file()
    {
        $component = Livewire::test(ScanResults::class, ['scanId' => $this->scan->id]);
        
        $filePath = 'app/Http/Controllers/UserController.php';
        $component->call('loadFileIssues', $filePath);
        
        $fileIssues = $component->get('selectedFileIssues');
        $this->assertCount(2, $fileIssues); // 2 issues in UserController
        
        foreach ($fileIssues as $issue) {
            $this->assertEquals($filePath, $issue['file_path']);
        }
    }

    /** @test */
    public function can_navigate_between_file_pages()
    {
        $component = Livewire::test(ScanResults::class, ['scanId' => $this->scan->id]);
        
        // Set items per page to 2 to force pagination
        $component->set('itemsPerPage', 2);
        
        // Go to page 2
        $component->call('gotoPage', 2);
        
        // Should show different issues
        $currentPageIssues = $component->get('issues');
        $this->assertLessThanOrEqual(2, count($currentPageIssues));
    }

    /** @test */
    public function can_change_view_mode()
    {
        $component = Livewire::test(ScanResults::class, ['scanId' => $this->scan->id]);
        
        // Switch to list view
        $component->call('setViewMode', 'list')
            ->assertSet('viewMode', 'list');
        
        // Switch to tree view
        $component->call('setViewMode', 'tree')
            ->assertSet('viewMode', 'tree');
    }

    /** @test */
    public function handles_empty_scan_results()
    {
        // Create scan with no issues
        $emptyScan = Scan::create([
            'type' => 'codebase',
            'target' => base_path(),
            'status' => 'completed',
            'files_scanned' => 10,
            'issues_found' => 0,
            'started_at' => now(),
            'completed_at' => now(),
        ]);
        
        Livewire::test(ScanResults::class, ['scanId' => $emptyScan->id])
            ->assertSee('No issues found')
            ->assertSet('totalIssues', 0);
    }

    /** @test */
    public function can_refresh_scan_results()
    {
        $component = Livewire::test(ScanResults::class, ['scanId' => $this->scan->id]);
        
        // Create a new issue
        Issue::create([
            'scan_id' => $this->scan->id,
            'severity' => 'medium',
            'category' => 'quality',
            'rule_id' => 'test_rule',
            'title' => 'New Test Issue',
            'description' => 'A new test issue',
            'file_path' => 'test.php',
            'line_number' => 1,
            'column_number' => 1,
            'fixed' => false,
        ]);
        
        // Refresh results
        $component->call('refreshResults');
        
        // Should now show 6 issues instead of 5
        $this->assertEquals(6, $component->get('totalIssues'));
    }
}