<?php

namespace Rafaelogic\CodeSnoutr\Services\Issues;

use Rafaelogic\CodeSnoutr\Models\Issue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BulkActionService
{
    /**
     * Perform bulk resolve action on issues
     */
    public function bulkResolve(array $issueIds): array
    {
        try {
            $updated = Issue::whereIn('id', $issueIds)
                ->where('fixed', false)
                ->update([
                    'fixed' => true,
                    'fixed_at' => now(),
                    'fix_method' => 'manual'
                ]);

            Log::info('Bulk resolve completed', [
                'issue_count' => count($issueIds),
                'updated_count' => $updated
            ]);

            return [
                'success' => true,
                'message' => "Successfully resolved {$updated} issues",
                'data' => [
                    'requested_count' => count($issueIds),
                    'updated_count' => $updated
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Bulk resolve failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to resolve issues: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Perform bulk ignore action on issues
     */
    public function bulkIgnore(array $issueIds): array
    {
        try {
            $updated = Issue::whereIn('id', $issueIds)
                ->where('fixed', false)
                ->update([
                    'fixed' => true,
                    'fix_method' => 'ignored'
                ]);

            Log::info('Bulk ignore completed', [
                'issue_count' => count($issueIds),
                'updated_count' => $updated
            ]);

            return [
                'success' => true,
                'message' => "Successfully ignored {$updated} issues",
                'data' => [
                    'requested_count' => count($issueIds),
                    'updated_count' => $updated
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Bulk ignore failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to ignore issues: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Perform bulk false positive action on issues
     */
    public function bulkMarkFalsePositive(array $issueIds): array
    {
        try {
            $updated = Issue::whereIn('id', $issueIds)
                ->where('fixed', false)
                ->update([
                    'fixed' => true,
                    'fix_method' => 'false_positive'
                ]);

            Log::info('Bulk false positive completed', [
                'issue_count' => count($issueIds),
                'updated_count' => $updated
            ]);

            return [
                'success' => true,
                'message' => "Successfully marked {$updated} issues as false positive",
                'data' => [
                    'requested_count' => count($issueIds),
                    'updated_count' => $updated
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Bulk false positive failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to mark issues as false positive: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Perform bulk delete action on issues
     */
    public function bulkDelete(array $issueIds): array
    {
        try {
            $deleted = Issue::whereIn('id', $issueIds)->delete();

            Log::info('Bulk delete completed', [
                'issue_count' => count($issueIds),
                'deleted_count' => $deleted
            ]);

            return [
                'success' => true,
                'message' => "Successfully deleted {$deleted} issues",
                'data' => [
                    'requested_count' => count($issueIds),
                    'deleted_count' => $deleted
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Bulk delete failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to delete issues: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get bulk action options for issues
     */
    public function getAvailableActions(Collection $issues): array
    {
        $unfixedCount = $issues->where('fixed', false)->count();
        $fixedCount = $issues->where('fixed', true)->count();

        $actions = [];

        if ($unfixedCount > 0) {
            $actions['resolve'] = [
                'label' => 'Mark as Resolved',
                'description' => "Mark {$unfixedCount} unfixed issues as resolved",
                'icon' => 'check-circle',
                'count' => $unfixedCount,
                'available' => true
            ];

            $actions['ignore'] = [
                'label' => 'Ignore Issues',
                'description' => "Ignore {$unfixedCount} unfixed issues",
                'icon' => 'eye-slash',
                'count' => $unfixedCount,
                'available' => true
            ];

            $actions['false_positive'] = [
                'label' => 'Mark False Positive',
                'description' => "Mark {$unfixedCount} unfixed issues as false positive",
                'icon' => 'exclamation-triangle',
                'count' => $unfixedCount,
                'available' => true
            ];
        }

        $actions['delete'] = [
            'label' => 'Delete Issues',
            'description' => "Permanently delete {$issues->count()} selected issues",
            'icon' => 'trash',
            'count' => $issues->count(),
            'available' => true,
            'destructive' => true
        ];

        return $actions;
    }

    /**
     * Validate bulk action request
     */
    public function validateBulkAction(string $action, array $issueIds): array
    {
        if (empty($issueIds)) {
            return [
                'valid' => false,
                'message' => 'No issues selected for bulk action'
            ];
        }

        $validActions = ['resolve', 'ignore', 'false_positive', 'delete'];
        if (!in_array($action, $validActions)) {
            return [
                'valid' => false,
                'message' => 'Invalid bulk action specified'
            ];
        }

        // Check if issues exist
        $existingCount = Issue::whereIn('id', $issueIds)->count();
        if ($existingCount !== count($issueIds)) {
            return [
                'valid' => false,
                'message' => 'Some selected issues no longer exist'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Bulk action is valid'
        ];
    }

    /**
     * Execute bulk action based on type
     */
    public function executeBulkAction(string $action, array $issueIds): array
    {
        $validation = $this->validateBulkAction($action, $issueIds);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message'],
                'data' => null
            ];
        }

        switch ($action) {
            case 'resolve':
                return $this->bulkResolve($issueIds);
                
            case 'ignore':
                return $this->bulkIgnore($issueIds);
                
            case 'false_positive':
                return $this->bulkMarkFalsePositive($issueIds);
                
            case 'delete':
                return $this->bulkDelete($issueIds);
                
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown bulk action',
                    'data' => null
                ];
        }
    }
}