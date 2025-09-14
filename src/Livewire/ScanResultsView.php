<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Illuminate\Support\Facades\Log;

class ScanResultsView extends Component
{
    public $scanId;
    public $scan;
    public $issues = [];
    public $selectedSeverity = 'all';
    public $searchTerm = '';
    public $testMessage = '';

    public function mount($scanId)
    {
        Log::info('ScanResultsView mounting', ['scanId' => $scanId]);
        
        $this->scanId = $scanId;
        $this->loadScan();
    }

    public function loadScan()
    {
        try {
            $this->scan = Scan::find($this->scanId);
            
            if ($this->scan) {
                $this->issues = Issue::where('scan_id', $this->scanId)
                    ->when($this->selectedSeverity !== 'all', function ($query) {
                        return $query->where('severity', $this->selectedSeverity);
                    })
                    ->when($this->searchTerm, function ($query) {
                        return $query->where(function ($q) {
                            $q->where('title', 'like', '%' . $this->searchTerm . '%')
                              ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                              ->orWhere('file_path', 'like', '%' . $this->searchTerm . '%');
                        });
                    })
                    ->orderBy('severity_order', 'asc')
                    ->orderBy('file_path', 'asc')
                    ->take(50) // Limit to prevent performance issues
                    ->get();
                    
                Log::info('Scan loaded successfully', [
                    'scanId' => $this->scanId,
                    'issuesCount' => count($this->issues)
                ]);
            } else {
                Log::warning('Scan not found', ['scanId' => $this->scanId]);
            }
        } catch (\Exception $e) {
            Log::error('Error loading scan', [
                'scanId' => $this->scanId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updatedSelectedSeverity()
    {
        Log::info('Severity filter changed', ['severity' => $this->selectedSeverity]);
        $this->loadScan();
    }

    public function updatedSearchTerm()
    {
        Log::info('Search term changed', ['searchTerm' => $this->searchTerm]);
        $this->loadScan();
    }

    public function testConnection()
    {
        Log::info('testConnection called - ScanResultsView is working!');
        $this->testMessage = 'Test successful at ' . now()->format('H:i:s');
        return 'Test completed successfully';
    }

    public function clearTest()
    {
        Log::info('clearTest called');
        $this->testMessage = '';
    }

    public function render()
    {
        return view('codesnoutr::livewire.scan-results-view');
    }
}
