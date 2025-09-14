<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Illuminate\Support\Facades\Log;

class SimpleScanResults extends Component
{
    use WithPagination;

    public $scanId;
    public $scan;
    public $severityFilter = 'all';
    public $searchTerm = '';
    
    protected $queryString = [
        'severityFilter' => ['except' => 'all'],
        'searchTerm' => ['except' => ''],
    ];

    public function mount($scanId)
    {
        $this->scanId = $scanId;
        $this->loadScan();
        
        Log::info('SimpleScanResults mounted', [
            'scanId' => $scanId,
            'component_id' => $this->getId()
        ]);
    }

    public function loadScan()
    {
        $this->scan = Scan::find($this->scanId);
    }

    public function testConnection()
    {
        Log::info('SimpleScanResults testConnection called', [
            'component_id' => $this->getId(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        return 'Simple test successful!';
    }

    public function updatedSeverityFilter()
    {
        $this->resetPage();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function getIssuesProperty()
    {
        if (!$this->scan) {
            return collect();
        }

        $query = Issue::where('scan_id', $this->scanId);

        // Apply severity filter
        if ($this->severityFilter !== 'all') {
            $query->where('severity', $this->severityFilter);
        }

        // Apply search filter
        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('file_path', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }

        return $query->orderBy('severity', 'desc')
                    ->orderBy('file_path')
                    ->paginate(20);
    }

    public function render()
    {
        return view('codesnoutr::livewire.simple-scan-results', [
            'issues' => $this->issues,
        ]);
    }
}
