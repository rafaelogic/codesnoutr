@props([
    'name' => '',
    'id' => '',
    'placeholder' => 'Choose an option...',
    'size' => 'md', // 'sm', 'md', 'lg'
    'state' => 'default', // 'default', 'error', 'success'
    'disabled' => false,
    'multiple' => false,
    'options' => [],
    'value' => null,
    'optgroups' => []
])

@php
    $selectId = $id ?: $name;
    
    $baseClasses = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500';
    
    $sizeClasses = [
        'sm' => 'text-sm py-1.5 px-3',
        'md' => 'text-sm py-2 px-3',
        'lg' => 'text-base py-3 px-4'
    ];
    
    $stateClasses = [
        'default' => 'border-gray-300 focus:border-blue-500 focus:ring-blue-500',
        'error' => 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500',
        'success' => 'border-green-300 focus:border-green-500 focus:ring-green-500'
    ];
    
    $selectSize = $sizeClasses[$size] ?? $sizeClasses['md'];
    $selectState = $stateClasses[$state] ?? $stateClasses['default'];
    
    $selectClasses = $baseClasses . ' ' . $selectSize . ' ' . $selectState;
    
    if ($disabled) {
        $selectClasses .= ' bg-gray-50 text-gray-500 cursor-not-allowed';
    }
    
    $attributes = $attributes->merge([
        'class' => $selectClasses,
        'name' => $name,
        'id' => $selectId
    ]);
    
    if ($disabled) {
        $attributes = $attributes->merge(['disabled' => true]);
    }
    
    if ($multiple) {
        $attributes = $attributes->merge(['multiple' => true]);
    }
@endphp

<select {{ $attributes }}>
    @if($placeholder && !$multiple)
        <option value="" {{ !$value ? 'selected' : '' }}>{{ $placeholder }}</option>
    @endif
    
    @if(!empty($optgroups))
        @foreach($optgroups as $group)
            <optgroup label="{{ $group['label'] }}">
                @foreach($group['options'] as $optionValue => $optionLabel)
                    <option 
                        value="{{ $optionValue }}" 
                        {{ ($value == $optionValue || (is_array($value) && in_array($optionValue, $value))) ? 'selected' : '' }}
                    >
                        {{ $optionLabel }}
                    </option>
                @endforeach
            </optgroup>
        @endforeach
    @elseif(!empty($options))
        @foreach($options as $optionValue => $optionLabel)
            <option 
                value="{{ $optionValue }}" 
                {{ ($value == $optionValue || (is_array($value) && in_array($optionValue, $value))) ? 'selected' : '' }}
            >
                {{ $optionLabel }}
            </option>
        @endforeach
    @else
        {{ $slot }}
    @endif
</select>
