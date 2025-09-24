<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Quality;

use Rafaelogic\CodeSnoutr\Scanners\Rules\AbstractRuleEngine;

/**
 * Quality Rule Engine
 * 
 * Coordinates all quality rules and combines their results.
 * This replaces the monolithic QualityRules class with a modular approach.
 */
class QualityRuleEngine extends AbstractRuleEngine
{
    protected array $rules = [];

    public function __construct()
    {
        $this->rules = [
            new CodingStandardsRule(),
            new ComplexityRule(),
            new DocumentationRule(),
            new NamingConventionsRule(),
            new BestPracticesRule(),
        ];
    }

    /**
     * Analyze code using all quality rules
     *
     * @param string $filePath The path to the file being analyzed
     * @param array $ast The AST representation (not used in quality rules)
     * @param string $content The file content as string
     * @return array Array of all issues found by all rules
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        
        foreach ($this->rules as $rule) {
            $ruleIssues = $rule->analyze($filePath, $ast, $content);
            foreach ($ruleIssues as $issue) {
                $this->addIssue($issue);
            }
        }
        
        return $this->getIssues();
    }

    /**
     * Get the individual rules for testing or configuration
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Add a custom rule to the engine
     *
     * @param AbstractQualityRule $rule
     * @return void
     */
    public function addRule(AbstractQualityRule $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * Remove a rule by class name
     *
     * @param string $ruleClassName
     * @return bool True if rule was found and removed, false otherwise
     */
    public function removeRule(string $ruleClassName): bool
    {
        foreach ($this->rules as $index => $rule) {
            if (get_class($rule) === $ruleClassName) {
                unset($this->rules[$index]);
                $this->rules = array_values($this->rules); // Reindex
                return true;
            }
        }
        return false;
    }
}