<?php

namespace Tests\Unit;

use Tests\TestCase;
use Rafaelogic\CodeSnoutr\Services\AI\AutoFixService;
use Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService;
use ReflectionClass;
use Mockery;

class AutoFixServiceUnitTest extends TestCase
{
    protected AutoFixService $autoFixService;
    protected ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        
        $mockAiService = Mockery::mock(AiAssistantService::class);
        $mockAiService->shouldReceive('isAvailable')->andReturn(true);
        
        $this->autoFixService = new AutoFixService($mockAiService);
        $this->reflection = new ReflectionClass($this->autoFixService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_correctly_identifies_class_docblocks()
    {
        $method = $this->reflection->getMethod('isClassDocblock');
        $method->setAccessible(true);

        // Valid class docblocks
        $classDocblock1 = '/**
 * Class TestFilter
 */';
        $this->assertTrue($method->invoke($this->autoFixService, $classDocblock1));

        $classDocblock2 = '/**
 * Filter class for handling user queries
 *
 * @package App\Models\Filters
 */';
        $this->assertTrue($method->invoke($this->autoFixService, $classDocblock2));

        // Combined docblock + class (should still be detected as class docblock)
        $combinedDocblock = '/**
 * Class TestFilter
 *
 * @package App\Models\Filters
 */
class TestFilter extends BaseFilter';
        $this->assertTrue($method->invoke($this->autoFixService, $combinedDocblock));

        // Not class docblocks
        $methodDocblock = '/**
 * Filter by name
 *
 * @return Builder
 */';
        $this->assertFalse($method->invoke($this->autoFixService, $methodDocblock));

        $propertyDocblock = '/**
 * @var array
 */';
        $this->assertFalse($method->invoke($this->autoFixService, $propertyDocblock));
    }

    /** @test */
    public function it_correctly_identifies_method_docblocks()
    {
        $method = $this->reflection->getMethod('isMethodDocblock');
        $method->setAccessible(true);

        // Valid method docblocks
        $methodDocblock1 = '/**
 * Filter results by name
 *
 * @return Builder
 */';
        $this->assertTrue($method->invoke($this->autoFixService, $methodDocblock1));

        $methodDocblock2 = '/**
 * Set the filter value
 *
 * @param string $value
 * @return self
 */';
        $this->assertTrue($method->invoke($this->autoFixService, $methodDocblock2));

        $propertyDocblock = '/**
 * List of filterable fields
 *
 * @var array
 */';
        $this->assertTrue($method->invoke($this->autoFixService, $propertyDocblock));

        // Not method docblocks
        $classDocblock = '/**
 * Class TestFilter
 */';
        $this->assertFalse($method->invoke($this->autoFixService, $classDocblock));

        $completeMethod = 'public function test(): bool
{
    return true;
}';
        $this->assertFalse($method->invoke($this->autoFixService, $completeMethod));
    }

    /** @test */
    public function it_correctly_identifies_complete_method_implementations()
    {
        $method = $this->reflection->getMethod('isCompleteMethodImplementation');
        $method->setAccessible(true);

        // Valid complete method implementations
        $simpleMethod = 'public function test(): bool
{
    return true;
}';
        $this->assertTrue($method->invoke($this->autoFixService, $simpleMethod));

        $complexMethod = 'private function filterByName(): Builder
{
    if (isset($this->request[\'name\'])) {
        return $this->builder->where(\'name\', \'like\', \'%\'.$this->request[\'name\'].\'%\');
    }
    return $this->builder;
}';
        $this->assertTrue($method->invoke($this->autoFixService, $complexMethod));

        $protectedMethod = 'protected function validateInput($input): bool
{
    return !empty($input);
}';
        $this->assertTrue($method->invoke($this->autoFixService, $protectedMethod));

        // Not complete method implementations
        $methodDocblock = '/**
 * @return Builder
 */';
        $this->assertFalse($method->invoke($this->autoFixService, $methodDocblock));

        $methodSignatureOnly = 'public function test(): bool';
        $this->assertFalse($method->invoke($this->autoFixService, $methodSignatureOnly));

        $property = 'private $filterList = [\'name\'];';
        $this->assertFalse($method->invoke($this->autoFixService, $property));
    }

    /** @test */
    public function it_correctly_identifies_class_member_code()
    {
        $method = $this->reflection->getMethod('isClassMemberCode');
        $method->setAccessible(true);

        // Valid class member code
        $publicMethod = 'public function test(): bool
{
    return true;
}';
        $this->assertTrue($method->invoke($this->autoFixService, $publicMethod));

        $privateProperty = 'private $filterList = [\'name\', \'email\'];';
        $this->assertTrue($method->invoke($this->autoFixService, $privateProperty));

        $protectedMethod = 'protected function validateInput($input)
{
    return true;
}';
        $this->assertTrue($method->invoke($this->autoFixService, $protectedMethod));

        $trait = 'use SomeTraitName;';
        $this->assertTrue($method->invoke($this->autoFixService, $trait));

        // Not class member code
        $docblock = '/**
 * Class docblock
 */';
        $this->assertFalse($method->invoke($this->autoFixService, $docblock));

        $namespace = 'namespace App\Models;';
        $this->assertFalse($method->invoke($this->autoFixService, $namespace));
    }

    /** @test */
    public function it_correctly_detects_combined_docblock_and_class()
    {
        $method = $this->reflection->getMethod('isCombinedDocblockAndClass');
        $method->setAccessible(true);

        // Valid combined patterns
        $combined1 = '/**
 * Class TestFilter
 *
 * @package App\Models\Filters
 */
class TestFilter extends BaseFilter';
        $this->assertTrue($method->invoke($this->autoFixService, $combined1));

        $combined2 = '/**
 * Filter class
 */
abstract class AbstractFilter';
        $this->assertTrue($method->invoke($this->autoFixService, $combined2));

        // Not combined patterns
        $docblockOnly = '/**
 * Class TestFilter
 */';
        $this->assertFalse($method->invoke($this->autoFixService, $docblockOnly));

        $classOnly = 'class TestFilter extends BaseFilter';
        $this->assertFalse($method->invoke($this->autoFixService, $classOnly));

        $methodDocblock = '/**
 * @return Builder
 */';
        $this->assertFalse($method->invoke($this->autoFixService, $methodDocblock));
    }

    /** @test */
    public function it_correctly_extracts_docblock_from_combined_code()
    {
        $method = $this->reflection->getMethod('extractDocblockOnly');
        $method->setAccessible(true);

        $combinedCode = '/**
 * Class TestFilter
 *
 * @package App\Models\Filters
 */
class TestFilter extends BaseFilter';

        $expected = '/**
 * Class TestFilter
 *
 * @package App\Models\Filters
 */';

        $result = $method->invoke($this->autoFixService, $combinedCode);
        $this->assertEquals($expected, $result);

        // If it's just a docblock, should return as-is
        $docblockOnly = '/**
 * Method docblock
 */';
        $result = $method->invoke($this->autoFixService, $docblockOnly);
        $this->assertEquals($docblockOnly, $result);
    }

    /** @test */
    public function it_parses_valid_json_responses_correctly()
    {
        $method = $this->reflection->getMethod('parseAiFixData');
        $method->setAccessible(true);

        $validJson = '{
    "code": "/**\\n * Class TestFilter\\n */",
    "explanation": "Added class docblock",
    "confidence": 0.9,
    "safe_to_automate": true,
    "affected_lines": [7],
    "type": "replace"
}';

        $result = $method->invoke($this->autoFixService, $validJson);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('explanation', $result);
        $this->assertArrayHasKey('confidence', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('replace', $result['type']);
        $this->assertEquals(0.9, $result['confidence']);
    }

    /** @test */
    public function it_handles_json_with_control_characters()
    {
        $method = $this->reflection->getMethod('parseAiFixData');
        $method->setAccessible(true);

        $jsonWithControlChars = '{
    "code": "/**\\n * Class TestFilter\\n *\\n * @package App\\\\Models\\\\Filters\\n */",
    "explanation": "Added class docblock with escapes",
    "confidence": 0.9,
    "safe_to_automate": true,
    "affected_lines": [7],
    "type": "replace"
}';

        $result = $method->invoke($this->autoFixService, $jsonWithControlChars);

        $this->assertNotNull($result);
        $this->assertStringContainsString('Class TestFilter', $result['code']);
        $this->assertStringContainsString('@package App\\Models\\Filters', $result['code']);
    }

    /** @test */
    public function it_returns_null_for_invalid_json()
    {
        $method = $this->reflection->getMethod('parseAiFixData');
        $method->setAccessible(true);

        $invalidJsons = [
            'This is not JSON',
            '{"incomplete": true',
            '{malformed json}',
            '',
            'null',
            '{"code": }', // Missing value
        ];

        foreach ($invalidJsons as $invalidJson) {
            $result = $method->invoke($this->autoFixService, $invalidJson);
            $this->assertNull($result, "Should return null for invalid JSON: {$invalidJson}");
        }
    }

    /** @test */
    public function it_applies_proper_indentation_to_code()
    {
        $method = $this->reflection->getMethod('applyIndentation');
        $method->setAccessible(true);

        $code = '/**
 * Class docblock
 */';
        $baseIndent = '    '; // 4 spaces

        $result = $method->invoke($this->autoFixService, $code, $baseIndent);

        $lines = explode("\n", $result);
        foreach ($lines as $line) {
            if (trim($line) !== '') { // Skip empty lines
                $this->assertStringStartsWith($baseIndent, $line, "Line should start with base indentation: {$line}");
            }
        }
    }

    /** @test */
    public function it_finds_class_indentation_correctly()
    {
        $method = $this->reflection->getMethod('findClassIndentation');
        $method->setAccessible(true);

        $lines = [
            '<?php',
            '',
            'namespace App\\Models;',
            '',
            'class TestFilter',
            '{',
            '    private $field;',
            '    ',
            '    public function test()',
            '    {',
            '        return true;',
            '    }',
            '}'
        ];

        // Should find 4-space indentation for class members
        $result = $method->invoke($this->autoFixService, $lines, 9); // Line with method
        $this->assertEquals('    ', $result);

        $result = $method->invoke($this->autoFixService, $lines, 6); // Line with property
        $this->assertEquals('    ', $result);
    }

    /** @test */
    public function it_handles_deeply_nested_class_structures()
    {
        $method = $this->reflection->getMethod('findClassIndentation');
        $method->setAccessible(true);

        $lines = [
            '<?php',
            'namespace App\\Models;',
            'class OuterClass',
            '{',
            '    class InnerClass', // Nested class (rare but possible)
            '    {',
            '        private $field;',
            '        public function method()',
            '        {',
            '            return true;',
            '        }',
            '    }',
            '}'
        ];

        // Should find the correct indentation for the inner class
        $result = $method->invoke($this->autoFixService, $lines, 7); // Line with method in inner class
        $this->assertEquals('        ', $result); // 8 spaces for inner class members
    }
}