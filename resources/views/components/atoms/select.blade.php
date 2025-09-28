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
    
    // Enhanced base classes with strong dark mode support and better contrast
    $baseClasses = 'block w-full rounded-md shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 appearance-none bg-gray-100 bg-no-repeat bg-right bg-[length:16px_16px] pr-10';
    
    // Light and dark mode backgrounds with high contrast
    $backgroundClasses = 'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100';
    
    // Enhanced arrow icons with better contrast for light and dark modes
    $arrowClasses = 'bg-[url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%23374151\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e")] dark:bg-[url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%23d1d5db\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e")]';
    
    $sizeClasses = [
        'sm' => 'text-sm py-2.5 px-3 h-10',
        'md' => 'text-sm py-3 px-3 h-12',
        'lg' => 'text-base py-4 px-4 h-14'
    ];
    
    $stateClasses = [
        'default' => 'border-gray-400 dark:border-gray-600 focus:border-blue-600 dark:focus:border-blue-400 focus:ring-blue-600 dark:focus:ring-blue-400 bg-gray-100 dark:bg-gray-800',
        'error' => 'border-red-400 dark:border-red-600 focus:border-red-600 dark:focus:border-red-400 focus:ring-red-600 dark:focus:ring-red-400 bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-100 bg-gray-100 dark:bg-gray-800',
        'success' => 'border-green-400 dark:border-green-600 focus:border-green-600 dark:focus:border-green-400 focus:ring-green-600 dark:focus:ring-green-400 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-100 bg-gray-100 dark:bg-gray-800',
        'warning' => 'border-yellow-400 dark:border-yellow-600 focus:border-yellow-600 dark:focus:border-yellow-400 focus:ring-yellow-600 dark:focus:ring-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-100 bg-gray-100 dark:bg-gray-800'
    ];
    
    $selectSize = $sizeClasses[$size] ?? $sizeClasses['md'];
    $selectState = $stateClasses[$state] ?? $stateClasses['default'];
    
    // Combine all classes with proper spacing
    $allClasses = [
        $baseClasses,
        $backgroundClasses,
        $arrowClasses,
        $selectSize,
        $selectState
    ];
    
    if ($disabled) {
        $allClasses[] = 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 cursor-not-allowed border-gray-300 dark:border-gray-700 opacity-80';
    }
    
    $selectClasses = implode(' ', array_filter($allClasses));
    
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
