@props([
    'padding' => 'default' // 'none', 'sm', 'default', 'lg'
])

@php
    $paddingClasses = [
        'none' => '',
        'sm' => 'p-4',
        'default' => 'p-6',
        'lg' => 'p-8'
    ];
    
    $paddingClass = $paddingClasses[$padding] ?? $paddingClasses['default'];
@endphp

<div class="{{ $paddingClass }}">
    {{ $slot }}
</div>