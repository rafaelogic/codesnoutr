<?php

namespace Rafaelogic\CodeSnoutr\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class TestComponent extends Component
{
    public $counter = 0;
    public $message = 'Hello from Livewire!';

    public function increment()
    {
        Log::info('TestComponent increment called', ['counter' => $this->counter]);
        $this->counter++;
    }

    public function testAlert()
    {
        Log::info('TestComponent testAlert called');
        $this->message = 'Button clicked at ' . now()->toTimeString();
    }

    public function render()
    {
        Log::info('TestComponent rendering', ['counter' => $this->counter]);
        return view('codesnoutr::livewire.test-component');
    }
}
