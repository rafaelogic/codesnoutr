@props([
    'variant' => 'primary', // primary, secondary, success, danger, warning, info, gray
    'size' => 'md', // sm, md, lg
    'dot' => false,
    'removable' => false,
    'href' => null
])

@php
    $baseClasses = 'inline-flex items-center font-medium rounded-full';
    
    $variantClasses = [
        'primary' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
        'secondary' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
        'success' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        'danger' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
        'warning' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        'info' => 'bg-cyan-100 dark:bg-cyan-900 text-cyan-800 dark:text-cyan-200',
        'gray' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'
    ];
    
    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm'
    ];
    
    $dotColors = [
        'primary' => 'bg-blue-500',
        'secondary' => 'bg-gray-500',
        'success' => 'bg-green-500',
        'danger' => 'bg-red-500',
        'warning' => 'bg-yellow-500',
        'info' => 'bg-cyan-500',
        'gray' => 'bg-gray-400'
    ];
    
    $classes = implode(' ', [
        $baseClasses,
        $variantClasses[$variant] ?? $variantClasses['primary'],
        $sizeClasses[$size] ?? $sizeClasses['md']
    ]);
    
    $component = $href ? 'a' : 'span';
@endphp

<{{ $component }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if($dot)
        <span class="w-1.5 h-1.5 {{ $dotColors[$variant] ?? $dotColors['primary'] }} rounded-full mr-1.5"></span>
    @endif
    
    {{ $slot }}
    
    @if($removable)
        <button type="button" class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-black hover:bg-opacity-10 focus:outline-none focus:bg-black focus:bg-opacity-10">
            <x-atoms.icon name="x" size="xs" />
        </button>
    @endif
</{{ $component }}>
