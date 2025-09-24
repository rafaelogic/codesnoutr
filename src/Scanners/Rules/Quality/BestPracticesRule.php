<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Quality;

/**
 * Analyzes code for best practice violations
 * 
 * This rule checks for:
 * - Potentially unused variables
 * - Magic numbers that should be constants
 * - Empty catch blocks
 * - TODO/FIXME/HACK comments
 */
class BestPracticesRule extends AbstractQualityRule
{
    /**
     * Analyze code for best practice violations
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        $lines = $this->getLines($content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            $this->checkUnusedVariables($filePath, $lineNumber, $line, $content);
            $this->checkMagicNumbers($filePath, $lineNumber, $line, $content);
            $this->checkEmptyCatchBlocks($filePath, $lineNumber, $line, $content);
            $this->checkTodoComments($filePath, $lineNumber, $line, $content);
        }
        
        return $this->getIssues();
    }

    /**
     * Check for potentially unused variables
     */
    protected function checkUnusedVariables(string $filePath, int $lineNumber, string $line, string $content): void
    {
        if (preg_match('/\\$([a-zA-Z_][a-zA-Z0-9_]*)\\s*=/', $line, $matches) && 
            !preg_match('/\\$this->/', $line)) {
            
            $variableName = $matches[1];
            
            // Skip if this is a known exception
            if ($this->isVariableException($variableName, $filePath, $content, $lineNumber)) {
                return;
            }
            
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
    }

    /**
     * Check for magic numbers that should be constants
     */
    protected function checkMagicNumbers(string $filePath, int $lineNumber, string $line, string $content): void
    {
        if (preg_match('/[^\\w]([0-9]{2,})[^\\w]/', $line, $matches)) {
            $number = $matches[1];
            
            if ($this->shouldSkipMagicNumber($number, $line, $filePath)) {
                return;
            }
            
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

    /**
     * Check for empty catch blocks
     */
    protected function checkEmptyCatchBlocks(string $filePath, int $lineNumber, string $line, string $content): void
    {
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
    }

    /**
     * Check for TODO/FIXME/HACK comments
     */
    protected function checkTodoComments(string $filePath, int $lineNumber, string $line, string $content): void
    {
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

    /**
     * Determine if a magic number should be skipped
     */
    protected function shouldSkipMagicNumber(string $number, string $line, string $filePath): bool
    {
        // Skip common legitimate numbers
        if (in_array($number, ['100', '200', '404', '500', '9999'])) {
            return true;
        }
        
        // Skip HTML attributes (width, height, size, etc.)
        if (preg_match('/\b(width|height|size|maxlength|min|max|step|rows|cols|tabindex|colspan|rowspan|scale)=[\'"]\d+[\'"]/', $line)) {
            return true;
        }
        
        // Skip CSS properties (z-index, font-size, width, height, etc.)
        if (preg_match('/\b(z-index|font-size|line-height|width|height|margin|padding|top|right|bottom|left|opacity|order|flex|grid|border-radius|font-weight):\s*\d+/', $line)) {
            return true;
        }
        
        // Skip CSS units (px, em, rem, %, vh, vw, etc.)
        if (preg_match('/\d+(px|em|rem|%|vh|vw|pt|pc|in|cm|mm|ex|ch|vmin|vmax)/', $line)) {
            return true;
        }
        
        // Skip HTML color values (#ffffff, rgb(255,255,255), etc.)
        if (preg_match('/#[0-9a-fA-F]+|rgb\(|rgba\(|hsl\(|hsla\(/', $line)) {
            return true;
        }
        
        // Skip dates and times (YYYY-MM-DD, timestamps, etc.)
        if (preg_match('/\d{4}-\d{2}-\d{2}|\d{4}_\d{2}_\d{2}|\d{10,}/', $line)) {
            return true;
        }
        
        // Skip Blade template context (only flag PHP code)
        if (str_ends_with($filePath, '.blade.php')) {
            // Only check within @php blocks or PHP variables
            if (!preg_match('/@php|<\?php|\$\w+\s*=/', $line)) {
                return true;
            }
        }
        
        return false;
    }
}