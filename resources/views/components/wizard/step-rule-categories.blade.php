<!-- Step 3: Rule Categories -->
<div class="space-y-6">
    <div class="text-center mb-8">
        <x-atoms.text as="h3" size="lg" weight="medium" class="mb-2">
            Configure Analysis Rules
        </x-atoms.text>
        <x-atoms.text color="muted">
            Choose which types of issues you want to detect in your code
        </x-atoms.text>
    </div>

    <!-- Quick Actions -->
    <div class="flex justify-center space-x-4 mb-6">
        <x-atoms.button 
            type="button" 
            wire:click="selectAllCategories"
            variant="outline-primary"
            size="sm"
            icon="check"
        >
            Select All
        </x-atoms.button>
        <x-atoms.button 
            type="button" 
            wire:click="deselectAllCategories"
            variant="outline-secondary"
            size="sm"
            icon="x-mark"
        >
            Deselect All
        </x-atoms.button>
    </div>

    <!-- Rule Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($this->getAllCategories() as $key => $category)
        <div class="relative">
            <input type="checkbox" 
                   wire:model.live="ruleCategories" 
                   value="{{ $key }}" 
                   id="category-{{ $key }}" 
                   @checked(in_array($key, $ruleCategories))
                   class="sr-only peer">
            <label for="category-{{ $key }}" 
                   class="flex flex-col p-6 bg-white dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-{{ $category['color'] }}-300 dark:hover:border-{{ $category['color'] }}-500 transition-all duration-200 peer-checked:border-{{ $category['color'] }}-600 peer-checked:bg-{{ $category['color'] }}-50 dark:peer-checked:bg-{{ $category['color'] }}-900 peer-checked:ring-2 peer-checked:ring-{{ $category['color'] }}-500 peer-checked:ring-offset-2">
                
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center">
                        <div class="h-10 w-10 bg-{{ $category['color'] }}-100 dark:bg-{{ $category['color'] }}-900 rounded-lg flex items-center justify-center mr-3">
                            <x-atoms.icon 
                                :name="$category['icon']" 
                                size="sm" 
                                class="text-{{ $category['color'] }}-600 dark:text-{{ $category['color'] }}-400" 
                            />
                        </div>
                        <div>
                            <x-atoms.text weight="semibold">{{ $category['title'] }}</x-atoms.text>
                        </div>
                    </div>
                    
                    <!-- Checkbox Indicator -->
                    <div class="relative">
                        <div class="h-6 w-6 border-2 border-gray-300 dark:border-gray-500 rounded-full flex items-center justify-center">
                            <svg class="h-4 w-4 text-white opacity-0 transition-opacity duration-200" 
                                 :class="{'opacity-100': ruleCategories.includes('{{ $key }}')}"
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="absolute inset-0 h-6 w-6 bg-{{ $category['color'] }}-600 rounded-full transition-all duration-200 -z-10"
                             :class="{'scale-100 opacity-100': ruleCategories.includes('{{ $key }}'), 'scale-0 opacity-0': !ruleCategories.includes('{{ $key }}')}"></div>
                    </div>
                </div>

                <!-- Description -->
                <x-atoms.text size="sm" color="muted" class="mb-4">{{ $category['description'] }}</x-atoms.text>

                <!-- Features -->
                <div class="space-y-2">
                    @if($key === 'security')
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">SQL Injection Detection</x-atoms.text>
                    </div>
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">XSS Vulnerability Checks</x-atoms.text>
                    </div>
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">Hardcoded Credentials</x-atoms.text>
                    </div>
                    @elseif($key === 'performance')
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">N+1 Query Detection</x-atoms.text>
                    </div>
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">Memory Usage Issues</x-atoms.text>
                    </div>
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">Caching Opportunities</x-atoms.text>
                    </div>
                    @elseif($key === 'quality')
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">Code Complexity</x-atoms.text>
                    </div>
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">Coding Standards</x-atoms.text>
                    </div>
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">Documentation Issues</x-atoms.text>
                    </div>
                    @else
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">Eloquent Best Practices</x-atoms.text>
                    </div>
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">Route Optimization</x-atoms.text>
                    </div>
                    <div class="flex items-center">
                        <x-atoms.icon name="check" size="xs" class="mr-2 text-gray-500 dark:text-gray-400" />
                        <x-atoms.text size="xs" color="muted">Blade Template Issues</x-atoms.text>
                    </div>
                    @endif
                </div>
            </label>
        </div>
        @endforeach
    </div>

    @error('ruleCategories')
    <x-atoms.alert variant="danger" size="md" class="mt-6">
        <x-slot name="icon">x-circle</x-slot>
        {{ $message }}
    </x-atoms.alert>
    @enderror

    @if(count($ruleCategories) > 0)
    <x-atoms.alert variant="success" size="md" class="mt-6">
        <x-slot name="icon">check-circle</x-slot>
        <x-slot name="title">{{ count($ruleCategories) }} Rule Categories Selected</x-slot>
        <x-atoms.text size="sm">
            {{ implode(', ', array_map('ucfirst', $ruleCategories)) }}
        </x-atoms.text>
    </x-atoms.alert>
    @endif
</div>
