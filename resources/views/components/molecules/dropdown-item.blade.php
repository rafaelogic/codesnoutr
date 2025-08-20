@props([
    'href' => null,
    'active' => false,
    'icon' => null,
    'disabled' => false,
    'destructive' => false
])

@php
    $baseClasses = 'block w-full px-4 py-2 text-left text-sm transition-colors duration-150';
    
    if ($disabled) {
        $classes = $baseClasses . ' text-gray-400 cursor-not-allowed';
    } elseif ($destructive) {
        $classes = $baseClasses . ' text-red-700 hover:bg-red-50 hover:text-red-900';
    } elseif ($active) {
        $classes = $baseClasses . ' bg-gray-100 text-gray-900';
    } else {
        $classes = $baseClasses . ' text-gray-700 hover:bg-gray-100 hover:text-gray-900';
    }
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-atoms.icon :name="$icon" size="sm" class="inline mr-3" />
        @endif
        {{ $slot }}
    </a>
@else
    <button 
        type="button" 
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled) disabled @endif
    >
        @if($icon)
            <x-atoms.icon :name="$icon" size="sm" class="inline mr-3" />
        @endif
        {{ $slot }}
    </button>
@endif
