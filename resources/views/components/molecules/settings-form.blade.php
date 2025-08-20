@props([
    'method' => 'POST',
    'action' => '#',
    'submitText' => 'Save Changes',
    'cancelText' => 'Cancel',
    'cancelUrl' => null,
    'loading' => false
])

<form method="{{ $method === 'GET' ? 'GET' : 'POST' }}" action="{{ $action }}" {{ $attributes->except(['method', 'action']) }}>
    @if($method !== 'GET' && $method !== 'POST')
        @method($method)
    @endif
    
    @if($method !== 'GET')
        @csrf
    @endif

    <div class="space-y-6">
        {{ $slot }}
    </div>

    <div class="pt-6 border-t border-gray-200">
        <div class="flex justify-end space-x-3">
            @if($cancelUrl)
                <x-atoms.button 
                    tag="a" 
                    href="{{ $cancelUrl }}" 
                    variant="secondary"
                >
                    {{ $cancelText }}
                </x-atoms.button>
            @endif

            <x-atoms.button 
                type="submit" 
                variant="primary"
                :loading="$loading"
                :disabled="$loading"
            >
                {{ $submitText }}
            </x-atoms.button>
        </div>
    </div>
</form>
