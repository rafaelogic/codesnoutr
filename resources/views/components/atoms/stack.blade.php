@props([
    'spacing' => 'default', // xs, sm, default, lg, xl, 2xl
    'direction' => 'vertical' // vertical, horizontal
])

@php
    $spacingClasses = [
        'xs' => $direction === 'vertical' ? 'space-y-1' : 'space-x-1',
        'sm' => $direction === 'vertical' ? 'space-y-2' : 'space-x-2',
        'default' => $direction === 'vertical' ? 'space-y-4' : 'space-x-4',
        'lg' => $direction === 'vertical' ? 'space-y-6' : 'space-x-6',
        'xl' => $direction === 'vertical' ? 'space-y-8' : 'space-x-8',
        '2xl' => $direction === 'vertical' ? 'space-y-12' : 'space-x-12'
    ];
    
    $directionClass = $direction === 'vertical' ? 'flex flex-col' : 'flex flex-row';
    
    $classes = implode(' ', [
        $directionClass,
        $spacingClasses[$spacing] ?? $spacingClasses['default']
    ]);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>