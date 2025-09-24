<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Quality;

/**
 * Analyzes code for naming convention issues
 * 
 * This rule checks for:
 * - Non-descriptive variable names (single letters)
 * - Snake case variables (should be camelCase)
 * - Class naming conventions (should be PascalCase)
 */
class NamingConventionsRule extends AbstractQualityRule
{
    /**
     * Analyze code for naming convention issues
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        $lines = $this->getLines($content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            $this->checkNonDescriptiveVariables($filePath, $lineNumber, $line, $content);
            $this->checkSnakeCaseVariables($filePath, $lineNumber, $line, $content);
            $this->checkClassNaming($filePath, $lineNumber, $line, $content);
        }
        
        return $this->getIssues();
    }

    /**
     * Check for non-descriptive variable names
     */
    protected function checkNonDescriptiveVariables(string $filePath, int $lineNumber, string $line, string $content): void
    {
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
    }

    /**
     * Check for snake_case variables (should use camelCase)
     */
    protected function checkSnakeCaseVariables(string $filePath, int $lineNumber, string $line, string $content): void
    {
        if (preg_match('/\\$([a-z]+_[a-z_]+)/', $line, $matches)) {
            $variableName = $matches[1];
            
            // Skip if variable has legitimate snake_case usage
            if (!$this->isLegitimateSnakeCaseUsage($variableName, $line, $content)) {
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
        }
    }

    /**
     * Check class naming conventions
     */
    protected function checkClassNaming(string $filePath, int $lineNumber, string $line, string $content): void
    {
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