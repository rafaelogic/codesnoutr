@props([
    'show' => false,
    'size' => 'md', // sm, md, lg, xl, full
    'persistent' => false,
    'title' => '',
    'maxWidth' => null
])

@php
    $sizeClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl',
        'full' => 'max-w-full'
    ];
    
    $modalClass = $maxWidth ? $maxWidth : ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

<div 
    x-data="{ show: @js($show) }"
    x-show="show"
    x-on:open-modal.window="$event.detail.name === '{{ $attributes->get('name') }}' ? show = true : null"
    x-on:close-modal.window="$event.detail.name === '{{ $attributes->get('name') }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="!{{ $persistent ? 'true' : 'false' }} && (show = false)"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: none;"
>
    <!-- Backdrop -->
    <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        @if(!$persistent) @click="show = false" @endif
    ></div>

    <!-- Modal Container -->
    <div class="flex min-h-full items-center justify-center">
        <div 
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all w-full {{ $modalClass }}"
            {{ $attributes->except(['name']) }}
        >
            @if($title || isset($header))
                <div class="border-b border-gray-200 px-6 py-4">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
                            @if(!$persistent)
                                <button 
                                    type="button" 
                                    @click="show = false"
                                    class="rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <x-atoms.icon name="x" size="sm" />
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <!-- Modal Body -->
            <div class="px-6 py-4">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
