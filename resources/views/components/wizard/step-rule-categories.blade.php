<!-- Step 3: Rule Categories -->
<div class="space-y-6">
    <div class="text-center mb-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Configure Analysis Rules</h3>
        <p class="text-gray-600 dark:text-gray-400">Choose which types of issues you want to detect in your code</p>
    </div>

    <!-- Quick Actions -->
    <div class="flex justify-center space-x-4 mb-6">
        <button type="button" 
                wire:click="selectAllCategories"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-200 dark:hover:bg-indigo-800 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Select All
        </button>
        <button type="button" 
                wire:click="deselectAllCategories"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Deselect All
        </button>
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
                            @if($category['icon'] === 'shield-check')
                            <svg class="h-5 w-5 text-{{ $category['color'] }}-600 dark:text-{{ $category['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            @elseif($category['icon'] === 'lightning-bolt')
                            <svg class="h-5 w-5 text-{{ $category['color'] }}-600 dark:text-{{ $category['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            @elseif($category['icon'] === 'star')
                            <svg class="h-5 w-5 text-{{ $category['color'] }}-600 dark:text-{{ $category['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            @else
                            <svg class="h-5 w-5 text-{{ $category['color'] }}-600 dark:text-{{ $category['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ $category['title'] }}</h4>
                        </div>
                    </div>
                    
                    <!-- Checkbox Indicator -->
                    <div class="hidden peer-checked:block">
                        <div class="h-6 w-6 bg-{{ $category['color'] }}-600 rounded-full flex items-center justify-center">
                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div class="peer-checked:hidden">
                        <div class="h-6 w-6 border-2 border-gray-300 dark:border-gray-500 rounded-full"></div>
                    </div>
                </div>

                <!-- Description -->
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $category['description'] }}</p>

                <!-- Features -->
                <div class="space-y-2">
                    @if($key === 'security')
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        SQL Injection Detection
                    </div>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        XSS Vulnerability Checks
                    </div>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Hardcoded Credentials
                    </div>
                    @elseif($key === 'performance')
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        N+1 Query Detection
                    </div>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Memory Usage Issues
                    </div>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Caching Opportunities
                    </div>
                    @elseif($key === 'quality')
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Code Complexity
                    </div>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Coding Standards
                    </div>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Documentation Issues
                    </div>
                    @else
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Eloquent Best Practices
                    </div>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Route Optimization
                    </div>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Blade Template Issues
                    </div>
                    @endif
                </div>
            </label>
        </div>
        @endforeach
    </div>

    @error('ruleCategories')
    <div class="mt-6 p-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-800 rounded-lg">
        <div class="flex">
            <svg class="h-5 w-5 text-red-400 dark:text-red-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="ml-3 text-sm text-red-800 dark:text-red-300">{{ $message }}</p>
        </div>
    </div>
    @enderror

    @if(count($ruleCategories) > 0)
    <div class="mt-6 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-800 rounded-lg">
        <div class="flex items-start">
            <svg class="h-5 w-5 text-green-600 dark:text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-green-800 dark:text-green-300">{{ count($ruleCategories) }} Rule Categories Selected</h4>
                <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                    {{ implode(', ', array_map('ucfirst', $ruleCategories)) }}
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
