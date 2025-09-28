@props([
    'type' => 'text',
    'size' => 'md', // sm, md, lg
    'state' => 'default', // default, error, success, warning
    'placeholder' => '',
    'disabled' => false,
    'readonly' => false,
    'required' => false,
    'value' => '',
    'id' => null,
    'name' => null,
    'rows' => 4,
    'min' => null,
    'max' => null
])

@php
    // Base classes with enhanced contrast for both light and dark modes
    $baseClasses = 'input block w-full rounded-md shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-500';
    
    $stateClasses = [
        'default' => 'input--default border-gray-400 dark:border-gray-600 focus:border-blue-600 dark:focus:border-blue-400 focus:ring-blue-600 dark:focus:ring-blue-400 bg-gray-100',
        'error' => 'input--error border-red-400 dark:border-red-600 focus:border-red-600 dark:focus:border-red-400 focus:ring-red-600 dark:focus:ring-red-400 bg-red-50 dark:bg-red-900/20 bg-gray-100 dark:bg-gray-900',
        'success' => 'input--success border-green-400 dark:border-green-600 focus:border-green-600 dark:focus:border-green-400 focus:ring-green-600 dark:focus:ring-green-400 bg-green-50 dark:bg-green-900/20 bg-gray-100 dark:bg-gray-900',
        'warning' => 'input--warning border-yellow-400 dark:border-yellow-600 focus:border-yellow-600 dark:focus:border-yellow-400 focus:ring-yellow-600 dark:focus:ring-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 bg-gray-100 dark:bg-gray-900'
    ];
    
    $sizeClasses = [
        'sm' => 'input--sm px-3 py-2.5 text-sm',
        'md' => 'input--md px-3 py-3 text-sm',
        'lg' => 'input--lg px-4 py-4 text-base'
    ];
    
    $disabledClasses = $disabled ? 'input--disabled bg-gray-400 dark:bg-gray-800 text-gray-600 dark:text-gray-400 cursor-not-allowed border-gray-300 dark:border-gray-700 opacity-80' : '';
    
    $classes = implode(' ', array_filter([
        $baseClasses,
        $stateClasses[$state] ?? $stateClasses['default'],
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $disabledClasses,
        $readonly ? 'readonly' : ''
    ]));
@endphp

@if($type === 'textarea')
    <textarea 
        @if($id) id="{{ $id }}" @endif
        @if($name) name="{{ $name }}" @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        @if($required) required @endif
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => $classes . ' resize-none']) }}
    >{{ $value }}</textarea>
@elseif($type === 'select')
    <select 
        @if($id) id="{{ $id }}" @endif
        @if($name) name="{{ $name }}" @endif
        @if($disabled) disabled @endif
        @if($required) required @endif
        {{ $attributes->merge(['class' => $classes . ' pr-10 appearance-none bg-no-repeat bg-right bg-[length:16px_16px] bg-[url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%23374151\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e")] dark:bg-[url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%23d1d5db\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e")]']) }}
    >
        {{ $slot }}
    </select>
@elseif($type === 'checkbox')
    <input 
        type="checkbox"
        @if($id) id="{{ $id }}" @endif
        @if($name) name="{{ $name }}" @endif
        @if($value) value="{{ $value }}" @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'h-4 w-4 text-indigo-600 dark:text-indigo-400 border-gray-400 dark:border-gray-600 rounded focus:ring-indigo-600 dark:focus:ring-indigo-400 bg-white dark:bg-gray-700 focus:ring-offset-white dark:focus:ring-offset-gray-800']) }}
    />
@else
    <input 
        type="{{ $type }}"
        @if($id) id="{{ $id }}" @endif
        @if($name) name="{{ $name }}" @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($value) value="{{ $value }}" @endif
        @if($min) min="{{ $min }}" @endif
        @if($max) max="{{ $max }}" @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        @if($required) required @endif
        {{ $attributes->merge(['class' => $classes]) }}
    />
@endif
