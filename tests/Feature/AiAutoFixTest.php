<?php

namespace Rafaelogic\CodeSnoutr\Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Rafaelogic\CodeSnoutr\Livewire\AiAutoFix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class AiAutoFixTest extends TestCase
{
    use RefreshDatabase;

    private Issue $issue;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Enable AI for testing
        Setting::set('ai_enabled', true);
        Setting::set('ai_auto_fix_enabled', true);
        Setting::set('openai_api_key', 'sk-test-key-for-testing');
        
        // Create test scan and issue
        $scan = Scan::create([
            'type' => 'codebase',
            'target' => base_path(),
            'status' => 'completed',
            'files_scanned' => 1,
            'issues_found' => 1,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        // Create a test file with a security issue
        $testFile = $this->createTestFile('<?php

class TestController extends Controller
{
    public function index(Request $request)
    {
        // SQL injection vulnerability
        $query = "SELECT * FROM users WHERE id = " . $request->get("id");
        return DB::select($query);
    }
}');

        $this->issue = Issue::create([
            'scan_id' => $scan->id,
            'severity' => 'critical',
            'category' => 'security',
            'rule_id' => 'sql_injection',
            'title' => 'SQL Injection Vulnerability',
            'description' => 'Direct concatenation of user input into SQL query',
            'file_path' => $testFile,
            'line_number' => 7,
            'column_number' => 20,
            'fixed' => false,
        ]);
    }

    /** @test */
    public function ai_auto_fix_component_initializes_correctly()
    {
        Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id])
            ->assertSet('issue.id', $this->issue->id)
            ->assertSet('aiAvailable', true)
            ->assertSet('autoFixEnabled', true)
            ->assertSet('fixApplied', false)
            ->assertSee('AI Auto-Fix');
    }

    /** @test */
    public function can_analyze_issue_and_get_recommendations()
    {
        // Mock OpenAI API response
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'fix_suggestion' => 'Use parameterized queries to prevent SQL injection',
                                'explanation' => 'The current code concatenates user input directly into SQL query',
                                'code_example' => '$query = "SELECT * FROM users WHERE id = ?"; DB::select($query, [$request->get("id")]);',
                                'confidence' => 0.9,
                                'automated_fix' => true
                            ])
                        ]
                    ]
                ]
            ], 200)
        ]);

        Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id])
            ->call('analyzeIssue')
            ->assertSet('showRecommendations', true)
            ->assertSee('Use parameterized queries')
            ->assertDispatched('notification');
    }

    /** @test */
    public function can_generate_auto_fix_preview()
    {
        // Mock OpenAI API response for fix generation
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'code' => '$query = "SELECT * FROM users WHERE id = ?"; return DB::select($query, [$request->get("id")]);',
                                'explanation' => 'Replaced string concatenation with parameterized query',
                                'confidence' => 0.85,
                                'safe_to_automate' => true,
                                'affected_lines' => [7],
                                'type' => 'replace'
                            ])
                        ]
                    ]
                ]
            ], 200)
        ]);

        Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id])
            ->call('generateAutoFix')
            ->assertSet('showPreview', true)
            ->assertSee('Auto-Fix Preview')
            ->assertDispatched('notification');
    }

    /** @test */
    public function can_apply_auto_fix_with_backup()
    {
        // Create fix preview data
        $component = Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id]);
        $component->set('fixPreview', [
            'fix_data' => [
                'code' => '$query = "SELECT * FROM users WHERE id = ?"; return DB::select($query, [$request->get("id")]);',
                'type' => 'replace',
                'confidence' => 0.85,
                'safe_to_automate' => true,
                'affected_lines' => [7]
            ],
            'preview' => [
                'total_changes' => 1,
                'diff' => [
                    [
                        'line' => 7,
                        'type' => 'changed',
                        'original' => '        $query = "SELECT * FROM users WHERE id = " . $request->get("id");',
                        'modified' => '        $query = "SELECT * FROM users WHERE id = ?"; return DB::select($query, [$request->get("id")]);'
                    ]
                ]
            ]
        ]);

        $component->call('applyFix')
            ->assertSet('fixApplied', true)
            ->assertDispatched('notification')
            ->assertDispatched('issue-fixed');

        // Verify issue was marked as fixed
        $this->issue->refresh();
        $this->assertTrue($this->issue->fixed);
        $this->assertEquals('ai_auto', $this->issue->fix_method);
    }

    /** @test */
    public function can_restore_from_backup()
    {
        // First apply a fix
        $component = Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id]);
        $component->set('fixPreview', [
            'fix_data' => [
                'code' => 'fixed code',
                'type' => 'replace',
                'confidence' => 0.85,
                'safe_to_automate' => true
            ],
            'preview' => ['total_changes' => 1]
        ]);

        $component->call('applyFix');
        
        // Set backup path
        $component->set('backupPath', 'backups/test-backup.php');
        
        // Now restore from backup
        $component->call('restoreFromBackup')
            ->assertSet('fixApplied', false)
            ->assertDispatched('notification')
            ->assertDispatched('issue-restored');
    }

    /** @test */
    public function shows_confidence_indicators()
    {
        $component = Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id]);
        
        // Test high confidence
        $this->assertEquals('High Confidence', $component->call('getConfidenceText', 0.9));
        $this->assertEquals('green', $component->call('getConfidenceColor', 0.9));
        
        // Test medium confidence
        $this->assertEquals('Medium Confidence', $component->call('getConfidenceText', 0.7));
        $this->assertEquals('yellow', $component->call('getConfidenceColor', 0.7));
        
        // Test low confidence
        $this->assertEquals('Low Confidence', $component->call('getConfidenceText', 0.5));
        $this->assertEquals('orange', $component->call('getConfidenceColor', 0.5));
        
        // Test very low confidence
        $this->assertEquals('Very Low Confidence', $component->call('getConfidenceText', 0.3));
        $this->assertEquals('red', $component->call('getConfidenceColor', 0.3));
    }

    /** @test */
    public function validates_safety_before_auto_apply()
    {
        $component = Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id]);
        
        // Safe fix (high confidence and marked as safe)
        $component->set('fixPreview', [
            'fix_data' => [
                'confidence' => 0.85,
                'safe_to_automate' => true
            ]
        ]);
        $this->assertTrue($component->call('isSafeToAutoApply'));
        
        // Unsafe fix (low confidence)
        $component->set('fixPreview', [
            'fix_data' => [
                'confidence' => 0.5,
                'safe_to_automate' => true
            ]
        ]);
        $this->assertFalse($component->call('isSafeToAutoApply'));
        
        // Unsafe fix (marked as unsafe)
        $component->set('fixPreview', [
            'fix_data' => [
                'confidence' => 0.85,
                'safe_to_automate' => false
            ]
        ]);
        $this->assertFalse($component->call('isSafeToAutoApply'));
    }

    /** @test */
    public function can_copy_fix_code_to_clipboard()
    {
        $component = Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id]);
        
        // Set recommendations with code example
        $component->set('recommendations', [
            'code_example' => 'test code example'
        ]);
        
        $component->call('copyFixCode')
            ->assertDispatched('copy-to-clipboard')
            ->assertDispatched('notification');
    }

    /** @test */
    public function handles_ai_service_unavailable()
    {
        // Disable AI
        Setting::set('ai_enabled', false);
        
        Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id])
            ->assertSet('aiAvailable', false)
            ->assertSee('AI Assistant Unavailable');
    }

    /** @test */
    public function handles_openai_api_errors()
    {
        // Mock API error response
        Http::fake([
            'api.openai.com/*' => Http::response([
                'error' => [
                    'message' => 'Invalid API key'
                ]
            ], 401)
        ]);

        Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id])
            ->call('analyzeIssue')
            ->assertSet('showRecommendations', false)
            ->assertSet('error', 'Failed to analyze issue: API call failed: HTTP request returned status code 401')
            ->assertSee('Failed to analyze issue');
    }

    /** @test */
    public function can_hide_recommendations_and_preview()
    {
        $component = Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id]);
        
        // Show recommendations first
        $component->set('showRecommendations', true)
            ->set('recommendations', ['test' => 'data']);
            
        // Hide recommendations
        $component->call('hideRecommendations')
            ->assertSet('showRecommendations', false)
            ->assertSet('recommendations', null);
            
        // Show preview first
        $component->set('showPreview', true)
            ->set('fixPreview', ['test' => 'data']);
            
        // Hide preview
        $component->call('hidePreview')
            ->assertSet('showPreview', false)
            ->assertSet('fixPreview', null);
    }

    /** @test */
    public function can_reset_component_state()
    {
        $component = Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id]);
        
        // Set some state
        $component->set('recommendations', ['test' => 'data'])
            ->set('fixPreview', ['test' => 'data'])
            ->set('showRecommendations', true)
            ->set('showPreview', true)
            ->set('error', 'test error')
            ->set('fixApplied', true);
            
        // Reset state
        $component->call('resetState')
            ->assertSet('recommendations', null)
            ->assertSet('fixPreview', null)
            ->assertSet('showRecommendations', false)
            ->assertSet('showPreview', false)
            ->assertSet('error', null)
            ->assertSet('fixApplied', false);
    }

    /** @test */
    public function shows_fix_type_descriptions()
    {
        $component = Livewire::test(AiAutoFix::class, ['issueId' => $this->issue->id]);
        
        $this->assertEquals('Replace existing code', $component->call('getFixTypeDescription', 'replace'));
        $this->assertEquals('Insert new code', $component->call('getFixTypeDescription', 'insert'));
        $this->assertEquals('Remove code', $component->call('getFixTypeDescription', 'delete'));
        $this->assertEquals('Modify code', $component->call('getFixTypeDescription', 'unknown'));
    }
}