<?php

namespace Rafaelogic\CodeSnoutr\Livewire\Dashboard;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Illuminate\Support\Facades\DB;

class MetricsOverview extends Component
{
    public $metrics = [];

    public function mount()
    {
        $this->loadMetrics();
    }

    public function render()
    {
        return view('codesnoutr::livewire.dashboard.metrics-overview');
    }

    protected function loadMetrics()
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
        
        $this->metrics = [
            'total_scans' => [
                'value' => $totalScans,
                'change' => $scansChange,
                'changeType' => $scansChange >= 0 ? 'increase' : 'decrease'
            ],
            'total_issues' => [
                'value' => $totalIssues,
                'change' => null
            ],
            'critical_issues' => [
                'value' => $criticalIssues,
                'change' => null
            ],
            'resolution_rate' => [
                'value' => $totalIssues > 0 ? round(($resolvedIssues / $totalIssues) * 100, 1) . '%' : '0%',
                'change' => null
            ]
        ];
    }

    protected function calculatePercentageChange($current, $previous)
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }
}