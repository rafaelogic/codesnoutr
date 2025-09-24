<?php

namespace Rafaelogic\CodeSnoutr\Actions\IssueActions;

use Rafaelogic\CodeSnoutr\Contracts\Issues\IssueActionInterface;
use Rafaelogic\CodeSnoutr\Models\Issue;

class MarkFalsePositiveAction implements IssueActionInterface
{
    public function execute(Issue $issue): array
    {
        try {
            $issue->update([
                'fixed' => true,
                'fix_method' => 'false_positive'
            ]);

            return [
                'success' => true,
                'message' => 'Issue marked as false positive successfully',
                'data' => [
                    'issueId' => $issue->id,
                    'filePath' => $issue->file_path
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to mark issue as false positive: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function getDescription(): string
    {
        return 'Mark issue as false positive';
    }

    public function canExecute(Issue $issue): bool
    {
        return !$issue->fixed;
    }
}