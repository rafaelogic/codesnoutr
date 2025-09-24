@props([
    'src' => null,
    'alt' => '',
    'size' => 'md', // xs, sm, md, lg, xl, 2xl
    'shape' => 'circle', // circle, square, rounded
    'status' => null, // online, offline, busy, away
    'initials' => null,
    'fallbackIcon' => 'user'
])

@php
    $sizeClasses = [
        'xs' => 'w-6 h-6 text-xs',
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-10 h-10 text-base',
        'lg' => 'w-12 h-12 text-lg',
        'xl' => 'w-16 h-16 text-xl',
        '2xl' => 'w-20 h-20 text-2xl'
    ];
    
    $shapeClasses = [
        'circle' => 'rounded-full',
        'square' => 'rounded-none',
        'rounded' => 'rounded-lg'
    ];
    
    $statusColors = [
        'online' => 'bg-green-500',
        'offline' => 'bg-gray-400',
        'busy' => 'bg-red-500',
        'away' => 'bg-yellow-500'
    ];
    
    $statusSizes = [
        'xs' => 'w-1.5 h-1.5',
        'sm' => 'w-2 h-2',
        'md' => 'w-2.5 h-2.5',
        'lg' => 'w-3 h-3',
        'xl' => 'w-4 h-4',
        '2xl' => 'w-5 h-5'
    ];
    
    $classes = implode(' ', [
        'relative inline-flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 overflow-hidden transition-all duration-200',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $shapeClasses[$shape] ?? $shapeClasses['circle']
    ]);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($src)
        <img 
            src="{{ $src }}" 
            alt="{{ $alt }}"
            class="w-full h-full object-cover"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'"
        />
    @endif
    
    <!-- Fallback content -->
    <div class="flex items-center justify-center w-full h-full {{ $src ? 'hidden' : '' }}">
        @if($initials)
            <span class="font-medium select-none">{{ $initials }}</span>
        @else
            <x-atoms.icon 
                :name="$fallbackIcon" 
                class="w-1/2 h-1/2 text-current opacity-75" 
            />
        @endif
    </div>
    
    <!-- Status indicator -->
    @if($status)
        <div class="absolute -bottom-0 -right-0 rounded-full border-2 border-white dark:border-gray-800 {{ $statusSizes[$size] ?? $statusSizes['md'] }} {{ $statusColors[$status] ?? $statusColors['offline'] }}"></div>
    @endif
</div>