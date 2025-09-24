<?php

namespace Rafaelogic\CodeSnoutr\Contracts\Issues;

use Rafaelogic\CodeSnoutr\Models\Scan;
use Illuminate\Support\Collection;

interface IssueFilterServiceInterface
{
    /**
     * Get available filter options for a scan
     */
    public function getFilterOptions(Scan $scan): array;

    /**
     * Apply filters to issues query
     */
    public function applyFilters($query, array $filters);

    /**
     * Build filter criteria array
     */
    public function buildFilterCriteria(array $filters): array;
}