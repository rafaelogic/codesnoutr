@props([
    'type' => 'info', // info, success, warning, danger
    'dismissible' => false,
    'title' => '',
    'icon' => true,
    'size' => 'md' // sm, md, lg
])

@php
    $typeConfig = [
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-800',
            'icon' => 'information-circle',
            'iconColor' => 'text-blue-400'
        ],
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'text' => 'text-green-800',
            'icon' => 'check-circle',
            'iconColor' => 'text-green-400'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-200',
            'text' => 'text-yellow-800',
            'icon' => 'exclamation-circle',
            'iconColor' => 'text-yellow-400'
        ],
        'danger' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'text' => 'text-red-800',
            'icon' => 'exclamation-circle',
            'iconColor' => 'text-red-400'
        ]
    ];
    
    $config = $typeConfig[$type] ?? $typeConfig['info'];
    
    $sizeClasses = [
        'sm' => 'p-3',
        'md' => 'p-4',
        'lg' => 'p-6'
    ];
    
    $classes = implode(' ', [
        'alert rounded-md border',
        $config['bg'],
        $config['border'],
        $sizeClasses[$size] ?? $sizeClasses['md']
    ]);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} role="alert">
    <div class="flex">
        @if($icon)
            <div class="flex-shrink-0">
                <x-atoms.icon 
                    :name="$config['icon']" 
                    size="md" 
                    :class="$config['iconColor']" 
                />
            </div>
        @endif
        
        <div class="{{ $icon ? 'ml-3' : '' }} flex-1">
            @if($title)
                <h3 class="alert-title text-sm font-medium {{ $config['text'] }}">
                    {{ $title }}
                </h3>
            @endif
            
            <div class="alert-message {{ $title ? 'mt-2' : '' }} text-sm {{ $config['text'] }}">
                {{ $slot }}
            </div>
        </div>
        
        @if($dismissible)
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button 
                        type="button" 
                        class="inline-flex rounded-md p-1.5 {{ $config['iconColor'] }} hover:bg-black hover:bg-opacity-10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-transparent focus:ring-blue-600"
                        onclick="this.closest('.alert').remove()"
                    >
                        <span class="sr-only">Dismiss</span>
                        <x-atoms.icon name="x" size="md" />
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
