<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;

class ScanResultsTest extends Component
{
    public $scanId;
    public $message = 'Test component working';

    public function mount($scanId = null)
    {
        $this->scanId = $scanId;
    }

    public function testMethod()
    {
        $this->message = 'Button clicked at ' . now();
    }

    public function render()
    {
        return view('codesnoutr::livewire.scan-results-test');
    }
}