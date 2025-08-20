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
    'name' => null
])

@php
    $baseClasses = 'block w-full border rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0';
    
    $stateClasses = [
        'default' => 'border-gray-300 focus:border-blue-500 focus:ring-blue-500',
        'error' => 'border-red-300 focus:border-red-500 focus:ring-red-500 bg-red-50',
        'success' => 'border-green-300 focus:border-green-500 focus:ring-green-500 bg-green-50',
        'warning' => 'border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50'
    ];
    
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-3 py-2 text-sm',
        'lg' => 'px-4 py-3 text-base'
    ];
    
    $disabledClasses = $disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'bg-white text-gray-900';
    $readonlyClasses = $readonly ? 'bg-gray-50' : '';
    
    $classes = implode(' ', [
        $baseClasses,
        $stateClasses[$state] ?? $stateClasses['default'],
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $disabledClasses,
        $readonlyClasses
    ]);
@endphp

<input 
    type="{{ $type }}"
    @if($id) id="{{ $id }}" @endif
    @if($name) name="{{ $name }}" @endif
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    @if($value) value="{{ $value }}" @endif
    @if($disabled) disabled @endif
    @if($readonly) readonly @endif
    @if($required) required @endif
    {{ $attributes->merge(['class' => $classes]) }}
/>
