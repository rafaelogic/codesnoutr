<?php

namespace Rafaelogic\CodeSnoutr\Services\Issues;

use Rafaelogic\CodeSnoutr\Contracts\Issues\IssueFilterServiceInterface;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class IssueFilterService implements IssueFilterServiceInterface
{
    /**
     * Apply filters to issues query
     */
    public function applyFilters($query, array $filters)
    {
        if (!empty($filters['severity']) && $filters['severity'] !== 'all') {
            $query->where('severity', $filters['severity']);
        }

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['file']) && $filters['file'] !== 'all') {
            $query->where('file_path', 'like', '%' . $filters['file'] . '%');
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('file_path', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query;
    }

    /**
     * Get available filter options for a scan
     */
    public function getFilterOptions(Scan $scan): array
    {
        $issues = $scan->issues();

        return [
            'severities' => $issues->distinct('severity')
                ->orderBy('severity')
                ->pluck('severity')
                ->filter()
                ->values()
                ->toArray(),
                
            'categories' => $issues->distinct('category')
                ->orderBy('category')
                ->pluck('category')
                ->filter()
                ->values()
                ->toArray(),
                
            'files' => $issues->distinct('file_path')
                ->orderBy('file_path')
                ->pluck('file_path')
                ->map(function($path) {
                    return basename($path);
                })
                ->filter()
                ->values()
                ->toArray()
        ];
    }

    /**
     * Build filter criteria array
     */
    public function buildFilterCriteria(array $filters): array
    {
        $criteria = [];

        if (!empty($filters['severity']) && $filters['severity'] !== 'all') {
            $criteria['severity'] = $filters['severity'];
        }

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $criteria['category'] = $filters['category'];
        }

        if (!empty($filters['file']) && $filters['file'] !== 'all') {
            $criteria['file_path_like'] = '%' . $filters['file'] . '%';
        }

        if (!empty($filters['search'])) {
            $criteria['search'] = $filters['search'];
        }

        return $criteria;
    }
}