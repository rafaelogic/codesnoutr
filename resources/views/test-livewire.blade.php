<x-templates.app-layout title="Test Livewire">
    <div class="p-8">
        <h1 class="text-2xl font-bold mb-4">Livewire Test</h1>
        
        <!-- Test our simple component first -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold mb-2">Testing Simple Test Component:</h2>
            @livewire('codesnoutr-test-component')
        </div>
        
        <!-- Test DarkModeToggle (we know this component exists) -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold mb-2">Testing DarkModeToggle Component:</h2>
            @livewire('codesnoutr-dark-mode-toggle')
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Test page DOM loaded');
                console.log('Livewire available:', typeof Livewire !== 'undefined');
            });
            
            document.addEventListener('livewire:init', function() {
                console.log('Livewire initialized on test page!');
            });
            
            // Check if Livewire script is loaded
            setTimeout(function() {
                console.log('After timeout - Livewire available:', typeof Livewire !== 'undefined');
                if (typeof Livewire !== 'undefined') {
                    console.log('Livewire components:', Object.keys(Livewire.all()));
                }
            }, 1000);
        </script>
    </div>
</x-templates.app-layout>
