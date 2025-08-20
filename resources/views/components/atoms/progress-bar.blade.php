@props([
    'value' => 0,
    'max' => 100,
    'size' => 'md', // sm, md, lg
    'variant' => 'primary', // primary, success, warning, danger
    'showLabel' => false,
    'label' => '',
    'animated' => false
])

@php
    $percentage = $max > 0 ? min(100, ($value / $max) * 100) : 0;
    
    $sizeClasses = [
        'sm' => 'h-1',
        'md' => 'h-2',
        'lg' => 'h-3'
    ];
    
    $variantClasses = [
        'primary' => 'bg-blue-600',
        'success' => 'bg-green-600',
        'warning' => 'bg-yellow-500',
        'danger' => 'bg-red-600'
    ];
    
    $barClasses = implode(' ', [
        'transition-all duration-300 ease-in-out rounded-full',
        $variantClasses[$variant] ?? $variantClasses['primary'],
        $animated ? 'bg-gradient-to-r animate-pulse' : ''
    ]);
    
    $containerClasses = implode(' ', [
        'w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden',
        $sizeClasses[$size] ?? $sizeClasses['md']
    ]);
@endphp

<div {{ $attributes->merge(['class' => 'progress-container']) }}>
    @if($showLabel || $label)
        <div class="flex justify-between items-center mb-1">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $label ?: "Progress" }}
            </span>
            @if($showLabel)
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ round($percentage) }}%</span>
            @endif
        </div>
    @endif
    
    <div class="{{ $containerClasses }}">
        <div 
            class="{{ $barClasses }}"
            style="width: {{ $percentage }}%; height: 100%"
            role="progressbar"
            aria-valuenow="{{ $value }}"
            aria-valuemin="0"
            aria-valuemax="{{ $max }}"
        ></div>
    </div>
</div>
