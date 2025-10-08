@props([
    'title',
    'value',
    'change' => null,
    'changeType' => 'increase', // increase, decrease, neutral
    'icon' => null,
    'color' => 'blue' // blue, green, red, yellow, gray
])

@php
    $colorClasses = [
        'blue' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/30',
            'icon' => 'text-blue-600 dark:text-blue-300',
            'change' => 'text-blue-600 dark:text-blue-300'
        ],
        'green' => [
            'bg' => 'bg-green-50 dark:bg-green-900/30',
            'icon' => 'text-green-600 dark:text-green-300',
            'change' => 'text-green-600 dark:text-green-300'
        ],
        'red' => [
            'bg' => 'bg-red-50 dark:bg-red-900/30',
            'icon' => 'text-red-600 dark:text-red-300',
            'change' => 'text-red-600 dark:text-red-300'
        ],
        'yellow' => [
            'bg' => 'bg-yellow-50 dark:bg-yellow-900/30',
            'icon' => 'text-yellow-600 dark:text-yellow-300',
            'change' => 'text-yellow-600 dark:text-yellow-300'
        ],
        'gray' => [
            'bg' => 'bg-gray-50 dark:bg-gray-900/30',
            'icon' => 'text-gray-600 dark:text-gray-300',
            'change' => 'text-gray-600 dark:text-gray-300'
        ]
    ];
    
    $currentColor = $colorClasses[$color] ?? $colorClasses['blue'];
    
    $changeIcon = match($changeType) {
        'increase' => 'arrow-up',
        'decrease' => 'arrow-down',
        default => 'minus'
    };
@endphp

<x-atoms.surface class="p-5" {{ $attributes }}>
    <div class="flex items-center">
        @if($icon)
            <div class="flex-shrink-0">
                <div class="w-8 h-8 {{ $currentColor['bg'] }} rounded-md flex items-center justify-center">
                    <x-atoms.icon :name="$icon" size="sm" class="{{ $currentColor['icon'] }}" />
                </div>
            </div>
        @endif
        
        <div class="ml-5 w-0 flex-1">
            <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-300 truncate">
                    {{ $title }}
                </dt>
                <dd class="flex items-baseline">
                    <x-atoms.text size="2xl" weight="semibold">
                        {{ $value }}
                    </x-atoms.text>
                    
                    @if($change !== null)
                        <div class="ml-2 flex items-baseline text-sm font-semibold {{ $currentColor['change'] }}">
                            <x-atoms.icon :name="$changeIcon" size="xs" class="self-center flex-shrink-0" />
                            <span class="sr-only">
                                @if($changeType === 'increase') Increased by @elseif($changeType === 'decrease') Decreased by @endif
                            </span>
                            {{ abs($change) }}%
                        </div>
                    @endif
                </dd>
            </dl>
        </div>
    </div>
</x-atoms.surface>