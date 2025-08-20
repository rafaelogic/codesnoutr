@props([
    'label' => '',
    'for' => '',
    'required' => false,
    'error' => '',
    'help' => '',
    'size' => 'md' // sm, md, lg
])

@php
    $fieldId = $for ?: uniqid('field_');
@endphp

<div {{ $attributes->merge(['class' => 'form-field space-y-1']) }}>
    @if($label)
        <x-atoms.label :for="$fieldId" :required="$required" :size="$size">
            {{ $label }}
        </x-atoms.label>
    @endif
    
    <div class="form-input-wrapper">
        {{ $slot }}
    </div>
    
    @if($error)
        <div class="form-error flex items-center space-x-1 text-red-600">
            <x-atoms.icon name="exclamation-circle" size="sm" color="danger" />
            <span class="text-sm">{{ $error }}</span>
        </div>
    @endif
    
    @if($help && !$error)
        <div class="form-help">
            <span class="text-sm text-gray-500">{{ $help }}</span>
        </div>
    @endif
</div>
