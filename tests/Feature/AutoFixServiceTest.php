<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Rafaelogic\CodeSnoutr\Services\AI\AutoFixService;
use Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Mockery;

class AutoFixServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AutoFixService $autoFixService;
    protected $mockAiService;
    protected string $testFilesPath;
    protected Scan $testScan;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock AI service
        $this->mockAiService = Mockery::mock(AiAssistantService::class);
        $this->mockAiService->shouldReceive('isAvailable')->andReturn(true);
        
        $this->autoFixService = new AutoFixService($this->mockAiService);
        
        // Create a test scan for all issues
        $this->testScan = Scan::create([
            'type' => 'test',
            'target' => 'test',
            'status' => 'running',
            'scan_options' => [],
            'paths_scanned' => [],
            'total_files' => 0,
            'total_issues' => 0,
            'critical_issues' => 0,
            'warning_issues' => 0,
            'info_issues' => 0,
            'started_at' => now(),
        ]);
        
        // Create test files directory
        $this->testFilesPath = storage_path('testing/auto_fix_tests');
        if (!File::exists($this->testFilesPath)) {
            File::makeDirectory($this->testFilesPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (File::exists($this->testFilesPath)) {
            File::deleteDirectory($this->testFilesPath);
        }
        
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_successfully_applies_class_docblock_before_class_declaration()
    {
        // Create test file content
        $originalContent = '<?php

namespace App\Models\Filters;

use Illuminate\Database\Eloquent\Builder;

class TestFilter extends BaseFilter
{
    private $filterList = [\'name\'];
}';

    $testFilePath = $this->createTestFile($originalContent, 'TestFilter.php');
        
        // Create test issue
        $issue = $this->createTestIssue($testFilePath, 7, 'Missing class docblock');
        
        // Mock AI response
        $aiResponse = [
            'code' => '/**
 * Class TestFilter
 *
 * @package App\Models\Filters
 */',
            'explanation' => 'Added class docblock',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [7],
            'type' => 'replace'
        ];

        // Test the application
        $result = $this->autoFixService->applyFix($issue, $aiResponse);

        $this->assertTrue($result['success']);
        
        // Verify the content
        $modifiedContent = File::get($testFilePath);
        $this->assertStringContainsString('/**', $modifiedContent);
        $this->assertStringContainsString('* Class TestFilter', $modifiedContent);
        $this->assertStringContainsString('@package App\Models\Filters', $modifiedContent);
        
        // Verify class docblock is before class declaration
        $lines = explode("\n", $modifiedContent);
        $classLineIndex = $this->findLineContaining($lines, 'class TestFilter');
        $docblockLineIndex = $this->findLineContaining($lines, '/**');
        
        $this->assertLessThan($classLineIndex, $docblockLineIndex, 'Class docblock should be before class declaration');
    }

    /** @test */
    public function it_successfully_applies_method_docblock_before_method_declaration()
    {
        $originalContent = '<?php

namespace App\Models\Filters;

class TestFilter 
{
    public function filterByName()
    {
        return $this->builder;
    }
}';

    $testFilePath = $this->createTestFile($originalContent, 'TestFilter.php');
        $issue = $this->createTestIssue($testFilePath, 7, 'Missing method docblock');
        
        $aiResponse = [
            'code' => '/**
 * Filter results by name
 *
 * @return Builder
 */',
            'explanation' => 'Added method docblock',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [7],
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $aiResponse);

        $this->assertTrue($result['success']);
        
        $modifiedContent = File::get($testFilePath);
        $this->assertStringContainsString('Filter results by name', $modifiedContent);
        $this->assertStringContainsString('@return Builder', $modifiedContent);
        
        // Verify method docblock is before method declaration
        $lines = explode("\n", $modifiedContent);
        $methodLineIndex = $this->findLineContaining($lines, 'public function filterByName');
        $docblockLineIndex = $this->findLineContaining($lines, '/**');
        
        $this->assertLessThan($methodLineIndex, $docblockLineIndex, 'Method docblock should be before method declaration');
    }

    /** @test */
    public function it_handles_combined_docblock_and_class_declaration()
    {
        $originalContent = '<?php

namespace App\Models\Filters;

class TestFilter 
{
    private $filterList = [\'name\'];
}';

    $testFilePath = $this->createTestFile($originalContent, 'TestFilter.php');
        $issue = $this->createTestIssue($testFilePath, 5, 'Missing class docblock');
        
        // AI generates combined docblock + class (this was causing duplicate class declarations)
        $aiResponse = [
            'code' => '/**
 * Class TestFilter
 *
 * @package App\Models\Filters
 */
class TestFilter extends BaseFilter',
            'explanation' => 'Added class docblock',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [5],
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $aiResponse);

        $this->assertTrue($result['success']);
        
        $modifiedContent = File::get($testFilePath);
        
        // Should NOT have duplicate class declarations
        $classDeclarationCount = substr_count($modifiedContent, 'class TestFilter');
        $this->assertEquals(1, $classDeclarationCount, 'Should not have duplicate class declarations');
        
        // But should have the docblock
        $this->assertStringContainsString('/**', $modifiedContent);
        $this->assertStringContainsString('* Class TestFilter', $modifiedContent);
    }

    /** @test */
    public function it_successfully_replaces_complete_method_implementation()
    {
        $originalContent = '<?php

namespace App\Models\Filters;

class TestFilter 
{
    public function filterByName()
    {
        return $this->builder->where("name", "like", "%{$this->request[\'name\']}%");
    }
}';

    $testFilePath = $this->createTestFile($originalContent, 'TestFilter.php');
        $issue = $this->createTestIssue($testFilePath, 7, 'Method too long, needs refactoring');
        
        // AI generates complete method implementation
        $aiResponse = [
            'code' => 'public function filterByName(): Builder
    {
        return isset($this->request[\'name\'])
            ? $this->builder->whereRaw(\'(name LIKE ?)\', [\'%\'.$this->request[\'name\'].\'%\'])
            : $this->builder;
    }',
            'explanation' => 'Refactored method for better readability',
            'confidence' => 0.85,
            'safe_to_automate' => true,
            'affected_lines' => [7],
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $aiResponse);

        $this->assertTrue($result['success']);
        
        $modifiedContent = File::get($testFilePath);
        $this->assertStringContainsString('whereRaw', $modifiedContent);
        $this->assertStringContainsString('isset($this->request[\'name\'])', $modifiedContent);
        $this->assertStringContainsString(': Builder', $modifiedContent);
        
        // Verify PHP syntax is valid
        $this->assertValidPhpSyntax($modifiedContent);
    }

    /** @test */
    public function it_handles_invalid_json_from_ai_gracefully()
    {
    $testFilePath = $this->createTestFile('<?php class TestFilter {}', 'TestFilter.php');
        $issue = $this->createTestIssue($testFilePath, 1, 'Test issue');
        
        // Invalid JSON response from AI
        $invalidResponse = 'This is not valid JSON {broken';

        $result = $this->autoFixService->parseAiFixData($invalidResponse);

        $this->assertNull($result);
    }

    /** @test */
    public function it_handles_malformed_docblocks_with_control_characters()
    {
    $testFilePath = $this->createTestFile('<?php class TestFilter {}', 'TestFilter.php');
        $issue = $this->createTestIssue($testFilePath, 1, 'Test issue');
        
        // AI response with control characters (common issue we encountered)
        $responseWithControlChars = '{
            "code": "/**\\n * Class TestFilter\\n *\\n * @package App\\\\Models\\\\Filters\\n */",
            "explanation": "Added class docblock",
            "confidence": 0.9,
            "safe_to_automate": true,
            "affected_lines": [1],
            "type": "replace"
        }';

        $result = $this->autoFixService->parseAiFixData($responseWithControlChars);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('code', $result);
        $this->assertStringContainsString('Class TestFilter', $result['code']);
    }

    /** @test */
    public function it_fails_gracefully_when_backup_creation_fails()
    {
        // Create a test file in a non-writable location to force backup failure
        $originalContent = '<?php class TestFilter {}';
    $testFilePath = $this->createTestFile($originalContent, 'TestFilter.php');
        
        // Make the backup directory non-writable
        $backupPath = dirname($testFilePath) . '/backups';
        File::makeDirectory($backupPath, 0000); // No permissions
        
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

        // Should handle backup failure gracefully
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('backup', strtolower($result['error']));
        
        // Cleanup
        chmod($backupPath, 0755);
        File::deleteDirectory($backupPath);
    }

    /** @test */
    public function it_validates_syntax_before_applying_changes()
    {
        $originalContent = '<?php

class TestFilter 
{
    public function test() 
    {
        return true;
    }
}';

    $testFilePath = $this->createTestFile($originalContent, 'TestFilter.php');
        $issue = $this->createTestIssue($testFilePath, 5, 'Test issue');
        
        // AI response that would create invalid PHP syntax
        $aiResponse = [
            'code' => 'public function test() 
    {
        return true;
    } // Missing opening brace',
            'explanation' => 'Broken method implementation',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [5],
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $aiResponse);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('validation', strtolower($result['error']));
        
        // Original file should be unchanged
        $currentContent = File::get($testFilePath);
        $this->assertEquals($originalContent, $currentContent);
    }

    /** @test */
    public function it_handles_multiline_method_boundaries_correctly()
    {
        $originalContent = '<?php

class TestFilter 
{
    public function complexMethod($param1, $param2) 
    {
        if ($param1) {
            if ($param2) {
                return $this->builder->where("field", $param1);
            }
        }
        return $this->builder;
    }
    
    public function anotherMethod() 
    {
        return "test";
    }
}';

    $testFilePath = $this->createTestFile($originalContent, 'TestFilter.php');
        $issue = $this->createTestIssue($testFilePath, 5, 'Refactor complex method');
        
        $aiResponse = [
            'code' => 'public function complexMethod($param1, $param2): Builder
    {
        if (!$param1 || !$param2) {
            return $this->builder;
        }
        
        return $this->builder->where("field", $param1);
    }',
            'explanation' => 'Simplified complex method',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [5, 6, 7, 8, 9, 10, 11, 12], // Multiple lines
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $aiResponse);

        $this->assertTrue($result['success']);
        
        $modifiedContent = File::get($testFilePath);
        
        // Should still have the other method
        $this->assertStringContainsString('anotherMethod', $modifiedContent);
        
        // Should have the refactored method
        $this->assertStringContainsString('if (!$param1 || !$param2)', $modifiedContent);
        
        // Should have valid PHP syntax
        $this->assertValidPhpSyntax($modifiedContent);
    }

    /** @test */
    public function it_preserves_proper_indentation_for_class_members()
    {
        $originalContent = '<?php

namespace App\Models\Filters;

class TestFilter 
{
    private $filterList = [\'name\'];
    
    public function test()
    {
        return true;
    }
}';

    $testFilePath = $this->createTestFile($originalContent, 'TestFilter.php');
        $issue = $this->createTestIssue($testFilePath, 8, 'Add method docblock');
        
        $aiResponse = [
            'code' => '/**
     * Test method
     *
     * @return bool
     */',
            'explanation' => 'Added method docblock',
            'confidence' => 0.9,
            'safe_to_automate' => true,
            'affected_lines' => [8],
            'type' => 'replace'
        ];

        $result = $this->autoFixService->applyFix($issue, $aiResponse);

        $this->assertTrue($result['success']);
        
        $modifiedContent = File::get($testFilePath);
        $lines = explode("\n", $modifiedContent);
        
        // Find the docblock and verify indentation matches class members
        $docblockLineIndex = $this->findLineContaining($lines, '/**');
        $methodLineIndex = $this->findLineContaining($lines, 'public function test');
        
        // Both should have the same indentation (4 spaces for class members)
        preg_match('/^(\s*)/', $lines[$docblockLineIndex], $docblockIndent);
        preg_match('/^(\s*)/', $lines[$methodLineIndex], $methodIndent);
        
        $this->assertEquals($methodIndent[1], $docblockIndent[1], 'Docblock and method should have same indentation');
    }

    // Helper methods

    protected function createTestFile(string $content, string $filename = 'test.php'): string
    {
        $filePath = $this->testFilesPath . '/' . $filename;
        File::put($filePath, $content);
        return $filePath;
    }

    protected function createTestIssue(string $filePath, int $lineNumber, string $description): Issue
    {
        return Issue::create([
            'scan_id' => $this->testScan->id,
            'file_path' => $filePath,
            'line_number' => $lineNumber,
            'rule_id' => 'test_rule',
            'description' => $description,
            'title' => 'Test issue',
            'suggestion' => 'Test suggestion',
            'context' => ['line' => $lineNumber],
            'category' => 'style',
            'severity' => 'medium',
            'rule_name' => 'test_rule',
        ]);
    }

    protected function findLineContaining(array $lines, string $needle): int
    {
        foreach ($lines as $index => $line) {
            if (str_contains($line, $needle)) {
                return $index;
            }
        }
        throw new \Exception("Could not find line containing: {$needle}");
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