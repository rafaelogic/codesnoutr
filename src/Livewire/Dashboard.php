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
        $totalScans = Scan::count();
        $totalIssues = Issue::count();
        $resolvedIssues = Issue::where('fixed', true)->count();
        $criticalIssues = Issue::where('severity', 'critical')->count();
        
        // Calculate weekly changes
        $weekAgo = now()->subWeek();
        $scansLastWeek = Scan::where('created_at', '>=', $weekAgo)->count();
        $scansPreviousWeek = Scan::where('created_at', '<', $weekAgo)
            ->where('created_at', '>=', now()->subWeeks(2))->count();
            
        $scansChange = $this->calculatePercentageChange($scansLastWeek, $scansPreviousWeek);
        
        $issuesLastWeek = Issue::where('created_at', '>=', $weekAgo)->count();
        $issuesPreviousWeek = Issue::where('created_at', '<', $weekAgo)
            ->where('created_at', '>=', now()->subWeeks(2))->count();
            
        $issuesChange = $this->calculatePercentageChange($issuesLastWeek, $issuesPreviousWeek);
        
        // Issues by category
        $issuesByCategory = Issue::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
        
        $this->stats = [
            'total_scans' => $totalScans,
            'total_issues' => $totalIssues,
            'critical_issues' => $criticalIssues,
            'resolution_rate' => $totalIssues > 0 ? round(($resolvedIssues / $totalIssues) * 100, 1) : 0,
            'scans_change' => $scansChange,
            'issues_change' => $issuesChange,
            'issues_by_category' => $issuesByCategory,
        ];
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
}