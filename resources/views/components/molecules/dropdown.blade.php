@props([
    'align' => 'right', // left, right, center
    'width' => '48', // Width in rem, can be 'auto'
    'trigger' => null,
    'contentClasses' => ''
])

@php
    $alignmentClasses = [
        'left' => 'origin-top-left left-0',
        'right' => 'origin-top-right right-0',
        'center' => 'origin-top'
    ];

    $widthClasses = [
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
        'auto' => 'w-auto'
    ];

    $classes = ($alignmentClasses[$align] ?? $alignmentClasses['right']) . ' ' . ($widthClasses[$width] ?? $widthClasses['48']);
@endphp

<div class="relative" x-data="{ open: false }">
    <!-- Trigger -->
    <div @click="open = !open" class="cursor-pointer">
        {{ $trigger }}
    </div>

    <!-- Dropdown Content -->
    <div 
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $classes }} rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none {{ $contentClasses }}"
        {{ $attributes }}
    >
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</div>
