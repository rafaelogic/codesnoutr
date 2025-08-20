@props([
    'name' => '',
    'id' => '',
    'checked' => false,
    'value' => '1',
    'size' => 'md', // 'sm', 'md', 'lg'
    'color' => 'primary', // 'primary', 'secondary', 'success', 'warning', 'danger'
    'disabled' => false,
    'label' => '',
    'description' => ''
])

@php
    $toggleId = $id ?: $name;
    
    $sizeClasses = [
        'sm' => 'h-4 w-8',
        'md' => 'h-6 w-11',
        'lg' => 'h-8 w-14'
    ];
    
    $dotSizeClasses = [
        'sm' => 'h-3 w-3',
        'md' => 'h-5 w-5',
        'lg' => 'h-7 w-7'
    ];
    
    $translateClasses = [
        'sm' => 'translate-x-4',
        'md' => 'translate-x-5',
        'lg' => 'translate-x-6'
    ];
    
    $colorClasses = [
        'primary' => 'focus:ring-blue-500 bg-blue-600',
        'secondary' => 'focus:ring-gray-500 bg-gray-600',
        'success' => 'focus:ring-green-500 bg-green-600',
        'warning' => 'focus:ring-yellow-500 bg-yellow-600',
        'danger' => 'focus:ring-red-500 bg-red-600'
    ];
    
    $toggleSize = $sizeClasses[$size] ?? $sizeClasses['md'];
    $dotSize = $dotSizeClasses[$size] ?? $dotSizeClasses['md'];
    $translate = $translateClasses[$size] ?? $translateClasses['md'];
    $toggleColor = $colorClasses[$color] ?? $colorClasses['primary'];
    
    $attributes = $attributes->merge([
        'class' => 'sr-only',
        'type' => 'checkbox',
        'name' => $name,
        'id' => $toggleId,
        'value' => $value
    ]);
    
    if ($checked) {
        $attributes = $attributes->merge(['checked' => true]);
    }
    
    if ($disabled) {
        $attributes = $attributes->merge(['disabled' => true]);
    }
@endphp

<div class="flex items-center">
    <input {{ $attributes }} />
    
    <label 
        for="{{ $toggleId }}" 
        class="{{ $toggleSize }} bg-gray-200 relative inline-flex flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus-within:ring-2 focus-within:ring-offset-2 {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
    >
        <span 
            class="{{ $dotSize }} bg-white inline-block rounded-full shadow transform ring-0 transition duration-200 ease-in-out"
            :class="$refs.{{ $toggleId }}.checked ? '{{ $translate }}' : 'translate-x-0'"
            x-ref="{{ $toggleId }}Dot"
        ></span>
        
        <script>
            document.addEventListener('alpine:init', () => {
                const checkbox = document.getElementById('{{ $toggleId }}');
                const label = checkbox.nextElementSibling;
                const dot = label.querySelector('span');
                
                function updateToggle() {
                    if (checkbox.checked) {
                        label.classList.add('{{ $toggleColor }}');
                        label.classList.remove('bg-gray-200');
                        dot.classList.add('{{ $translate }}');
                        dot.classList.remove('translate-x-0');
                    } else {
                        label.classList.remove('{{ $toggleColor }}');
                        label.classList.add('bg-gray-200');
                        dot.classList.remove('{{ $translate }}');
                        dot.classList.add('translate-x-0');
                    }
                }
                
                // Initialize
                updateToggle();
                
                // Listen for changes
                checkbox.addEventListener('change', updateToggle);
            });
        </script>
    </label>
    
    @if($label || $description)
        <div class="ml-3">
            @if($label)
                <label for="{{ $toggleId }}" class="text-sm font-medium text-gray-900 cursor-pointer">
                    {{ $label }}
                </label>
            @endif
            
            @if($description)
                <p class="text-sm text-gray-500">{{ $description }}</p>
            @endif
        </div>
    @endif
</div>
