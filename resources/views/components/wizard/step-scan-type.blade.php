<!-- Step 1: Scan Type Selection -->
<div class="space-y-6">
    <div class="text-center mb-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Select Analysis Type</h3>
        <p class="text-gray-600 dark:text-gray-400">Choose what you'd like to analyze with CodeSnoutr</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Single File -->
        <div class="relative">
            <input type="radio" wire:model.live="scanType" value="file" id="scan-file" class="sr-only peer">
            <label for="scan-file" class="flex flex-col items-center p-6 bg-white dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900 peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:ring-offset-2">
                <div class="h-12 w-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mb-4 peer-checked:bg-indigo-600 peer-checked:text-white">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400 peer-checked:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Single File</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center">Analyze a specific PHP file for issues and vulnerabilities</p>
                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-600 rounded">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                        </svg>
                        Fast & Focused
                    </span>
                </div>
            </label>
        </div>

        <!-- Directory -->
        <div class="relative">
            <input type="radio" wire:model.live="scanType" value="directory" id="scan-directory" class="sr-only peer">
            <label for="scan-directory" class="flex flex-col items-center p-6 bg-white dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900 peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:ring-offset-2">
                <div class="h-12 w-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mb-4 peer-checked:bg-indigo-600 peer-checked:text-white">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400 peer-checked:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Directory</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center">Scan all PHP files within a specific directory</p>
                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-600 rounded">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 01.707.293L10.414 5H16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm2 3v8h10V8H5z" clip-rule="evenodd"/>
                        </svg>
                        Targeted Analysis
                    </span>
                </div>
            </label>
        </div>

        <!-- Full Codebase -->
        <div class="relative">
            <input type="radio" wire:model.live="scanType" value="codebase" id="scan-codebase" class="sr-only peer">
            <label for="scan-codebase" class="flex flex-col items-center p-6 bg-white dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900 peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:ring-offset-2">
                <div class="h-12 w-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4 peer-checked:bg-indigo-600 peer-checked:text-white">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400 peer-checked:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Full Codebase</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center">Comprehensive analysis of your entire Laravel application</p>
                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-600 rounded">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                        </svg>
                        Most Comprehensive
                    </span>
                </div>
            </label>
        </div>
    </div>

    @error('scanType')
    <div class="mt-4 p-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-800 rounded-lg">
        <div class="flex">
            <svg class="h-5 w-5 text-red-400 dark:text-red-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="ml-3 text-sm text-red-800 dark:text-red-300">{{ $message }}</p>
        </div>
    </div>
    @enderror

    @if($scanType)
    <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900 border border-indigo-200 dark:border-indigo-800 rounded-lg">
        <div class="flex items-start">
            <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-indigo-800 dark:text-indigo-300">{{ ucfirst($scanType) }} Scan Selected</h4>
                <p class="text-sm text-indigo-700 dark:text-indigo-400 mt-1">{{ $this->getScanTypeDescription($scanType) }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
