<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Rafaelogic\CodeSnoutr\Services\AI\AutoFixService;
use Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Illuminate\Support\Facades\File;
use Mockery;

/**
 * These tests are designed to FAIL initially, demonstrating edge cases 
 * and validation scenarios that should be caught by the AutoFixService.
 * They serve as regression tests to ensure we handle problematic cases gracefully.
 */
class AutoFixServiceFailureTest extends TestCase
{
    use RefreshDatabase;

    protected AutoFixService $autoFixService;
    protected $mockAiService;
    protected string $testFilesPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockAiService = Mockery::mock(AiAssistantService::class);
        $this->mockAiService->shouldReceive('isAvailable')->andReturn(true);
        
        $this->autoFixService = new AutoFixService($this->mockAiService);
        
        $this->testFilesPath = storage_path('testing/auto_fix_failure_tests');
        if (!File::exists($this->testFilesPath)) {
            File::makeDirectory($this->testFilesPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testFilesPath)) {
            File::deleteDirectory($this->testFilesPath);
        }
        
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_rejects_ai_response_that_would_create_syntax_errors()
    {
        $originalContent = '<?php

class TestFilter 
{
    public function test() 
    {
        return true;
    }
}';

        $testFilePath = $this->createTestFileForFailure('TestFilter.php', $originalContent);
        $issue = $this->createTestIssue($testFilePath, 5, 'Test issue');
        
        // AI response that creates invalid PHP (missing opening brace)
        $badAiResponse = [
            'code' => 'public function test() 
        return true;
    }', // Missing opening brace
            'explanation' => 'Broken method implementation',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [5],
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $badAiResponse);

        // Should fail validation
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('validation', strtolower($result['error']));
        
        // Original file should be unchanged
        $currentContent = File::get($testFilePath);
        $this->assertEquals($originalContent, $currentContent);
    }

    /** @test */
    public function it_handles_ai_response_with_missing_required_fields()
    {
        $testFilePath = $this->createTestFileForFailure('TestFilter.php', '<?php class TestFilter {}');
        $issue = $this->createTestIssue($testFilePath, 1, 'Test issue');
        
        $incompleteAiResponse = [
            'explanation' => 'Missing code field',
            'confidence' => 0.9,
            // Missing 'code' field
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $incompleteAiResponse);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('required', strtolower($result['error']) ?: '');
    }

    /** @test */
    public function it_handles_file_permission_errors_gracefully()
    {
        $originalContent = '<?php class TestFilter {}';
        $testFilePath = $this->createTestFileForFailure('TestFilter.php', $originalContent);
        
        // Make file read-only
        chmod($testFilePath, 0444);
        
        $issue = $this->createTestIssue($testFilePath, 1, 'Test issue');
        
        $aiResponse = [
            'code' => '/** Class docblock */',
            'explanation' => 'Added docblock',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [1],
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $aiResponse);

        $this->assertFalse($result['success']);
        
        // Restore permissions for cleanup
        chmod($testFilePath, 0644);
    }

    /** @test */
    public function it_handles_extremely_large_ai_responses()
    {
        $testFilePath = $this->createTestFileForFailure('TestFilter.php', '<?php class TestFilter {}');
        $issue = $this->createTestIssue($testFilePath, 1, 'Test issue');
        
        // Create an extremely large code response (potential memory issue)
        $largeCode = str_repeat('// This is a very long comment that goes on and on' . "\n", 10000);
        
        $aiResponse = [
            'code' => $largeCode,
            'explanation' => 'Added very large comment block',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [1],
            'type' => 'replace'
        ];

        // This should either work or fail gracefully without crashing
        $result = $this->autoFixService->applyFix($issue, $aiResponse);
        
        // We accept either success or graceful failure, but no crashes
        $this->assertIsBool($result['success']);
    }

    /** @test */
    public function it_handles_ai_response_with_unicode_characters()
    {
        $testFilePath = $this->createTestFileForFailure('TestFilter.php', '<?php class TestFilter {}');
        $issue = $this->createTestIssue($testFilePath, 1, 'Test issue');
        
        $unicodeAiResponse = [
            'code' => '/**
 * Filtro de pruébas with émojis 🚀 and spëcial chars: ñáéíóú
 * Unicode test: ∑∆∞≈ç√∫˜µ≤≥÷
 */',
            'explanation' => 'Added unicode docblock',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [1],
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $unicodeAiResponse);
        
        // Unicode should be handled properly
        if ($result['success']) {
            $content = File::get($testFilePath);
            $this->assertStringContainsString('🚀', $content);
            $this->assertStringContainsString('ñáéíóú', $content);
        } else {
            // If it fails, it should fail gracefully
            $this->assertIsString($result['error']);
        }
    }

    /** @test */
    public function it_handles_circular_or_infinite_brace_patterns()
    {
        $originalContent = '<?php

class TestFilter 
{
    public function problematic()
    {
        // This method has unusual brace patterns
        if (true) { if (false) { } }
        return true;
    }
}';

        $testFilePath = $this->createTestFileForFailure('TestFilter.php', $originalContent);
        $issue = $this->createTestIssue($testFilePath, 5, 'Refactor method');
        
        // AI response with complex nested braces
        $complexAiResponse = [
            'code' => 'public function problematic(): bool
    {
        // Nested conditions with multiple braces
        if (true) {
            if (false) {
                if (null) {
                    return false;
                }
            }
        }
        return true;
    }',
            'explanation' => 'Refactored with complex nesting',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [5, 6, 7, 8, 9, 10, 11],
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $complexAiResponse);
        
        // Should handle complex brace patterns without infinite loops
        $this->assertIsBool($result['success']);
        
        if ($result['success']) {
            $content = File::get($testFilePath);
            $this->assertValidPhpSyntax($content);
        }
    }

    /** @test */
    public function it_prevents_code_injection_in_ai_responses()
    {
        $testFilePath = $this->createTestFileForFailure('TestFilter.php', '<?php class TestFilter {}');
        $issue = $this->createTestIssue($testFilePath, 1, 'Test issue');
        
        // Potentially malicious AI response (trying to inject system commands)
        $maliciousAiResponse = [
            'code' => '/**
 * Class docblock
 */
<?php system("rm -rf /"); ?>',
            'explanation' => 'Malicious injection attempt',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [1],
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $maliciousAiResponse);
        
        // Should either sanitize or reject malicious content
        if ($result['success']) {
            $content = File::get($testFilePath);
            // Should not contain system command injection
            $this->assertStringNotContainsString('system(', $content);
            $this->assertStringNotContainsString('rm -rf', $content);
        } else {
            // Or should fail validation
            $this->assertFalse($result['success']);
        }
    }

    // Helper methods

    protected function createTestFileForFailure(string $filename, string $content): string
    {
        $filePath = $this->testFilesPath . '/' . $filename;
        File::put($filePath, $content);
        return $filePath;
    }

    protected function createTestIssue(string $filePath, int $lineNumber, string $description): Issue
    {
        return Issue::create([
            'file_path' => $filePath,
            'line_number' => $lineNumber,
            'description' => $description,
            'category' => 'style',
            'severity' => 'medium',
            'rule_name' => 'test_rule',
            'scan_id' => 1
        ]);
    }

    protected function assertValidPhpSyntax(string $phpCode): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'php_syntax_test');
        file_put_contents($tempFile, $phpCode);
        
        $output = [];
        $returnCode = 0;
        exec("php -l {$tempFile} 2>&1", $output, $returnCode);
        
        unlink($tempFile);
        
        $this->assertEquals(0, $returnCode, 'PHP syntax should be valid. Output: ' . implode("\n", $output));
    }
}