<?php

namespace Rafaelogic\CodeSnoutr\Contracts\Issues;

interface BulkActionServiceInterface
{
    /**
     * Execute bulk action on multiple issues
     */
    public function executeBulkAction(string $action, array $issueIds): array;

    /**
     * Get available bulk actions
     */
    public function getAvailableActions(): array;

    /**
     * Validate bulk action before execution
     */
    public function validateBulkAction(string $action, array $issueIds): array;
}