@props([
    'variant' => 'info', // info, success, warning, danger
    'icon' => null,
    'dismissible' => false,
    'size' => 'md' // sm, md, lg
])

@php
    $variantClasses = [
        'info' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200',
        'success' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200',
        'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200',
        'danger' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'
    ];
    
    $iconColors = [
        'info' => 'text-blue-400',
        'success' => 'text-green-400',
        'warning' => 'text-yellow-400',
        'danger' => 'text-red-400'
    ];
    
    $sizeClasses = [
        'sm' => 'p-3 text-sm',
        'md' => 'p-4 text-sm',
        'lg' => 'p-6 text-base'
    ];
    
    $classes = implode(' ', [
        'border rounded-lg',
        $variantClasses[$variant] ?? $variantClasses['info'],
        $sizeClasses[$size] ?? $sizeClasses['md']
    ]);
    
    $iconColor = $iconColors[$variant] ?? $iconColors['info'];
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    <x-atoms.stack direction="horizontal" size="sm" class="items-center">
        @if($icon)
            <x-atoms.icon :name="$icon" size="sm" class="{{ $iconColor }}" />
        @endif
        
        <div class="flex-1">
            @if(isset($title))
                <div class="font-medium mb-1">{{ $title }}</div>
            @endif
            <div {{ !isset($title) ? 'class=font-medium' : '' }}>
                {{ $slot }}
            </div>
        </div>
        
        @if($dismissible)
            <x-atoms.button 
                variant="ghost" 
                size="xs" 
                icon="x-circle"
                onclick="this.closest('[role=alert]').remove()"
                class="ml-auto"
            />
        @endif
    </x-atoms.stack>
</div>