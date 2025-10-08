@props([
    'title' => '',
    'description' => '',
    'icon' => null,
    'footer' => false,
    'padding' => 'default', // 'default', 'none', 'sm', 'lg'
    'variant' => 'default', // default, elevated, bordered, ghost, interactive
    'shadow' => 'default',
    'hover' => false, // adds hover effects
    'loading' => false,
    'collapsible' => false,
    'collapsed' => false
])

@php
    $cardClasses = [
        'surface',
        $variant === 'interactive' ? 'surface--interactive hover-lift' : '',
        $hover ? 'hover-scale' : '',
        $loading ? 'animate-pulse' : '',
    ];
@endphp

<div 
    {{ $attributes->merge(['class' => implode(' ', array_filter($cardClasses))]) }}
    @if($collapsible) x-data="{ collapsed: {{ $collapsed ? 'true' : 'false' }} }" @endif
>
    @if($title || $description || isset($actions) || $icon || $collapsible)
        <div class="flex items-start justify-between p-6 border-b border-gray-200 dark:border-gray-600">
            <div class="flex items-start space-x-3">
                @if($icon)
                    <div class="flex-shrink-0 mt-1">
                        <x-atoms.icon :name="$icon" size="md" class="text-gray-400 dark:text-gray-300" />
                    </div>
                @endif
                
                <div class="min-w-0 flex-1">
                    @if($title)
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $title }}
                        </h3>
                    @endif
                    
                    @if($description)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                            {{ $description }}
                        </p>
                    @endif
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                @if(isset($actions))
                    {{ $actions }}
                @endif
                
                @if($collapsible)
                    <button 
                        @click="collapsed = !collapsed"
                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        :aria-expanded="!collapsed"
                        aria-label="Toggle card content"
                    >
                        <x-atoms.icon 
                            name="chevron-down" 
                            size="sm"
                            class="transition-transform duration-200"
                            :class="collapsed ? '' : 'rotate-180'"
                        />
                    </button>
                @endif
            </div>
        </div>
    @endif

    <div 
        @if($collapsible) x-show="!collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" @endif
        class="{{ $padding === 'none' ? '' : ($padding === 'sm' ? 'p-4' : ($padding === 'lg' ? 'p-8' : 'p-6')) }}"
    >
        @if($loading)
            <div class="space-y-3">
                <div class="skeleton skeleton--title"></div>
                <div class="skeleton skeleton--text"></div>
                <div class="skeleton skeleton--text"></div>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>

    @if($footer)
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-600 rounded-b-lg">
            {{ $footer }}
        </div>
    @endif
</div>
