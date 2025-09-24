<?php

namespace Rafaelogic\CodeSnoutr\Livewire\Dashboard;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Models\Scan;

class RecentActivity extends Component
{
    public $recentScans = [];

    public function mount()
    {
        $this->loadRecentScans();
    }

    public function render()
    {
        return view('codesnoutr::livewire.dashboard.recent-activity');
    }

    protected function loadRecentScans()
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
                    'target' => $target,
                    'status' => $scan->status,
                    'issues_count' => $scan->issues->count(),
                    'created_at' => $scan->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }
}