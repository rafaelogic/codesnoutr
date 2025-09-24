@props([
    'method' => 'POST',
    'action' => null,
    'multipart' => false,
    'novalidate' => false,
    'spacing' => 'default', // tight, default, loose
    'layout' => 'vertical', // vertical, horizontal, inline
    'variant' => 'default' // default, card, modal
])

@php
    $spacingClasses = [
        'tight' => 'space-y-3',
        'default' => 'space-y-6',
        'loose' => 'space-y-8'
    ];
    
    $layoutClasses = [
        'vertical' => '',
        'horizontal' => 'space-y-0',
        'inline' => 'flex flex-wrap items-end gap-4 space-y-0'
    ];
    
    $containerClasses = [
        'default' => '',
        'card' => 'surface p-6',
        'modal' => 'surface p-6 max-w-md mx-auto'
    ];
    
    $formClasses = [
        $spacingClasses[$spacing] ?? $spacingClasses['default'],
        $layoutClasses[$layout] ?? '',
    ];
@endphp

<div class="{{ $containerClasses[$variant] ?? '' }}">
    <form 
        @if($action) action="{{ $action }}" @endif
        method="{{ $method }}"
        @if($multipart) enctype="multipart/form-data" @endif
        @if($novalidate) novalidate @endif
        {{ $attributes->merge(['class' => implode(' ', array_filter($formClasses))]) }}
    >
        @if($method !== 'GET' && $method !== 'POST')
            @method($method)
        @endif
        
        @csrf
        
        {{ $slot }}
    </form>
</div>