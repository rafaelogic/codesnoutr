@props([
    'placeholder' => 'Search...',
    'value' => '',
    'size' => 'md', // sm, md, lg
    'loading' => false,
    'clearable' => true,
    'autofocus' => false
])

@php
    $inputId = uniqid('search_');
    
    $containerClasses = [
        'sm' => 'relative max-w-xs',
        'md' => 'relative max-w-sm',
        'lg' => 'relative max-w-md'
    ];
    
    $containerClass = $containerClasses[$size] ?? $containerClasses['md'];
@endphp

<div {{ $attributes->merge(['class' => $containerClass]) }}>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            @if($loading)
                <x-atoms.spinner size="sm" color="secondary" />
            @else
                <x-atoms.icon name="search" size="sm" color="secondary" />
            @endif
        </div>
        
        <x-atoms.input
            :id="$inputId"
            type="search"
            :placeholder="$placeholder"
            :value="$value"
            :size="$size"
            :autofocus="$autofocus"
            class="pl-10 {{ $clearable && $value ? 'pr-10' : '' }}"
        />
        
        @if($clearable && $value)
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button 
                    type="button"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
                    onclick="document.getElementById('{{ $inputId }}').value = ''; this.parentElement.parentElement.classList.add('hidden');"
                >
                    <x-atoms.icon name="x" size="sm" />
                </button>
            </div>
        @endif
    </div>
</div>
