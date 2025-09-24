@props([
    'type' => 'info', // info, success, warning, error
    'title' => '',
    'message' => '',
    'dismissible' => true,
    'actions' => [],
    'icon' => null,
    'timeout' => null,
    'position' => 'top-right' // top-right, top-left, bottom-right, bottom-left, top-center, bottom-center
])

@php
    $typeConfig = [
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'border' => 'border-blue-200 dark:border-blue-800',
            'text' => 'text-blue-800 dark:text-blue-200',
            'icon' => 'information-circle'
        ],
        'success' => [
            'bg' => 'bg-green-50 dark:bg-green-900/20',
            'border' => 'border-green-200 dark:border-green-800',
            'text' => 'text-green-800 dark:text-green-200',
            'icon' => 'check-circle'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',
            'border' => 'border-yellow-200 dark:border-yellow-800',
            'text' => 'text-yellow-800 dark:text-yellow-200',
            'icon' => 'exclamation-triangle'
        ],
        'error' => [
            'bg' => 'bg-red-50 dark:bg-red-900/20',
            'border' => 'border-red-200 dark:border-red-800',
            'text' => 'text-red-800 dark:text-red-200',
            'icon' => 'x-circle'
        ]
    ];
    
    $config = $typeConfig[$type] ?? $typeConfig['info'];
    $iconName = $icon ?? $config['icon'];
    
    $positionClasses = [
        'top-right' => 'fixed top-4 right-4 z-50',
        'top-left' => 'fixed top-4 left-4 z-50',
        'bottom-right' => 'fixed bottom-4 right-4 z-50',
        'bottom-left' => 'fixed bottom-4 left-4 z-50',
        'top-center' => 'fixed top-4 left-1/2 transform -translate-x-1/2 z-50',
        'bottom-center' => 'fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50'
    ];
@endphp

<div 
    {{ $attributes->merge([
        'class' => implode(' ', [
            'max-w-sm w-full rounded-lg border shadow-lg p-4',
            $config['bg'],
            $config['border'],
            $config['text'],
            $positionClasses[$position] ?? $positionClasses['top-right'],
            'animate-slide-in-down'
        ])
    ]) }}
    @if($timeout) 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => show = false, {{ $timeout }})"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
    @else
        x-data="{ show: true }" 
        x-show="show"
    @endif
    role="alert"
    aria-live="polite"
>
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <x-atoms.icon :name="$iconName" class="w-5 h-5 mt-0.5" />
        </div>
        
        <div class="ml-3 flex-1">
            @if($title)
                <h4 class="text-sm font-medium mb-1">{{ $title }}</h4>
            @endif
            
            @if($message)
                <p class="text-sm">{{ $message }}</p>
            @elseif($slot->isNotEmpty())
                <div class="text-sm">{{ $slot }}</div>
            @endif
            
            @if(!empty($actions))
                <div class="mt-3 flex space-x-3">
                    @foreach($actions as $action)
                        <x-atoms.button
                            :variant="$action['variant'] ?? 'ghost'"
                            size="xs"
                            :href="$action['href'] ?? null"
                            onclick="{{ $action['onclick'] ?? '' }}"
                        >
                            {{ $action['label'] }}
                        </x-atoms.button>
                    @endforeach
                </div>
            @endif
        </div>
        
        @if($dismissible)
            <div class="ml-4 flex-shrink-0">
                <button 
                    @click="show = false"
                    class="inline-flex text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-current rounded-md p-1.5"
                    aria-label="Dismiss notification"
                >
                    <x-atoms.icon name="x-mark" size="sm" />
                </button>
            </div>
        @endif
    </div>
</div>