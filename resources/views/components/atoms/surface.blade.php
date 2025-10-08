@props([
    'variant' => 'default', // default, elevated, bordered, ghost
    'padding' => 'default', // none, sm, default, lg, xl
    'rounded' => 'default', // none, sm, default, lg, xl, full
    'shadow' => 'default' // none, sm, default, lg, xl
])

@php
    $variantClasses = [
        'default' => 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600',
        'elevated' => 'bg-white dark:bg-gray-800',
        'bordered' => 'bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-600',
        'ghost' => 'bg-transparent border border-transparent'
    ];
    
    $paddingClasses = [
        'none' => '',
        'sm' => 'p-4',
        'default' => 'p-6',
        'lg' => 'p-8',
        'xl' => 'p-10'
    ];
    
    $roundedClasses = [
        'none' => '',
        'sm' => 'rounded-sm',
        'default' => 'rounded-lg',
        'lg' => 'rounded-xl',
        'xl' => 'rounded-2xl',
        'full' => 'rounded-full'
    ];
    
    $shadowClasses = [
        'none' => '',
        'sm' => 'shadow-sm',
        'default' => 'shadow',
        'lg' => 'shadow-lg',
        'xl' => 'shadow-xl'
    ];
    
    $classes = implode(' ', array_filter([
        $variantClasses[$variant] ?? $variantClasses['default'],
        $paddingClasses[$padding] ?? $paddingClasses['default'],
        $roundedClasses[$rounded] ?? $roundedClasses['default'],
        $shadowClasses[$shadow] ?? $shadowClasses['default'],
        'transition-colors duration-200'
    ]));
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>