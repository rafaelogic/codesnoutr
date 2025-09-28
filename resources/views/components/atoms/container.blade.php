@props([
    'spacing' => 'default', // sm, default, lg
    'maxWidth' => '7xl', // sm, md, lg, xl, 2xl, 3xl, 4xl, 5xl, 6xl, 7xl, full
    'size' => null // alias for maxWidth
])

@php
    $actualMaxWidth = $size ?? $maxWidth ?? '7xl';
    
    $spacingClasses = [
        'sm' => 'py-4',
        'default' => 'py-6 sm:py-8',
        'lg' => 'py-8 sm:py-12'
    ];
    
    $maxWidthClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl',
        '7xl' => 'max-w-7xl',
        'full' => 'max-w-full'
    ];
    
    $classes = implode(' ', [
        $spacingClasses[$spacing] ?? $spacingClasses['default'],
        $maxWidthClasses[$actualMaxWidth] ?? $maxWidthClasses['7xl'],
        'mx-auto px-4 sm:px-6 lg:px-8'
    ]);
@endphp

<div class="{{ $classes }}">
    {{ $slot }}
</div>