<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules;

use PhpParser\Node;

abstract class AbstractRuleEngine
{
    protected array $rules = [];
    protected array $issues = [];

    /**
     * Analyze code and return issues
     */
    abstract public function analyze(string $filePath, array $ast, string $content): array;

    /**
     * Add an issue
     */
    protected function addIssue(array $issue): void
    {
        $this->issues[] = $issue;
    }

    /**
     * Get all issues found
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    /**
     * Clear issues
     */
    public function clearIssues(): void
    {
        $this->issues = [];
    }

    /**
     * Create a standardized issue array
     */
    protected function createIssue(
        string $filePath,
        int $lineNumber,
        string $category,
        string $severity,
        string $ruleId,
        string $title,
        string $description,
        string $suggestion,
        array $context = [],
        array $metadata = []
    ): array {
        return [
            'file_path' => $filePath,
            'line_number' => $lineNumber,
            'column_number' => $context['column'] ?? null,
            'category' => $category,
            'severity' => $severity,
            'rule_name' => $this->getRuleName($ruleId),
            'rule_id' => $ruleId,
            'title' => $title,
            'description' => $description,
            'suggestion' => $suggestion,
            'context' => $context,
            'metadata' => $metadata,
        ];
    }

    /**
     * Get human-readable rule name from rule ID
     */
    protected function getRuleName(string $ruleId): string
    {
        $parts = explode('.', $ruleId);
        $ruleName = end($parts);
        
        // Convert snake_case to Title Case
        return ucwords(str_replace('_', ' ', $ruleName));
    }

    /**
     * Get line number from AST node
     */
    protected function getLineNumber(Node $node): int
    {
        return $node->getStartLine() ?? 1;
    }

    /**
     * Get code context around a line
     */
    protected function getCodeContext(string $content, int $lineNumber, int $contextLines = 3): array
    {
        $lines = explode("\n", $content);
        $start = max(0, $lineNumber - $contextLines - 1);
        $end = min(count($lines), $lineNumber + $contextLines);

        $context = [];
        for ($i = $start; $i < $end; $i++) {
            $context[$i + 1] = $lines[$i] ?? '';
        }

        return $context;
    }

    /**
     * Check if node contains dangerous patterns
     */
    protected function containsDangerousPattern(Node $node, array $patterns): bool
    {
        $nodeContent = $this->nodeToString($node);
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $nodeContent)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Convert AST node to string (simplified)
     */
    protected function nodeToString(Node $node): string
    {
        // This is a simplified implementation
        // In practice, you'd use a proper pretty printer
        return get_class($node) . ' at line ' . $this->getLineNumber($node);
    }

    /**
     * Check if variable name follows naming convention
     */
    protected function followsNamingConvention(string $name, string $convention = 'camelCase'): bool
    {
        return match ($convention) {
            'camelCase' => preg_match('/^[a-z][a-zA-Z0-9]*$/', $name),
            'snake_case' => preg_match('/^[a-z][a-z0-9_]*$/', $name),
            'PascalCase' => preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name),
            default => true,
        };
    }

    /**
     * Calculate cyclomatic complexity for a function/method
     */
    protected function calculateComplexity(Node $node): int
    {
        $complexity = 1; // Base complexity
        
        // This would need to traverse the AST and count decision points
        // For now, return a placeholder
        return $complexity;
    }

    /**
     * Check if function/method is too long
     */
    protected function isTooLong(Node $node, int $maxLines = 50): bool
    {
        $startLine = $node->getStartLine() ?? 0;
        $endLine = $node->getEndLine() ?? 0;
        
        return ($endLine - $startLine) > $maxLines;
    }

    /**
     * Get severity level from configuration
     */
    protected function getSeverityLevel(string $ruleId, string $defaultSeverity = 'warning'): string
    {
        // This would check configuration for custom severity levels
        return $defaultSeverity;
    }

    /**
     * Check if rule is enabled in configuration
     */
    protected function isRuleEnabled(string $ruleId): bool
    {
        // This would check configuration for enabled rules
        return true;
    }
}
