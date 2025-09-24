<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Quality;

/**
 * Analyzes code for coding standards violations
 * 
 * This rule checks for:
 * - Line length violations (over 120 characters)
 * - Trailing whitespace
 * - Mixed indentation (tabs and spaces)
 */
class CodingStandardsRule extends AbstractQualityRule
{
    /**
     * Analyze code for coding standards issues
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        $lines = $this->getLines($content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            $this->checkLineLengthLimit($filePath, $lineNumber, $line, $content);
            $this->checkTrailingWhitespace($filePath, $lineNumber, $line, $content);
            $this->checkMixedIndentation($filePath, $lineNumber, $line, $content);
        }
        
        return $this->getIssues();
    }

    /**
     * Check for lines that exceed the maximum length
     */
    protected function checkLineLengthLimit(string $filePath, int $lineNumber, string $line, string $content): void
    {
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
    }

    /**
     * Check for trailing whitespace
     */
    protected function checkTrailingWhitespace(string $filePath, int $lineNumber, string $line, string $content): void
    {
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
    }

    /**
     * Check for mixed indentation (tabs and spaces)
     */
    protected function checkMixedIndentation(string $filePath, int $lineNumber, string $line, string $content): void
    {
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