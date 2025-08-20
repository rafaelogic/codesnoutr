@props([
    'title' => '',
    'description' => '',
    'footer' => false,
    'padding' => 'default' // 'default', 'none', 'sm', 'lg'
])

@php
    $paddingClasses = [
        'none' => '',
        'sm' => 'p-4',
        'default' => 'p-6',
        'lg' => 'p-8'
    ];
    
    $cardPadding = $paddingClasses[$padding] ?? $paddingClasses['default'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 shadow dark:shadow-gray-900/20 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors duration-200']) }}>
    @if($title || $description || isset($header))
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                @if($title)
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">{{ $title }}</h3>
                @endif
                
                @if($description)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                @endif
            </div>
            
            @if(isset($header))
                <div>
                    {{ $header }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $cardPadding }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
            {{ $footer }}
        </div>
    @endif
</div>
