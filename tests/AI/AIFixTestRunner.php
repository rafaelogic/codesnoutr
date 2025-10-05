<?php

/**
 * AI Auto Fix Test Runner
 * 
 * Quick script to test AI fixes on different issue types and validate
 * that the enhanced prompts and validation are working correctly.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Rafaelogic\CodeSnoutr\Services\AI\AutoFixService;
use Rafaelogic\CodeSnoutr\Models\Issue;

class AIFixTestRunner
{
    protected AutoFixService $autoFixService;
    protected array $testResults = [];

    public function __construct()
    {
        $this->autoFixService = new AutoFixService(
            app(\Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService::class)
        );
    }

    /**
     * Run all AI fix tests
     */
    public function runAllTests(): void
    {
        echo "🚀 Starting AI Auto Fix Test Suite\n";
        echo "=================================\n\n";

        $this->testLongLineIssue();
        $this->testMissingDocblockIssue();
        $this->testSqlInjectionIssue();
        $this->testValidationIssue();
        $this->testTrailingWhitespaceIssue();
        $this->testIncorrectFixValidation();

        $this->printResults();
    }

    /**
     * Test long line formatting
     */
    protected function testLongLineIssue(): void
    {
        echo "📏 Testing Long Line Issue Fix...\n";
        
        $testCode = "return \$this->nearbyPlaces()->where('locale', \$locale)->orderBy('distance', 'asc')->with(['category', 'reviews'])->get();";
        
        $issue = $this->createMockIssue([
            'category' => 'quality',
            'rule_name' => 'quality.long_line',
            'description' => 'Line exceeds 120 characters',
            'line_number' => 1
        ], $testCode);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->validateFix('Long Line', $fix, [
            'has_return' => str_contains($fix['code'] ?? '', 'return'),
            'has_where_method' => str_contains($fix['code'] ?? '', '->where('),
            'has_get_method' => str_contains($fix['code'] ?? '', '->get()'),
            'lines_under_120' => $this->checkLineLengths($fix['code'] ?? '', 120)
        ]);
    }

    /**
     * Test missing docblock fix
     */
    protected function testMissingDocblockIssue(): void
    {
        echo "📚 Testing Missing Docblock Issue Fix...\n";
        
        $testCode = "public function getFullNameAttribute()\n{\n    return \$this->name . ' (' . \$this->email . ')';\n}";
        
        $issue = $this->createMockIssue([
            'category' => 'quality', 
            'rule_name' => 'quality.missing_method_docblock',
            'description' => 'Method lacks proper documentation',
            'line_number' => 1
        ], $testCode);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->validateFix('Missing Docblock', $fix, [
            'is_insert_type' => ($fix['type'] ?? '') === 'insert',
            'has_docblock_start' => str_contains($fix['code'] ?? '', '/**'),
            'has_docblock_end' => str_contains($fix['code'] ?? '', '*/'),
            'has_return_annotation' => str_contains($fix['code'] ?? '', '@return')
        ]);
    }

    /**
     * Test SQL injection fix
     */
    protected function testSqlInjectionIssue(): void
    {
        echo "🔒 Testing SQL Injection Issue Fix...\n";
        
        $testCode = "DB::select(\"SELECT * FROM posts WHERE title LIKE '%{\$query}%'\");";
        
        $issue = $this->createMockIssue([
            'category' => 'security',
            'rule_name' => 'security.sql_injection',
            'description' => 'Raw SQL query with user input concatenation',
            'line_number' => 1
        ], $testCode);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->validateFix('SQL Injection', $fix, [
            'uses_parameter_binding' => str_contains($fix['code'] ?? '', '?'),
            'no_direct_concatenation' => !str_contains($fix['code'] ?? '', '{\$query}'),
            'has_parameter_array' => str_contains($fix['code'] ?? '', '[')
        ]);
    }

    /**
     * Test validation issue fix
     */
    protected function testValidationIssue(): void
    {
        echo "✅ Testing Validation Issue Fix...\n";
        
        $testCode = "User::create([\n    'name' => \$request->name,\n    'email' => \$request->email\n]);";
        
        $issue = $this->createMockIssue([
            'category' => 'laravel',
            'rule_name' => 'laravel.missing_validation',
            'description' => 'User input is not validated',
            'line_number' => 1
        ], $testCode);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->validateFix('Missing Validation', $fix, [
            'has_validate_call' => str_contains($fix['code'] ?? '', 'validate'),
            'has_required_rule' => str_contains($fix['code'] ?? '', 'required'),
            'has_validation_rules' => str_contains($fix['code'] ?? '', '[')
        ]);
    }

    /**
     * Test trailing whitespace fix
     */
    protected function testTrailingWhitespaceIssue(): void
    {
        echo "🧹 Testing Trailing Whitespace Issue Fix...\n";
        
        $testCode = "    public function test()   ";
        
        $issue = $this->createMockIssue([
            'category' => 'quality',
            'rule_name' => 'quality.trailing_whitespace',
            'description' => 'Line has trailing whitespace',
            'line_number' => 1
        ], $testCode);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->validateFix('Trailing Whitespace', $fix, [
            'no_trailing_spaces' => !str_ends_with($fix['code'] ?? '', ' '),
            'preserves_content' => str_contains($fix['code'] ?? '', 'public function test()'),
            'preserves_indentation' => str_starts_with($fix['code'] ?? '', '    ')
        ]);
    }

    /**
     * Test that validation catches incorrect fixes
     */
    protected function testIncorrectFixValidation(): void
    {
        echo "🛡️ Testing Incorrect Fix Validation...\n";
        
        $testCode = "return \$this->places()->where('locale', \$locale)->get();";
        
        $issue = $this->createMockIssue([
            'category' => 'quality',
            'rule_name' => 'quality.long_line',
            'description' => 'Line too long',
            'line_number' => 1
        ], $testCode);

        // Simulate incorrect AI response (like you experienced)
        $incorrectFix = [
            'code' => "\$this->places()->with('locale')->get();", // Missing return, wrong method
            'explanation' => 'Fixed line length',
            'confidence' => 0.8,
            'type' => 'replace'
        ];

        // Test validation
        $reflection = new ReflectionMethod($this->autoFixService, 'validateAiFixData');
        $reflection->setAccessible(true);
        $isValid = $reflection->invoke($this->autoFixService, $incorrectFix, $issue);

        $this->testResults['Incorrect Fix Validation'] = [
            'passed' => !$isValid, // Should be false (rejected)
            'details' => [
                'validation_rejected_bad_fix' => !$isValid
            ]
        ];

        echo $isValid ? "❌ FAILED - Validation should have rejected incorrect fix\n" : "✅ PASSED - Validation correctly rejected incorrect fix\n";
    }

    /**
     * Create mock issue for testing
     */
    protected function createMockIssue(array $attributes, string $testCode): Issue
    {
        // Create temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'codesnoutr_test_');
        file_put_contents($tempFile, "<?php\n\n" . $testCode);

        $issue = new Issue();
        foreach ($attributes as $key => $value) {
            $issue->$key = $value;
        }
        $issue->file_path = $tempFile;
        $issue->id = rand(1000, 9999);

        return $issue;
    }

    /**
     * Validate fix results
     */
    protected function validateFix(string $testName, ?array $fix, array $validations): void
    {
        $passed = $fix !== null;
        $details = [];

        if ($fix) {
            foreach ($validations as $key => $expected) {
                $details[$key] = $expected;
                if (!$expected) {
                    $passed = false;
                }
            }
        }

        $this->testResults[$testName] = [
            'passed' => $passed,
            'fix_generated' => $fix !== null,
            'confidence' => $fix['confidence'] ?? 0,
            'details' => $details
        ];

        echo $passed ? "✅ PASSED" : "❌ FAILED";
        if ($fix) {
            echo " (Confidence: " . number_format(($fix['confidence'] ?? 0) * 100, 1) . "%)";
        }
        echo "\n";
    }

    /**
     * Check if all lines are under specified length
     */
    protected function checkLineLengths(string $code, int $maxLength): bool
    {
        $lines = explode("\n", $code);
        foreach ($lines as $line) {
            if (strlen($line) > $maxLength) {
                return false;
            }
        }
        return true;
    }

    /**
     * Print final test results
     */
    protected function printResults(): void
    {
        echo "\n📊 Test Results Summary\n";
        echo "======================\n";

        $totalTests = count($this->testResults);
        $passedTests = array_sum(array_column($this->testResults, 'passed'));

        foreach ($this->testResults as $testName => $result) {
            $status = $result['passed'] ? '✅ PASS' : '❌ FAIL';
            echo sprintf("%-30s %s\n", $testName, $status);
        }

        echo "\n📈 Overall Results: {$passedTests}/{$totalTests} tests passed\n";
        
        if ($passedTests === $totalTests) {
            echo "🎉 All tests passed! AI fixes are working correctly.\n";
        } else {
            echo "⚠️  Some tests failed. Check the AI prompt enhancements.\n";
        }
    }
}

// Run the tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $runner = new AIFixTestRunner();
    $runner->runAllTests();
}