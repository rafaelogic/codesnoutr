@extends('codesnoutr::components.templates.app-layout')

@section('title', 'Livewire Test')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Livewire Component Test</h1>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Test Component (Should have wire:id wrapper)</h2>
        @livewire('codesnoutr-test-component')
    </div>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">ScanResults Component (Currently broken)</h2>
        @livewire('codesnoutr-scan-results', ['scanId' => 1])
    </div>
    
    <div class="mt-8">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('=== LIVEWIRE TEST PAGE ===');
                
                const wireElements = document.querySelectorAll('[wire\\:id]');
                console.log(`Found ${wireElements.length} Livewire components:`);
                
                wireElements.forEach((el, index) => {
                    const wireId = el.getAttribute('wire:id');
                    console.log(`Component ${index + 1}: wire:id="${wireId}"`);
                    console.log('Element:', el);
                    
                    if (window.Livewire && window.Livewire.find) {
                        const component = window.Livewire.find(wireId);
                        console.log('Livewire instance:', component);
                    }
                });
                
                if (wireElements.length === 0) {
                    console.error('❌ NO LIVEWIRE COMPONENTS FOUND!');
                    console.log('This indicates a serious Livewire rendering issue.');
                }
            });
        </script>
    </div>
</div>
@endsection