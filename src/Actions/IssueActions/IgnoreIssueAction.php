<?php

namespace Rafaelogic\CodeSnoutr\Actions\IssueActions;

use Rafaelogic\CodeSnoutr\Contracts\Issues\IssueActionInterface;
use Rafaelogic\CodeSnoutr\Models\Issue;

class IgnoreIssueAction implements IssueActionInterface
{
    public function execute(Issue $issue): array
    {
        try {
            $issue->update([
                'fixed' => true,
                'fix_method' => 'ignored'
            ]);

            return [
                'success' => true,
                'message' => 'Issue ignored successfully',
                'data' => [
                    'issueId' => $issue->id,
                    'filePath' => $issue->file_path
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to ignore issue: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function getDescription(): string
    {
        return 'Mark issue as ignored';
    }

    public function canExecute(Issue $issue): bool
    {
        return !$issue->fixed;
    }
}