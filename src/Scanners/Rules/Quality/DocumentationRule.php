<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Quality;

/**
 * Analyzes code for documentation issues
 * 
 * This rule checks for:
 * - Missing PHPDoc comments on public methods
 * - Missing class-level documentation
 */
class DocumentationRule extends AbstractQualityRule
{
    /**
     * Analyze code for documentation issues
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        $lines = $this->getLines($content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            $this->checkPublicMethodDocumentation($filePath, $lineNumber, $line, $lines, $content);
            $this->checkClassDocumentation($filePath, $lineNumber, $line, $lines, $content);
        }
        
        return $this->getIssues();
    }

    /**
     * Check for public methods without docblocks
     */
    protected function checkPublicMethodDocumentation(string $filePath, int $lineNumber, string $line, array $lines, string $content): void
    {
        if (preg_match('/public\\s+function\\s+\\w+/', $line)) {
            if (!$this->hasDocblockBefore($lines, $lineNumber - 1)) {
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
    }

    /**
     * Check for classes without docblocks
     */
    protected function checkClassDocumentation(string $filePath, int $lineNumber, string $line, array $lines, string $content): void
    {
        if (preg_match('/class\\s+\\w+/', $line)) {
            if (!$this->hasDocblockBefore($lines, $lineNumber - 1)) {
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

    /**
     * Check if there's a docblock in the previous lines
     */
    protected function hasDocblockBefore(array $lines, int $currentIndex): bool
    {
        // Look for docblock in previous 5 lines
        for ($i = max(0, $currentIndex - 5); $i < $currentIndex; $i++) {
            if (isset($lines[$i]) && preg_match('/\\/\\*\\*/', $lines[$i])) {
                return true;
            }
        }
        return false;
    }
}