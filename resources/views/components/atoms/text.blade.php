@props([
    'size' => 'default', // xs, sm, default, lg, xl, 2xl, 3xl
    'weight' => 'normal', // light, normal, medium, semibold, bold
    'color' => 'default', // default, muted, primary, secondary, danger, success, warning
    'as' => 'div' // div, span, p, h1, h2, h3, h4, h5, h6
])

@php
    $sizeClasses = [
        'xs' => 'text-xs',
        'sm' => 'text-sm',
        'default' => 'text-base',
        'lg' => 'text-lg',
        'xl' => 'text-xl',
        '2xl' => 'text-2xl',
        '3xl' => 'text-3xl'
    ];
    
    $weightClasses = [
        'light' => 'font-light',
        'normal' => 'font-normal',
        'medium' => 'font-medium',
        'semibold' => 'font-semibold',
        'bold' => 'font-bold'
    ];
    
    $colorClasses = [
        'default' => 'text-gray-900 dark:text-white',
        'muted' => 'text-gray-500 dark:text-gray-300',
        'primary' => 'text-blue-600 dark:text-blue-400',
        'secondary' => 'text-gray-600 dark:text-gray-200',
        'danger' => 'text-red-600 dark:text-red-400',
        'success' => 'text-green-600 dark:text-green-400',
        'warning' => 'text-yellow-600 dark:text-yellow-400'
    ];
    
    $classes = implode(' ', [
        $sizeClasses[$size] ?? $sizeClasses['default'],
        $weightClasses[$weight] ?? $weightClasses['normal'],
        $colorClasses[$color] ?? $colorClasses['default']
    ]);
@endphp

@if($as === 'div')
    <div {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</div>
@elseif($as === 'span')
    <span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
@elseif($as === 'p')
    <p {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</p>
@elseif(in_array($as, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']))
    <{{ $as }} {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</{{ $as }}>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</div>
@endif