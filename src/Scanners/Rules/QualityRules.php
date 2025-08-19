<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules;

class QualityRules extends AbstractRuleEngine
{
    /**
     * Analyze code for quality issues
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        
        // Check coding standards
        $this->checkCodingStandards($filePath, $content);
        
        // Check complexity issues
        $this->checkComplexity($filePath, $content);
        
        // Check documentation
        $this->checkDocumentation($filePath, $content);
        
        // Check naming conventions
        $this->checkNamingConventions($filePath, $content);
        
        // Check best practices
        $this->checkBestPractices($filePath, $content);
        
        return $this->getIssues();
    }

    /**
     * Check coding standards
     */
    protected function checkCodingStandards(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for long lines
            if (strlen($line) > 120) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'info',
                    'quality.long_line',
                    'Line Too Long',
                    'Line exceeds 120 characters which can hurt readability.',
                    'Consider breaking the line into multiple shorter lines.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for trailing whitespace
            if (preg_match('/\\s+$/', $line) && !empty(trim($line))) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'info',
                    'quality.trailing_whitespace',
                    'Trailing Whitespace',
                    'Line has trailing whitespace characters.',
                    'Remove trailing whitespace for cleaner code.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for mixed indentation
            if (preg_match('/^\\t+ +/', $line) || preg_match('/^ +\\t+/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'warning',
                    'quality.mixed_indentation',
                    'Mixed Indentation',
                    'Line uses mixed tabs and spaces for indentation.',
                    'Use consistent indentation (either tabs or spaces, not both).',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check complexity issues
     */
    protected function checkComplexity(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for deeply nested conditions
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
            
            // Check for complex conditionals
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
            
            // Check for long parameter lists
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

    /**
     * Check documentation
     */
    protected function checkDocumentation(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for public methods without docblocks
            if (preg_match('/public\\s+function\\s+\\w+/', $line)) {
                // Look for docblock in previous lines
                $hasDocblock = false;
                for ($i = max(0, $lineNumber - 5); $i < $lineNumber - 1; $i++) {
                    if (isset($lines[$i]) && preg_match('/\\/\\*\\*/', $lines[$i])) {
                        $hasDocblock = true;
                        break;
                    }
                }
                
                if (!$hasDocblock) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'quality.missing_docblock',
                        'Missing Documentation',
                        'Public method lacks proper documentation.',
                        'Add a PHPDoc comment describing the method, parameters, and return value.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for class without docblock
            if (preg_match('/class\\s+\\w+/', $line)) {
                // Look for docblock in previous lines
                $hasDocblock = false;
                for ($i = max(0, $lineNumber - 5); $i < $lineNumber - 1; $i++) {
                    if (isset($lines[$i]) && preg_match('/\\/\\*\\*/', $lines[$i])) {
                        $hasDocblock = true;
                        break;
                    }
                }
                
                if (!$hasDocblock) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'quality.missing_class_docblock',
                        'Missing Class Documentation',
                        'Class lacks proper documentation.',
                        'Add a PHPDoc comment describing the class purpose and usage.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
        }
    }

    /**
     * Check naming conventions
     */
    protected function checkNamingConventions(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for non-descriptive variable names
            if (preg_match('/\\$([a-z])\\s*=/', $line, $matches)) {
                $varName = $matches[1];
                if (strlen($varName) == 1 && !in_array($varName, ['i', 'j', 'k', 'x', 'y', 'z'])) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'quality.non_descriptive_variable',
                        'Non-descriptive Variable Name',
                        'Single letter variable names (except loop counters) reduce code readability.',
                        'Use descriptive variable names that clearly indicate their purpose.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for camelCase in variable names
            if (preg_match('/\\$[a-z]+_[a-z_]+/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'info',
                    'quality.snake_case_variable',
                    'Snake Case Variable',
                    'PHP conventions prefer camelCase for variable names.',
                    'Use camelCase naming: $userName instead of $user_name',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for PascalCase in class names
            if (preg_match('/class\\s+([a-z][a-zA-Z0-9_]*)/', $line, $matches)) {
                $className = $matches[1];
                if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $className)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'warning',
                        'quality.class_naming',
                        'Class Naming Convention',
                        'Class names should follow PascalCase convention.',
                        'Use PascalCase for class names: MyClass instead of myClass or my_class',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
        }
    }

    /**
     * Check best practices
     */
    protected function checkBestPractices(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for unused variables
            if (preg_match('/\\$\\w+\\s*=/', $line) && 
                !preg_match('/\\$this->/', $line)) {
                // This is a simple check - a more sophisticated implementation would track variable usage
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'info',
                    'quality.potential_unused_variable',
                    'Potential Unused Variable',
                    'Variable assignment detected - ensure it is used.',
                    'Remove unused variables or use them appropriately in your code.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for magic numbers
            if (preg_match('/[^\\w]([0-9]{2,})[^\\w]/', $line, $matches)) {
                $number = $matches[1];
                if ($number != '100' && $number != '200' && $number != '404') { // Common HTTP codes
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'quality.magic_number',
                        'Magic Number',
                        'Hard-coded numbers make code less maintainable.',
                        'Consider using named constants for magic numbers.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for empty catch blocks
            if (preg_match('/catch\\s*\\([^)]+\\)\\s*\\{\\s*\\}/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'warning',
                    'quality.empty_catch_block',
                    'Empty Catch Block',
                    'Empty catch blocks hide exceptions and make debugging difficult.',
                    'Handle the exception appropriately or at least log it.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for TODO comments
            if (preg_match('/(TODO|FIXME|HACK)/i', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'info',
                    'quality.todo_comment',
                    'TODO Comment',
                    'Code contains TODO, FIXME, or HACK comments.',
                    'Address these comments or create proper tickets for future work.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }
}
