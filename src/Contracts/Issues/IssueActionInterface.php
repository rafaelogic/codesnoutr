<?php

namespace Rafaelogic\CodeSnoutr\Contracts\Issues;

use Rafaelogic\CodeSnoutr\Models\Issue;

interface IssueActionInterface
{
    /**
     * Execute the action
     */
    public function execute(Issue $issue): array;

    /**
     * Get action description
     */
    public function getDescription(): string;

    /**
     * Validate if action can be executed
     */
    public function canExecute(Issue $issue): bool;
}