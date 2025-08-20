@props([
    'title' => '',
    'value' => '',
    'change' => null,
    'changeType' => 'neutral', // positive, negative, neutral
    'icon' => null,
    'href' => null,
    'loading' => false
])

@php
    $changeClasses = [
        'positive' => 'text-green-600',
        'negative' => 'text-red-600',
        'neutral' => 'text-gray-600'
    ];
    
    $changeClass = $changeClasses[$changeType] ?? $changeClasses['neutral'];
    
    $cardClasses = 'bg-white overflow-hidden shadow rounded-lg';
    if ($href) {
        $cardClasses .= ' hover:shadow-md transition-shadow duration-200 cursor-pointer';
    }
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $cardClasses]) }}>
@else
    <div {{ $attributes->merge(['class' => $cardClasses]) }}>
@endif
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    @if($loading)
                        <x-atoms.spinner size="lg" color="secondary" />
                    @elseif($icon)
                        <x-atoms.icon :name="$icon" size="lg" color="secondary" />
                    @endif
                </div>
                
                <div class="{{ $icon || $loading ? 'ml-5' : '' }} w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            {{ $title }}
                        </dt>
                        <dd class="flex items-baseline">
                            @if($loading)
                                <div class="animate-pulse">
                                    <div class="h-8 bg-gray-200 rounded w-24"></div>
                                </div>
                            @else
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ $value }}
                                </div>
                                
                                @if($change !== null)
                                    <div class="ml-2 flex items-baseline text-sm font-semibold {{ $changeClass }}">
                                        @if($changeType === 'positive')
                                            <x-atoms.icon name="arrow-up" size="xs" />
                                        @elseif($changeType === 'negative')
                                            <x-atoms.icon name="arrow-down" size="xs" />
                                        @endif
                                        {{ $change }}
                                    </div>
                                @endif
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        
        @if($slot->isNotEmpty())
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    {{ $slot }}
                </div>
            </div>
        @endif
@if($href)
    </a>
@else
    </div>
@endif
