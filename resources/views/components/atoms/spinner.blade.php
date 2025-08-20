@props([
    'size' => 'md', // xs, sm, md, lg, xl
    'color' => 'current' // current, primary, secondary, white
])

@php
    $sizeClasses = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-4 h-4',
        'md' => 'w-5 h-5',
        'lg' => 'w-6 h-6',
        'xl' => 'w-8 h-8'
    ];
    
    $colorClasses = [
        'current' => 'text-current',
        'primary' => 'text-blue-600',
        'secondary' => 'text-gray-600',
        'white' => 'text-white'
    ];
    
    $classes = implode(' ', [
        'animate-spin',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $colorClasses[$color] ?? $colorClasses['current']
    ]);
@endphp

<svg {{ $attributes->merge(['class' => $classes]) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
</svg>
