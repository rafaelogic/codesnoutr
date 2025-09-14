<div class="p-4 border border-gray-300 rounded">
    <h3 class="text-lg font-semibold mb-4">Test Component</h3>
    
    <div class="mb-4">
        <p><strong>Counter:</strong> {{ $counter }}</p>
        <p><strong>Message:</strong> {{ $message }}</p>
    </div>
    
    <div class="space-x-2">
        <button wire:click="increment" 
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Increment ({{ $counter }})
        </button>
        
        <button wire:click="testAlert" 
                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
            Test Alert
        </button>
        
        <button onclick="console.log('Plain JS works'); alert('Plain JS button clicked!');"
                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Plain JS Test
        </button>
    </div>
    
    <div class="mt-4 text-sm text-gray-600">
        <p>Component ID: {{ $this->getId() }}</p>
        <p>Loading state: <span wire:loading>Loading...</span><span wire:loading.remove>Ready</span></p>
    </div>
</div>
