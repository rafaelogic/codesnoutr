<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules;

use Rafaelogic\CodeSnoutr\Scanners\Rules\Quality\QualityRuleEngine;

/**
 * Quality Rules
 * 
 * This class maintains backward compatibility while delegating to the modular QualityRuleEngine.
 * The actual quality analysis is now handled by individual rule classes.
 */
class QualityRules extends AbstractRuleEngine
{
    protected QualityRuleEngine $engine;

    public function __construct()
    {
        $this->engine = new QualityRuleEngine();
    }

    /**
     * Analyze code for quality issues using the modular rule engine
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        return $this->engine->analyze($filePath, $ast, $content);
    }

    /**
     * Get the underlying rule engine for advanced usage
     */
    public function getEngine(): QualityRuleEngine
    {
        return $this->engine;
    }
}
