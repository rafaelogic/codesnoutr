<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Blade\Performance;

use Rafaelogic\CodeSnoutr\Scanners\Rules\Blade\AbstractBladeRule;

class N1QueryRule extends AbstractBladeRule
{
    /**
     * Analyze Blade content for potential N+1 query issues
     */
    protected function analyzeBladeContent(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            $this->checkNestedLoops($filePath, $line, $lineNumber, $content);
            $this->checkModelAccessInLoop($filePath, $line, $lineNumber);
        }
    }

    /**
     * Check for nested loops that might cause N+1 queries
     */
    protected function checkNestedLoops(string $filePath, string $line, int $lineNumber, string $content): void
    {
        // Look for nested foreach loops
        if (preg_match('/@foreach\s*\(\s*\$([^}]+)\s+as\s+/', $line)) {
            // Check if this is inside another loop
            $previousLines = array_slice(explode("\n", $content), 0, $lineNumber - 1);
            $loopDepth = 0;
            
            foreach (array_reverse($previousLines) as $prevLine) {
                if (preg_match('/@foreach/', $prevLine)) {
                    $loopDepth++;
                }
                if (preg_match('/@endforeach/', $prevLine)) {
                    $loopDepth--;
                }
            }
            
            if ($loopDepth > 0) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'performance',
                    'medium',
                    'blade.nested_loops',
                    'Potential N+1 Query in Nested Loop',
                    'Nested foreach loops can cause N+1 query problems if relationships are accessed.',
                    'Consider eager loading relationships using with() in your Eloquent query.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for model relationship access inside loops
     */
    protected function checkModelAccessInLoop(string $filePath, string $line, int $lineNumber): void
    {
        // Look for relationship access patterns like $item->relationship
        if (preg_match('/\{\{\s*\$\w+\->\w+/', $line) && 
            $this->isInsideLoop($filePath, $lineNumber)) {
            
            $this->addIssue($this->createIssue(
                $filePath,
                $lineNumber,
                'performance',
                'medium',
                'blade.relationship_in_loop',
                'Relationship Access in Loop',
                'Accessing model relationships inside loops can cause N+1 queries.',
                'Eager load relationships with with() method in your controller query.',
                []
            ));
        }
    }

    /**
     * Check if current line is inside a loop
     */
    protected function isInsideLoop(string $filePath, int $currentLine): bool
    {
        // This is a simplified check - in a real implementation,
        // you'd want to parse the entire content more carefully
        return true; // Placeholder implementation
    }
}