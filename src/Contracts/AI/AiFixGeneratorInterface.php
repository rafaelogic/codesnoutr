<?php

namespace Rafaelogic\CodeSnoutr\Contracts\AI;

use Rafaelogic\CodeSnoutr\Models\Issue;

interface AiFixGeneratorInterface
{
    /**
     * Generate AI fix for an issue
     */
    public function generateFix(Issue $issue): array;

    /**
     * Check if AI fix generation is available
     */
    public function isAvailable(): bool;

    /**
     * Get AI fix generation configuration
     */
    public function getConfiguration(): array;

    /**
     * Validate issue before generating fix
     */
    public function canGenerateFix(Issue $issue): bool;
}