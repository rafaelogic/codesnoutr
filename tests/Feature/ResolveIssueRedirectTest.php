<?php

namespace Rafaelogic\CodeSnoutr\Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Livewire\ScanResults;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResolveIssueRedirectTest extends TestCase
{
    use RefreshDatabase;

    private Scan $scan;
    private Issue $issue;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test scan with issues
        $this->scan = Scan::create([
            'type' => 'full',
            'target' => '/test/path',
            'status' => 'completed',
            'files_scanned' => 5,
            'issues_found' => 3,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $this->issue = Issue::create([
            'scan_id' => $this->scan->id,
            'file_path' => '/test/path/TestFile.php',
            'line_number' => 10,
            'column_number' => 5,
            'severity' => 'medium',
            'category' => 'quality',
            'title' => 'Test Issue',
            'description' => 'This is a test issue',
            'rule_id' => 'test-rule',
            'suggestion' => 'Fix this issue',
            'fixed' => false,
        ]);
    }

    /** @test */
    public function it_dispatches_redirect_event_when_resolving_issue()
    {
        Livewire::test(ScanResults::class, ['scanId' => $this->scan->id])
            ->call('resolveIssue', $this->issue->id)
            ->assertDispatched('redirect-to-scan-results', [
                'scanId' => $this->scan->id
            ]);
    }

    /** @test */
    public function it_marks_issue_as_resolved_and_dispatches_redirect()
    {
        Livewire::test(ScanResults::class, ['scanId' => $this->scan->id])
            ->call('resolveIssue', $this->issue->id)
            ->assertDispatched('issue-resolved', [
                'issueId' => $this->issue->id
            ])
            ->assertDispatched('redirect-to-scan-results', [
                'scanId' => $this->scan->id
            ]);

        // Verify the issue was marked as resolved
        $this->issue->refresh();
        $this->assertTrue($this->issue->fixed);
        $this->assertEquals('manual', $this->issue->fix_method);
        $this->assertNotNull($this->issue->fixed_at);
    }

    /** @test */
    public function it_dispatches_redirect_event_when_marking_as_ignored()
    {
        Livewire::test(ScanResults::class, ['scanId' => $this->scan->id])
            ->call('markAsIgnored', $this->issue->id)
            ->assertDispatched('redirect-to-scan-results', [
                'scanId' => $this->scan->id
            ]);

        // Verify the issue was marked as ignored
        $this->issue->refresh();
        $this->assertTrue($this->issue->fixed);
        $this->assertEquals('ignored', $this->issue->fix_method);
    }

    /** @test */
    public function it_dispatches_redirect_event_when_marking_as_false_positive()
    {
        Livewire::test(ScanResults::class, ['scanId' => $this->scan->id])
            ->call('markAsFalsePositive', $this->issue->id)
            ->assertDispatched('redirect-to-scan-results', [
                'scanId' => $this->scan->id
            ]);

        // Verify the issue was marked as false positive
        $this->issue->refresh();
        $this->assertTrue($this->issue->fixed);
        $this->assertEquals('false_positive', $this->issue->fix_method);
    }

    /** @test */
    public function it_handles_nonexistent_issue_gracefully()
    {
        $nonExistentId = 99999;

        Livewire::test(ScanResults::class, ['scanId' => $this->scan->id])
            ->call('resolveIssue', $nonExistentId)
            ->assertNotDispatched('redirect-to-scan-results');
    }

    /** @test */
    public function it_refreshes_file_group_data_after_resolving_issue()
    {
        $component = Livewire::test(ScanResults::class, ['scanId' => $this->scan->id]);
        
        // Load file issues first
        $component->call('loadFileIssues', $this->issue->file_path);
        
        // Now resolve the issue
        $component->call('resolveIssue', $this->issue->id)
            ->assertDispatched('issue-resolved')
            ->assertDispatched('redirect-to-scan-results');

        // Verify the issue was updated
        $this->issue->refresh();
        $this->assertTrue($this->issue->fixed);
    }
}
