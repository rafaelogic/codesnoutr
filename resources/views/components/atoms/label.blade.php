@props([
    'for' => null,
    'required' => false,
    'size' => 'md', // sm, md, lg
    'weight' => 'medium' // normal, medium, semibold, bold
])

@php
    $sizeClasses = [
        'sm' => 'text-xs',
        'md' => 'text-sm',
        'lg' => 'text-base'
    ];
    
    $weightClasses = [
        'normal' => 'font-normal',
        'medium' => 'font-medium',
        'semibold' => 'font-semibold',
        'bold' => 'font-bold'
    ];
    
    $classes = implode(' ', [
        'block text-gray-700',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $weightClasses[$weight] ?? $weightClasses['medium']
    ]);
@endphp

<label 
    @if($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
    @if($required)
        <span class="text-red-500 ml-1">*</span>
    @endif
</label>
