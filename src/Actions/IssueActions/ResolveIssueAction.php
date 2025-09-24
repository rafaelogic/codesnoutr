<?php

namespace Rafaelogic\CodeSnoutr\Actions\IssueActions;

use Rafaelogic\CodeSnoutr\Contracts\Issues\IssueActionInterface;
use Rafaelogic\CodeSnoutr\Models\Issue;

class ResolveIssueAction implements IssueActionInterface
{
    public function execute(Issue $issue): array
    {
        try {
            $issue->update([
                'fixed' => true,
                'fixed_at' => now(),
                'fix_method' => 'manual'
            ]);

            return [
                'success' => true,
                'message' => 'Issue resolved successfully',
                'data' => [
                    'issueId' => $issue->id,
                    'filePath' => $issue->file_path
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to resolve issue: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function getDescription(): string
    {
        return 'Mark issue as resolved';
    }

    public function canExecute(Issue $issue): bool
    {
        return !$issue->fixed;
    }
}