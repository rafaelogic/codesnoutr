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
    $baseClasses = 'input';
    
    $stateClasses = [
        'default' => 'input--default',
        'error' => 'input--error',
        'success' => 'input--success',
        'warning' => 'input--warning'
    ];
    
    $sizeClasses = [
        'sm' => 'input--sm',
        'md' => 'input--md',
        'lg' => 'input--lg'
    ];
    
    $classes = implode(' ', array_filter([
        $baseClasses,
        $stateClasses[$state] ?? $stateClasses['default'],
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $disabled ? 'input--disabled' : '',
        $readonly ? 'readonly' : ''
    ]));
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
