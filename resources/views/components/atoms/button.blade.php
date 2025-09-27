@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, success, warning, ghost, outline-primary, outline-secondary
    'size' => 'md', // xs, sm, md, lg, xl
    'loading' => false,
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left', // left, right, only
    'href' => null,
    'target' => null,
    'fullWidth' => false,
    'rounded' => 'default', // none, sm, default, lg, full
    'shadow' => true,
    'pulse' => false // for call-to-action buttons
])

@php
    $baseClasses = 'btn';
    
    $variantClasses = [
        'primary' => 'btn--primary',
        'secondary' => 'btn--secondary',
        'danger' => 'btn--danger',
        'success' => 'btn--success',
        'warning' => 'btn--warning',
        'ghost' => 'btn--ghost',
        'outline-primary' => 'btn--outline-primary',
        'outline-secondary' => 'btn--outline-secondary',
    ];
    
    $sizeClasses = [
        'xs' => 'btn--xs',
        'sm' => 'btn--sm',
        'md' => 'btn--md',
        'lg' => 'btn--lg',
        'xl' => 'btn--xl'
    ];
    
    $roundedClasses = [
        'none' => 'rounded-none',
        'sm' => 'rounded-sm',
        'default' => 'rounded-md',
        'lg' => 'rounded-lg',
        'full' => 'rounded-full'
    ];
    
    $iconAlignment = match($iconPosition) {
        'left' => 'btn--icon-left',
        'right' => 'btn--icon-right',
        'only' => 'btn--icon-only',
        default => 'btn--icon-left'
    };
    
    $iconSizeMap = [
        'xs' => 'xs',
        'sm' => 'sm', 
        'md' => 'sm',
        'lg' => 'md',
        'xl' => 'lg'
    ];
    
    $classes = implode(' ', array_filter([
        $baseClasses,
        $variantClasses[$variant] ?? $variantClasses['primary'],
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $roundedClasses[$rounded] ?? '',
        $fullWidth ? 'w-full' : '',
        $loading ? 'btn--loading' : '',
        $pulse ? 'animate-pulse' : '',
        !$shadow ? 'shadow-none' : '',
        $iconPosition === 'only' ? $iconAlignment : ($icon ? $iconAlignment : ''),
    ]));
@endphp

@if($href)
    <a 
        href="{{ $href }}" 
        @if($target) target="{{ $target }}" @endif
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled) aria-disabled="true" @endif
        role="button"
    >
        @if($loading)
            <x-atoms.icon name="loading" :size="$iconSizeMap[$size]" class="icon--loading mr-2" />
        @elseif($icon && $iconPosition === 'left')
            <x-atoms.icon :name="$icon" :size="$iconSizeMap[$size]" class="mr-2" />
        @endif
        
        @if($iconPosition !== 'only')
            <span>{{ $slot }}</span>
        @endif
        
        @if($icon && $iconPosition === 'right')
            <x-atoms.icon :name="$icon" :size="$iconSizeMap[$size]" class="ml-2" />
        @endif
        
        @if($icon && $iconPosition === 'only')
            <x-atoms.icon :name="$icon" :size="$iconSizeMap[$size]" />
        @endif
    </a>
@else
    <button 
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled || $loading) disabled @endif
        @if($loading) aria-busy="true" @endif
    >
        @if($loading)
            <x-atoms.icon name="loading" :size="$iconSizeMap[$size]" class="icon--loading mr-2" />
        @elseif($icon && $iconPosition === 'left')
            <x-atoms.icon :name="$icon" :size="$iconSizeMap[$size]" class="mr-2" />
        @endif
        
        @if($iconPosition !== 'only')
            <span>{{ $slot }}</span>
        @endif
        
        @if($icon && $iconPosition === 'right')
            <x-atoms.icon :name="$icon" :size="$iconSizeMap[$size]" class="ml-2" />
        @endif
        
        @if($icon && $iconPosition === 'only')
            <x-atoms.icon :name="$icon" :size="$iconSizeMap[$size]" />
        @endif
    </button>
@endif
