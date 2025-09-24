<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules;

use Rafaelogic\CodeSnoutr\Scanners\Rules\Blade\BladeRuleEngine;

/**
 * Blade Template Rules Engine
 * 
 * Refactored to use individual rule classes for better maintainability.
 * This class now acts as a facade for the BladeRuleEngine.
 */
class BladeRules extends AbstractRuleEngine
{
    protected BladeRuleEngine $engine;

    public function __construct()
    {
        $this->engine = new BladeRuleEngine();
    }

    /**
     * Analyze Blade template
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        return $this->engine->analyze($filePath, $ast, $content);
    }

    /**
     * Get all registered rules
     */
    public function getRules(): array
    {
        return $this->engine->getRules();
    }

    /**
     * Filter rules by category
     */
    public function filterRulesByCategory(array $enabledCategories): void
    {
        $this->engine->filterRulesByCategory($enabledCategories);
    }
}