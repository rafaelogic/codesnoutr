<?php

namespace Rafaelogic\CodeSnoutr\Services\Issues;

use Rafaelogic\CodeSnoutr\Contracts\Issues\IssueActionInterface;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Actions\IssueActions\ResolveIssueAction;
use Rafaelogic\CodeSnoutr\Actions\IssueActions\IgnoreIssueAction;
use Rafaelogic\CodeSnoutr\Actions\IssueActions\MarkFalsePositiveAction;
use Rafaelogic\CodeSnoutr\Actions\IssueActions\GenerateAiFixAction;
use Rafaelogic\CodeSnoutr\Actions\IssueActions\ApplyAiFixAction;
use Illuminate\Support\Facades\Log;

class IssueActionInvoker
{
    protected array $actions = [];

    public function __construct(
        ResolveIssueAction $resolveAction,
        IgnoreIssueAction $ignoreAction,
        MarkFalsePositiveAction $falsePositiveAction,
        GenerateAiFixAction $generateAiFixAction,
        ApplyAiFixAction $applyAiFixAction
    ) {
        $this->registerAction('resolve', $resolveAction);
        $this->registerAction('ignore', $ignoreAction);
        $this->registerAction('false_positive', $falsePositiveAction);
        $this->registerAction('generate_ai_fix', $generateAiFixAction);
        $this->registerAction('apply_ai_fix', $applyAiFixAction);
    }

    /**
     * Register an action
     */
    public function registerAction(string $name, IssueActionInterface $action): void
    {
        $this->actions[$name] = $action;
    }

    /**
     * Execute an action on an issue
     */
    public function executeAction(string $actionName, Issue $issue): array
    {
        try {
            if (!isset($this->actions[$actionName])) {
                return [
                    'success' => false,
                    'message' => "Unknown action: {$actionName}",
                    'data' => null
                ];
            }

            $action = $this->actions[$actionName];

            if (!$action->canExecute($issue)) {
                return [
                    'success' => false,
                    'message' => "Action '{$actionName}' cannot be executed on this issue",
                    'data' => null
                ];
            }

            $result = $action->execute($issue);
            
            // Log the action
            Log::info("Issue action executed", [
                'action' => $actionName,
                'issue_id' => $issue->id,
                'success' => $result['success'],
                'message' => $result['message']
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error("Failed to execute action '{$actionName}' on issue {$issue->id}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => "Failed to execute action: {$e->getMessage()}",
                'data' => null
            ];
        }
    }

    /**
     * Get available actions for an issue
     */
    public function getAvailableActions(Issue $issue): array
    {
        $available = [];
        
        foreach ($this->actions as $name => $action) {
            if ($action->canExecute($issue)) {
                $available[$name] = [
                    'name' => $name,
                    'description' => $action->getDescription(),
                    'can_execute' => true
                ];
            }
        }

        return $available;
    }

    /**
     * Get all registered actions
     */
    public function getAllActions(): array
    {
        $all = [];
        
        foreach ($this->actions as $name => $action) {
            $all[$name] = [
                'name' => $name,
                'description' => $action->getDescription()
            ];
        }

        return $all;
    }
}