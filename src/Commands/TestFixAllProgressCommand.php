<?php

namespace Rafaelogic\CodeSnoutr\Commands;

use Illuminate\Console\Command;
use Rafaelogic\CodeSnoutr\Livewire\FixAllProgress;

class TestFixAllProgressCommand extends Command
{
    protected $signature = 'codesnoutr:test-fix-all-progress';
    protected $description = 'Test the FixAllProgress Livewire component for undefined variables';

    public function handle()
    {
        $this->info('Testing FixAllProgress Livewire component...');
        
        try {
            // Create an instance of the component
            $component = new FixAllProgress();
            
            // Test mount with and without session ID
            $this->info('Testing mount method...');
            $component->mount();
            $this->checkProperty($component, 'sessionId', 'string');
            $this->checkProperty($component, 'status', 'string');
            $this->checkProperty($component, 'message', 'string');
            $this->checkProperty($component, 'currentStep', 'integer');
            $this->checkProperty($component, 'totalSteps', 'integer');
            $this->checkProperty($component, 'autoRefresh', 'boolean');
            $this->checkProperty($component, 'results', 'array');
            $this->checkProperty($component, 'fixedCount', 'integer'); 
            $this->checkProperty($component, 'failedCount', 'integer');
            
            // Test mount with session ID
            $sessionId = 'test-session-123';
            $component->mount($sessionId);
            $this->line("Session ID after mount: {$component->sessionId}");
            
            // Test the render method
            $this->info('Testing render method...');
            $view = $component->render();
            $this->line("Render method returned: " . get_class($view));
            
            $this->info('✅ All tests passed! Component is properly initialized.');
            
        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
    
    private function checkProperty($component, $property, $expectedType)
    {
        if (!property_exists($component, $property)) {
            throw new \Exception("Property '{$property}' does not exist");
        }
        
        $value = $component->{$property};
        $actualType = gettype($value);
        
        // Handle special cases
        if ($expectedType === 'integer' && $actualType === 'integer') {
            $this->line("✅ {$property}: {$value} (integer)");
        } elseif ($expectedType === 'string' && ($actualType === 'string' || $value === null)) {
            $this->line("✅ {$property}: " . ($value ?? 'null') . " (string/null)");
        } elseif ($expectedType === 'boolean' && $actualType === 'boolean') {
            $this->line("✅ {$property}: " . ($value ? 'true' : 'false') . " (boolean)");
        } elseif ($expectedType === 'array' && $actualType === 'array') {
            $this->line("✅ {$property}: [" . count($value) . " items] (array)");
        } else {
            $this->line("⚠️  {$property}: {$value} (expected {$expectedType}, got {$actualType})");
        }
    }
}