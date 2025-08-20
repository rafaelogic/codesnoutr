@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, success, warning, ghost
    'size' => 'md', // xs, sm, md, lg, xl
    'loading' => false,
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left', // left, right, only
    'href' => null,
    'target' => null,
    'fullWidth' => false
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variantClasses = [
        'primary' => 'bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-600 focus:ring-blue-500 border border-transparent',
        'secondary' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 focus:ring-gray-500 border border-gray-300 dark:border-gray-600',
        'danger' => 'bg-red-600 dark:bg-red-700 text-white hover:bg-red-700 dark:hover:bg-red-600 focus:ring-red-500 border border-transparent',
        'success' => 'bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 focus:ring-green-500 border border-transparent',
        'warning' => 'bg-yellow-500 dark:bg-yellow-600 text-white hover:bg-yellow-600 dark:hover:bg-yellow-500 focus:ring-yellow-500 border border-transparent',
        'ghost' => 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-gray-500 border border-transparent'
    ];
    
    $sizeClasses = [
        'xs' => 'px-2.5 py-1.5 text-xs rounded',
        'sm' => 'px-3 py-2 text-sm rounded-md',
        'md' => 'px-4 py-2 text-sm rounded-md',
        'lg' => 'px-4 py-2 text-base rounded-md',
        'xl' => 'px-6 py-3 text-base rounded-md'
    ];
    
    $iconSizeClasses = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-4 h-4',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
        'xl' => 'w-5 h-5'
    ];
    
    $classes = implode(' ', [
        $baseClasses,
        $variantClasses[$variant] ?? $variantClasses['primary'],
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $fullWidth ? 'w-full' : '',
        $iconPosition === 'only' ? 'px-2' : ''
    ]);
    
    $iconSize = $iconSizeClasses[$size] ?? $iconSizeClasses['md'];
@endphp

@if($href)
    <a 
        href="{{ $href }}"
        @if($target) target="{{ $target }}" @endif
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled) aria-disabled="true" @endif
    >
        @if($loading)
            <x-atoms.spinner :size="$size" class="mr-2" />
        @elseif($icon && ($iconPosition === 'left' || $iconPosition === 'only'))
            <x-atoms.icon :name="$icon" :class="$iconSize . ($iconPosition === 'only' ? '' : ' mr-2')" />
        @endif
        
        @if($iconPosition !== 'only')
            {{ $slot }}
        @endif
        
        @if($icon && $iconPosition === 'right')
            <x-atoms.icon :name="$icon" :class="$iconSize . ' ml-2'" />
        @endif
    </a>
@else
    <button 
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled || $loading) disabled @endif
    >
        @if($loading)
            <x-atoms.spinner :size="$size" class="mr-2" />
        @elseif($icon && ($iconPosition === 'left' || $iconPosition === 'only'))
            <x-atoms.icon :name="$icon" :class="$iconSize . ($iconPosition === 'only' ? '' : ' mr-2')" />
        @endif
        
        @if($iconPosition !== 'only')
            {{ $slot }}
        @endif
        
        @if($icon && $iconPosition === 'right')
            <x-atoms.icon :name="$icon" :class="$iconSize . ' ml-2'" />
        @endif
    </button>
@endif
