<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $stats = [];
    public $recentScans = [];
    public $topIssues = [];
    public $scanProgress = 0;
    public $darkMode = false;

    public function mount()
    {
        $this->loadStats();
        $this->loadRecentScans();
        $this->loadTopIssues();
        $this->loadDarkMode();
    }

    public function render()
    {
        return view('codesnoutr::livewire.dashboard');
    }

    public function loadStats()
    {
        $totalScans = Scan::count();
        $totalIssues = Issue::count();
        $resolvedIssues = Issue::where('fixed', true)->count();
        
        // Calculate change percentages based on last week
        $weekAgo = now()->subWeek();
        $scansLastWeek = Scan::where('created_at', '>=', $weekAgo)->count();
        $scansPreviousWeek = Scan::where('created_at', '<', $weekAgo)
            ->where('created_at', '>=', now()->subWeeks(2))->count();
        
        $issuesLastWeek = Issue::where('created_at', '>=', $weekAgo)->count();
        $issuesPreviousWeek = Issue::where('created_at', '<', $weekAgo)
            ->where('created_at', '>=', now()->subWeeks(2))->count();
            
        $scansChange = $scansPreviousWeek > 0 ? 
            round((($scansLastWeek - $scansPreviousWeek) / $scansPreviousWeek) * 100, 1) : 
            ($scansLastWeek > 0 ? 100 : 0);
            
        $issuesChange = $issuesPreviousWeek > 0 ? 
            round((($issuesLastWeek - $issuesPreviousWeek) / $issuesPreviousWeek) * 100, 1) : 
            ($issuesLastWeek > 0 ? 100 : 0);
        
        // Calculate resolution rate
        $resolutionRate = $totalIssues > 0 ? round(($resolvedIssues / $totalIssues) * 100, 1) : 0;
        
        $this->stats = [
            'total_scans' => $totalScans,
            'total_issues' => $totalIssues,
            'critical_issues' => Issue::where('severity', 'critical')->count(),
            'high_issues' => Issue::where('severity', 'high')->count(),
            'medium_issues' => Issue::where('severity', 'medium')->count(),
            'low_issues' => Issue::where('severity', 'info')->count(),
            'resolved_issues' => $resolvedIssues,
            'scans_change' => $scansChange,
            'issues_change' => $issuesChange,
            'resolution_rate' => $resolutionRate,
            'last_scan' => Scan::latest()->first()?->created_at,
            'scans_this_week' => $scansLastWeek,
            'issues_this_week' => $issuesLastWeek,
            'issues_by_category' => Issue::select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'issues_by_severity' => Issue::select('severity', DB::raw('count(*) as count'))
                ->groupBy('severity')
                ->pluck('count', 'severity')
                ->toArray(),
        ];
    }

    public function loadRecentScans()
    {
        $this->recentScans = Scan::with('issues')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($scan) {
                $scanOptions = $scan->scan_options ?? [];
                $target = $scanOptions['path'] ?? 'Full codebase';
                
                return [
                    'id' => $scan->id,
                    'type' => $scan->type,
                    'status' => $scan->status,
                    'target' => $target,
                    'issues_count' => $scan->issues->count(),
                    'issues_found' => $scan->total_issues ?? $scan->issues->count(),
                    'critical_count' => $scan->issues->where('severity', 'critical')->count(),
                    'high_count' => $scan->issues->where('severity', 'high')->count(),
                    'created_at' => $scan->created_at,
                    'completed_at' => $scan->completed_at,
                ];
            })
            ->toArray();
    }

    public function loadTopIssues()
    {
        $this->topIssues = Issue::select('rule_id', 'title', DB::raw('count(*) as count'))
            ->groupBy('rule_id', 'title')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($issue) {
                return [
                    'rule_id' => $issue->rule_id,
                    'title' => $issue->title,
                    'count' => $issue->count,
                ];
            })
            ->toArray();
    }

    public function loadDarkMode()
    {
        $setting = Setting::where('key', 'ui.dark_mode')->first();
        $this->darkMode = $setting ? (bool) $setting->value : false;
    }

    public function toggleDarkMode()
    {
        $this->darkMode = !$this->darkMode;
        
        Setting::updateOrCreate(
            ['key' => 'ui.dark_mode'],
            ['value' => $this->darkMode]
        );

        $this->dispatch('dark-mode-toggled', darkMode: $this->darkMode);
    }

    public function refreshStats()
    {
        $this->loadStats();
        $this->loadRecentScans();
        $this->loadTopIssues();
        
        $this->dispatch('stats-refreshed');
    }

    public function deleteScan($scanId)
    {
        $scan = Scan::find($scanId);
        if ($scan) {
            $scan->delete();
            $this->loadStats();
            $this->loadRecentScans();
            
            $this->dispatch('scan-deleted', message: 'Scan deleted successfully');
        }
    }

    public function getIssueCountByCategory($category)
    {
        return $this->stats['issues_by_category'][$category] ?? 0;
    }

    public function getIssueCountBySeverity($severity)
    {
        return $this->stats['issues_by_severity'][$severity] ?? 0;
    }

    public function getSeverityColor($severity)
    {
        return match($severity) {
            'critical' => 'text-red-700 dark:text-red-300 font-bold',
            'high' => 'text-orange-700 dark:text-orange-300 font-bold',
            'medium' => 'text-yellow-700 dark:text-yellow-300 font-bold',
            'info', 'low' => 'text-blue-700 dark:text-blue-300 font-bold',
            default => 'text-gray-700 dark:text-gray-300 font-bold'
        };
    }

    public function getSeverityBgColor($severity)
    {
        return match($severity) {
            'critical' => 'bg-red-100 dark:bg-red-900/20 border-red-200 dark:border-red-800',
            'high' => 'bg-orange-100 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800',
            'medium' => 'bg-yellow-100 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
            'info', 'low' => 'bg-blue-100 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
            default => 'bg-gray-100 dark:bg-gray-900/20 border-gray-200 dark:border-gray-800'
        };
    }

    public function getCategoryIcon($category)
    {
        return match($category) {
            'security' => 'shield-check',
            'performance' => 'lightning-bolt',
            'quality' => 'star',
            'laravel' => 'code-bracket',
            default => 'exclamation-triangle'
        };
    }

    public function getStatusIcon($status)
    {
        return match($status) {
            'completed' => 'check-circle',
            'failed' => 'x-circle',
            'running' => 'arrow-path',
            'pending' => 'clock',
            'cancelled' => 'stop-circle',
            default => 'question-mark-circle'
        };
    }

    public function getStatusColor($status)
    {
        return match($status) {
            'completed' => 'text-green-700 dark:text-green-300 font-semibold',
            'failed' => 'text-red-700 dark:text-red-300 font-semibold',
            'running' => 'text-blue-700 dark:text-blue-300 font-semibold',
            'pending' => 'text-yellow-700 dark:text-yellow-300 font-semibold',
            'cancelled' => 'text-gray-700 dark:text-gray-300 font-semibold',
            default => 'text-gray-700 dark:text-gray-300 font-semibold'
        };
    }

    public function getStatusBgColor($status)
    {
        return match($status) {
            'completed' => 'bg-green-100 dark:bg-green-900/20',
            'failed' => 'bg-red-100 dark:bg-red-900/20',
            'running' => 'bg-blue-100 dark:bg-blue-900/20',
            'pending' => 'bg-yellow-100 dark:bg-yellow-900/20',
            'cancelled' => 'bg-gray-100 dark:bg-gray-700/20',
            default => 'bg-gray-100 dark:bg-gray-700/20'
        };
    }
}
