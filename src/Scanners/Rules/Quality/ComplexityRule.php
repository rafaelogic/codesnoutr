<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Quality;

/**
 * Analyzes code for complexity issues
 * 
 * This rule checks for:
 * - Deep nesting (too many indentation levels)
 * - Complex conditional statements (too many logical operators)
 * - Long parameter lists in function definitions
 */
class ComplexityRule extends AbstractQualityRule
{
    /**
     * Analyze code for complexity issues
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        $lines = $this->getLines($content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            $this->checkNestingDepth($filePath, $lineNumber, $line, $content);
            $this->checkComplexConditionals($filePath, $lineNumber, $line, $content);
            $this->checkLongParameterLists($filePath, $lineNumber, $line, $content);
        }
        
        return $this->getIssues();
    }

    /**
     * Check for deeply nested code
     */
    protected function checkNestingDepth(string $filePath, int $lineNumber, string $line, string $content): void
    {
        $indentLevel = strlen($line) - strlen(ltrim($line));
        if ($indentLevel > 24) { // More than 6 levels of indentation (4 spaces each)
            $this->addIssue($this->createIssue(
                $filePath,
                $lineNumber,
                'quality',
                'warning',
                'quality.deep_nesting',
                'Deep Nesting',
                'Code is deeply nested which can hurt readability and maintainability.',
                'Consider extracting methods or using early returns to reduce nesting.',
                $this->getCodeContext($content, $lineNumber)
            ));
        }
    }

    /**
     * Check for complex conditional statements
     */
    protected function checkComplexConditionals(string $filePath, int $lineNumber, string $line, string $content): void
    {
        $conditionCount = substr_count($line, '&&') + substr_count($line, '||');
        if ($conditionCount > 3) {
            $this->addIssue($this->createIssue(
                $filePath,
                $lineNumber,
                'quality',
                'warning',
                'quality.complex_condition',
                'Complex Conditional',
                'Conditional statement has too many logical operators.',
                'Consider breaking complex conditions into multiple simpler conditions or extract to variables.',
                $this->getCodeContext($content, $lineNumber)
            ));
        }
    }

    /**
     * Check for functions with long parameter lists
     */
    protected function checkLongParameterLists(string $filePath, int $lineNumber, string $line, string $content): void
    {
        if (preg_match('/function\\s+\\w+\\s*\\([^)]{80,}\\)/', $line)) {
            $this->addIssue($this->createIssue(
                $filePath,
                $lineNumber,
                'quality',
                'warning',
                'quality.long_parameter_list',
                'Long Parameter List',
                'Function has a very long parameter list which can be hard to maintain.',
                'Consider using parameter objects or reducing the number of parameters.',
                $this->getCodeContext($content, $lineNumber)
            ));
        }
    }
}