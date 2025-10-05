<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $stats = [];
    public $recentScans = [];
    public $topIssues = [];
    public $isLoading = false;
    public $isRefreshing = false;
    
    // AI Fix All state
    public $fixAllInProgress = false;
    public $fixAllResults = [];
    public $showFixAllResults = false;
    public $currentFixingIssue = null;

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function render()
    {
        return view('codesnoutr::livewire.dashboard');
    }

    public function loadDashboardData()
    {
        $this->isLoading = true;
        
        $this->loadStats();
        $this->loadRecentScans();
        $this->loadTopIssues();
        
        $this->isLoading = false;
    }

    public function refreshStats()
    {
        $this->isRefreshing = true;
        
        $this->loadStats();
        $this->loadRecentScans();
        $this->loadTopIssues();
        
        $this->isRefreshing = false;
        $this->dispatch('dashboard-refreshed');
    }

    protected function loadStats()
    {
        try {
            // Use proper scopes and ensure data accuracy
            $totalScans = Scan::count();
            $totalIssues = Issue::count();
            $resolvedIssues = Issue::fixed()->count(); // Use the scope for better clarity
            $criticalIssues = Issue::where('severity', 'critical')->count();
            
            // Get AI usage data from the AI service for more accuracy
            try {
                $aiService = app(\Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService::class);
                $aiUsageStats = $aiService->getUsageStats();
                $aiSpending = $aiUsageStats['current_usage'];
                $aiMonthlyLimit = $aiUsageStats['monthly_limit'];
                $aiSpendingPercentage = $aiUsageStats['percentage_used'];
            } catch (\Exception $e) {
                // Fallback to direct setting access
                $aiSpending = (float) \Rafaelogic\CodeSnoutr\Models\Setting::get('ai_current_usage', 0.00);
                $aiMonthlyLimit = (float) \Rafaelogic\CodeSnoutr\Models\Setting::get('ai_monthly_limit', 50.00);
                $aiSpendingPercentage = $aiMonthlyLimit > 0 ? round(($aiSpending / $aiMonthlyLimit) * 100, 1) : 0;
            }
            
            // Ensure values are properly formatted for display
            $aiSpending = max(0, $aiSpending); // Ensure non-negative
            $aiMonthlyLimit = max(1, $aiMonthlyLimit); // Ensure minimum 1 to prevent division by zero
            
            // Calculate weekly changes with better date handling
            $weekAgo = now()->subWeek();
            $twoWeeksAgo = now()->subWeeks(2);
            
            $scansLastWeek = Scan::where('created_at', '>=', $weekAgo)->count();
            $scansPreviousWeek = Scan::where('created_at', '>=', $twoWeeksAgo)
                ->where('created_at', '<', $weekAgo)->count();
                
            $scansChange = $this->calculatePercentageChange($scansLastWeek, $scansPreviousWeek);
            
            $issuesLastWeek = Issue::where('created_at', '>=', $weekAgo)->count();
            $issuesPreviousWeek = Issue::where('created_at', '>=', $twoWeeksAgo)
                ->where('created_at', '<', $weekAgo)->count();
                
            $issuesChange = $this->calculatePercentageChange($issuesLastWeek, $issuesPreviousWeek);
            
            // Issues by category with proper null handling
            $issuesByCategory = Issue::select('category', DB::raw('count(*) as count'))
                ->whereNotNull('category')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray();
            
            // Calculate more accurate metrics
            $resolutionRate = $totalIssues > 0 ? round(($resolvedIssues / $totalIssues) * 100, 1) : 0;
            // AI spending percentage already calculated above from AI service
            
            $this->stats = [
                'total_scans' => $totalScans,
                'total_issues' => $totalIssues,
                'resolved_issues' => $resolvedIssues,
                'critical_issues' => $criticalIssues,
                'ai_spending' => $aiSpending,
                'ai_monthly_limit' => $aiMonthlyLimit,
                'ai_spending_percentage' => min(100, $aiSpendingPercentage), // Cap at 100%
                'resolution_rate' => $resolutionRate,
                'scans_change' => $scansChange,
                'issues_change' => $issuesChange,
                'issues_by_category' => $issuesByCategory,
            ];
        } catch (\Exception $e) {
            // Fallback stats in case of database errors
            \Illuminate\Support\Facades\Log::error('Dashboard stats loading failed: ' . $e->getMessage());
            
            $this->stats = [
                'total_scans' => 0,
                'total_issues' => 0,
                'resolved_issues' => 0,
                'critical_issues' => 0,
                'ai_spending' => 0.00,
                'ai_monthly_limit' => 50.00,
                'ai_spending_percentage' => 0,
                'resolution_rate' => 0,
                'scans_change' => 0,
                'issues_change' => 0,
                'issues_by_category' => [],
            ];
        }
    }

    protected function loadRecentScans()
    {
        $this->recentScans = Scan::latest()
            ->take(5)
            ->get()
            ->map(function ($scan) {
                $issues = Issue::where('scan_id', $scan->id);
                $totalIssues = $issues->count();
                $criticalCount = (clone $issues)->where('severity', 'critical')->count();
                $highCount = (clone $issues)->where('severity', 'high')->count();
                
                return [
                    'id' => $scan->id,
                    'type' => $scan->type,
                    'target' => $scan->target,
                    'status' => $scan->status,
                    'issues_found' => $totalIssues,
                    'critical_count' => $criticalCount,
                    'high_count' => $highCount,
                    'created_at' => $scan->created_at,
                    'completed_at' => $scan->completed_at,
                ];
            })->toArray();
    }

    protected function loadTopIssues()
    {
        $this->topIssues = Issue::select('rule_id', 'title', DB::raw('count(*) as count'))
            ->where('fixed', false)
            ->groupBy('rule_id', 'title')
            ->orderByDesc('count')
            ->take(10)
            ->get()
            ->map(function ($issue) {
                return [
                    'rule_id' => $issue->rule_id,
                    'title' => $issue->title,
                    'count' => $issue->count,
                ];
            })->toArray();
    }

    protected function calculatePercentageChange($current, $previous)
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function getStatusIcon($status)
    {
        return match($status) {
            'completed' => 'check-circle',
            'pending' => 'clock',
            'running' => 'arrow-path',
            'failed' => 'x-circle',
            'cancelled' => 'stop-circle',
            default => 'question-mark-circle',
        };
    }

    public function getIssueCountByCategory($category)
    {
        return $this->stats['issues_by_category'][$category] ?? 0;
    }

    public function getCategoryColor($category)
    {
        return match($category) {
            'security' => 'danger',
            'performance' => 'warning', 
            'quality' => 'primary',
            'laravel' => 'success',
            default => 'secondary',
        };
    }

    public function getCategoryIcon($category)
    {
        return match($category) {
            'security' => 'shield-exclamation',
            'performance' => 'bolt',
            'quality' => 'star',
            'laravel' => 'code-bracket',
            default => 'tag',
        };
    }

    public function deleteScan($scanId)
    {
        try {
            $scan = Scan::findOrFail($scanId);
            
            // Delete associated issues first
            Issue::where('scan_id', $scanId)->delete();
            
            // Delete the scan
            $scan->delete();
            
            // Refresh the data
            $this->refreshStats();
            
            $this->dispatch('scan-deleted', scanId: $scanId);
            
        } catch (\Exception $e) {
            $this->dispatch('scan-delete-error', message: 'Failed to delete scan: ' . $e->getMessage());
        }
    }

    public function getScanStatusBadge($status)
    {
        return match($status) {
            'completed' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Completed'],
            'pending' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Pending'],
            'running' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Running'],
            'failed' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Failed'],
            'cancelled' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Cancelled'],
            default => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Unknown'],
        };
    }

    public function getSeverityBadge($severity)
    {
        return match($severity) {
            'critical' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Critical'],
            'high' => ['class' => 'bg-orange-100 text-orange-800', 'text' => 'High'],
            'medium' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Medium'],
            'low' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Low'],
            default => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Unknown'],
        };
    }

    public function fixAllIssues()
    {
        try {
            // Check if there are issues to fix
            $unfixedCount = Issue::where('fixed', false)->count();
            
            if ($unfixedCount === 0) {
                $this->dispatch('show-notification', [
                    'type' => 'info',
                    'message' => 'No issues found that need fixing.'
                ]);
                return;
            }

            // Check if AI is available
            $aiService = new \Rafaelogic\CodeSnoutr\Services\AI\AiAssistantService();
            if (!$aiService->isAvailable()) {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'message' => 'AI service is not available. Please configure your OpenAI API key in settings.'
                ]);
                return;
            }

            // Generate a unique session ID for tracking progress
            $sessionId = \Illuminate\Support\Str::uuid()->toString();
            
            // Redirect to the progress page with the session ID
            return redirect()->route('codesnoutr.fix-all.progress', ['sessionId' => $sessionId]);
            
        } catch (\Exception $e) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Failed to start AI fix process: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Parse AI fix JSON for preview display
     */
    protected function parseAiFixForPreview($aiFixData): ?array
    {
        if (empty($aiFixData)) {
            return null;
        }

        try {
            // If it's already an array, use it
            if (is_array($aiFixData)) {
                return $aiFixData;
            }

            // Try to decode JSON
            $decoded = json_decode($aiFixData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // Fallback for legacy format
            return [
                'code' => $aiFixData,
                'explanation' => 'AI fix generated',
                'confidence' => 0.8
            ];
        } catch (\Exception $e) {
            return [
                'code' => $aiFixData,
                'explanation' => 'Could not parse AI fix data',
                'confidence' => 0.5
            ];
        }
    }

    /**
     * Hide fix all results
     */
    public function hideFixAllResults()
    {
        $this->showFixAllResults = false;
        $this->fixAllResults = [];
    }
}