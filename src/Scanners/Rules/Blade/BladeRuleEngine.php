<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Blade;

use Rafaelogic\CodeSnoutr\Scanners\Rules\AbstractRuleEngine;
use Rafaelogic\CodeSnoutr\Scanners\Rules\Blade\Security\{
    XSSVulnerabilityRule,
    CSRFProtectionRule,
    InputSanitizationRule
};
use Rafaelogic\CodeSnoutr\Scanners\Rules\Blade\Performance\{
    N1QueryRule
};

class BladeRuleEngine extends AbstractRuleEngine
{
    protected array $ruleInstances = [];

    public function __construct()
    {
        $this->initializeRules();
    }

    /**
     * Initialize all blade rule instances
     */
    protected function initializeRules(): void
    {
        // Security rules
        $this->ruleInstances[] = new XSSVulnerabilityRule();
        $this->ruleInstances[] = new CSRFProtectionRule();
        $this->ruleInstances[] = new InputSanitizationRule();
        
        // Performance rules
        $this->ruleInstances[] = new N1QueryRule();
        
        // TODO: Add other rule categories as they are implemented
        // Quality rules, Best Practice rules, Accessibility rules, etc.
    }

    /**
     * Analyze Blade template with all registered rules
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        // Only analyze Blade templates
        if (!str_ends_with($filePath, '.blade.php')) {
            return [];
        }

        $this->clearIssues();
        
        // Run each rule against the content
        foreach ($this->ruleInstances as $rule) {
            $ruleIssues = $rule->analyze($filePath, $ast, $content);
            foreach ($ruleIssues as $issue) {
                $this->addIssue($issue);
            }
        }
        
        return $this->getIssues();
    }

    /**
     * Get all registered rules
     */
    public function getRules(): array
    {
        return array_map(function($rule) {
            return get_class($rule);
        }, $this->ruleInstances);
    }

    /**
     * Add a custom rule
     */
    public function addRule(AbstractBladeRule $rule): void
    {
        $this->ruleInstances[] = $rule;
    }

    /**
     * Remove a rule by class name
     */
    public function removeRule(string $ruleClass): void
    {
        $this->ruleInstances = array_filter($this->ruleInstances, function($rule) use ($ruleClass) {
            return !($rule instanceof $ruleClass);
        });
    }

    /**
     * Enable/disable specific rule categories
     */
    public function filterRulesByCategory(array $enabledCategories): void
    {
        $categoryMap = [
            'security' => 'Security',
            'performance' => 'Performance',
            'quality' => 'Quality',
            'bestpractices' => 'BestPractices',
            'accessibility' => 'Accessibility',
            'seo' => 'SEO',
            'maintainability' => 'Maintainability'
        ];

        $this->ruleInstances = array_filter($this->ruleInstances, function($rule) use ($enabledCategories, $categoryMap) {
            $ruleClass = get_class($rule);
            
            foreach ($enabledCategories as $category) {
                $categoryNamespace = $categoryMap[$category] ?? ucfirst($category);
                if (str_contains($ruleClass, '\\' . $categoryNamespace . '\\')) {
                    return true;
                }
            }
            
            return false;
        });
    }
}