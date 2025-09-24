@props([
    'label' => '',
    'for' => '',
    'name' => '',
    'required' => false,
    'optional' => false,
    'error' => '',
    'success' => '',
    'help' => '',
    'size' => 'md', // sm, md, lg
    'layout' => 'vertical', // vertical, horizontal
    'labelWidth' => 'w-32' // for horizontal layout
])

@php
    $fieldId = $for ?: $name ?: uniqid('field_');
    $state = $error ? 'error' : ($success ? 'success' : 'default');
    $isHorizontal = $layout === 'horizontal';
@endphp

<div class="form-field {{ $isHorizontal ? 'form-field--horizontal' : '' }}">
    @if($label)
        <label 
            for="{{ $fieldId }}" 
            class="form-label {{ $isHorizontal ? $labelWidth : '' }} {{ $required ? 'form-label--required' : '' }} {{ $optional ? 'form-label--optional' : '' }}"
        >
            {{ $label }}
        </label>
    @endif
    
    <div class="{{ $isHorizontal ? 'flex-1' : '' }}">
        <div class="relative">
            {{ $slot }}
            
            @if($error || $success)
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    @if($error)
                        <x-atoms.icon name="exclamation-circle" class="w-5 h-5 text-red-500" />
                    @elseif($success)
                        <x-atoms.icon name="check-circle" class="w-5 h-5 text-green-500" />
                    @endif
                </div>
            @endif
        </div>
        
        @if($error)
            <div class="form-error mt-2">
                <x-atoms.icon name="exclamation-circle" class="form-error__icon" />
                <span>{{ $error }}</span>
            </div>
        @elseif($success)
            <div class="form-success mt-2">
                <x-atoms.icon name="check-circle" class="w-4 h-4 flex-shrink-0" />
                <span>{{ $success }}</span>
            </div>
        @elseif($help)
            <p class="form-help mt-2">{{ $help }}</p>
        @endif
    </div>
</div>
