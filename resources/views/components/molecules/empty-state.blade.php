@props([
    'icon' => 'document-text',
    'title' => 'No data available',
    'description' => '',
    'actionText' => '',
    'actionHref' => '',
    'size' => 'md' // sm, md, lg
])

@php
    $sizeClasses = [
        'sm' => 'py-6',
        'md' => 'py-12',
        'lg' => 'py-16'
    ];
    
    $iconSizes = [
        'sm' => 'xl',
        'md' => '2xl',
        'lg' => '2xl'
    ];
    
    $classes = implode(' ', [
        'text-center',
        $sizeClasses[$size] ?? $sizeClasses['md']
    ]);
    
    $iconSize = $iconSizes[$size] ?? $iconSizes['md'];
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    <x-atoms.icon 
        :name="$icon" 
        :size="$iconSize" 
        color="muted"
        class="mx-auto mb-4"
    />
    
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
        {{ $title }}
    </h3>
    
    @if($description)
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
            {{ $description }}
        </p>
    @endif
    
    @if($actionText && $actionHref)
        <div>
            <x-atoms.button 
                :href="$actionHref" 
                variant="primary"
                icon="plus-circle"
            >
                {{ $actionText }}
            </x-atoms.button>
        </div>
    @endif
    
    @if($slot->isNotEmpty())
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif
</div>
