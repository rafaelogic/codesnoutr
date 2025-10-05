<?php

namespace Rafaelogic\CodeSnoutr\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Livewire\Dashboard;
use Livewire\Livewire;

class DashboardAiFixTest extends TestCase
{
    /** @test */
    public function it_can_display_fix_all_ui_when_issues_exist()
    {
        // Create a scan with issues
        $scan = Scan::factory()->create(['status' => 'completed']);
        
        // Create some unfixed issues
        Issue::factory()->count(5)->create([
            'scan_id' => $scan->id,
            'fixed' => false,
            'rule_id' => 'quality.long_line',
            'severity' => 'medium'
        ]);

        $component = Livewire::test(Dashboard::class);
        
        $component->assertSee('Fix All Issues with AI');
        $component->assertSee('Let AI automatically fix');
    }

    /** @test */
    public function it_can_parse_ai_fix_for_preview()
    {
        $component = Livewire::test(Dashboard::class);
        
        // Test JSON format
        $jsonFix = json_encode([
            'explanation' => 'Fixed long line by breaking it into multiple lines',
            'code' => "// Fixed code\nif (condition) {\n    return value;\n}",
            'confidence' => 0.9
        ]);
        
        $parsed = $component->parseAiFixForPreview($jsonFix);
        
        $this->assertIsArray($parsed);
        $this->assertEquals('Fixed long line by breaking it into multiple lines', $parsed['explanation']);
        $this->assertEquals(0.9, $parsed['confidence']);
        $this->assertStringContains('Fixed code', $parsed['code']);
    }

    /** @test */
    public function it_handles_legacy_ai_fix_format()
    {
        $component = Livewire::test(Dashboard::class);
        
        // Test legacy string format
        $legacyFix = "return \$this->places()->where('locale', \$locale)->get();";
        
        $parsed = $component->parseAiFixForPreview($legacyFix);
        
        $this->assertIsArray($parsed);
        $this->assertEquals('AI fix generated', $parsed['explanation']);
        $this->assertEquals($legacyFix, $parsed['code']);
        $this->assertEquals(0.8, $parsed['confidence']);
    }

    /** @test */
    public function it_shows_progress_during_fix_all_operation()
    {
        // Create a scan with issues
        $scan = Scan::factory()->create(['status' => 'completed']);
        
        Issue::factory()->create([
            'scan_id' => $scan->id,
            'fixed' => false,
            'rule_id' => 'quality.long_line',
            'title' => 'Line too long in UserController.php',
            'file_path' => '/app/Http/Controllers/UserController.php',
            'line_number' => 42
        ]);

        $component = Livewire::test(Dashboard::class);
        
        // Set state as if fix is in progress
        $component->set('fixAllInProgress', true);
        $component->set('currentFixingIssue', [
            'id' => 1,
            'title' => 'Line too long in UserController.php',
            'file' => 'UserController.php',
            'line' => 42
        ]);

        $component->assertSee('Fixing: Line too long in UserController.php in UserController.php');
    }

    /** @test */
    public function it_displays_fix_all_results_with_previews()
    {
        $component = Livewire::test(Dashboard::class);
        
        // Set fix results
        $component->set('showFixAllResults', true);
        $component->set('fixAllResults', [
            [
                'issue_id' => 1,
                'title' => 'Line too long',
                'file' => 'UserController.php',
                'status' => 'success',
                'message' => 'Successfully applied AI fix',
                'ai_fix' => [
                    'explanation' => 'Split long line into multiple lines',
                    'code' => "if (condition) {\n    return value;\n}",
                    'confidence' => 0.95
                ]
            ],
            [
                'issue_id' => 2,
                'title' => 'Missing validation',
                'file' => 'UserRequest.php',
                'status' => 'failed',
                'message' => 'Could not apply fix automatically',
                'ai_fix' => null
            ]
        ]);

        $component->assertSee('AI Fix All Complete');
        $component->assertSee('1 issues fixed, 1 failed');
        $component->assertSee('Split long line into multiple lines');
        $component->assertSee('Could not apply fix automatically');
    }

    /** @test */
    public function it_can_hide_fix_all_results()
    {
        $component = Livewire::test(Dashboard::class);
        
        // Set fix results visible
        $component->set('showFixAllResults', true);
        $component->set('fixAllResults', [['test' => 'data']]);

        $component->assertSee('AI Fix All Complete');
        
        // Hide results
        $component->call('hideFixAllResults');
        
        $component->assertDontSee('AI Fix All Complete');
        $this->assertFalse($component->get('showFixAllResults'));
        $this->assertEmpty($component->get('fixAllResults'));
    }
}