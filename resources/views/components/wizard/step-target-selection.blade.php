<!-- Step 2: Target Selection -->
<div class="space-y-6">
    <div class="text-center mb-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
            @if($scanType === 'file') Select File to Analyze
            @elseif($scanType === 'directory') Choose Directory to Scan
            @else Codebase Location
            @endif
        </h3>
        <p class="text-gray-600 dark:text-gray-400">
            @if($scanType === 'file') Browse and select a specific PHP file to analyze
            @elseif($scanType === 'directory') Choose the directory containing files you want to scan
            @else Your entire Laravel application will be analyzed
            @endif
        </p>
    </div>

    @if($scanType === 'codebase')
    <!-- Codebase Scan Info -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
        <div class="flex items-center">
            <div class="h-12 w-12 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="ml-4 flex-1">
                <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100">Full Application Analysis</h4>
                <p class="text-blue-700 dark:text-blue-300 text-sm">We'll analyze your entire Laravel application located at:</p>
                <p class="text-blue-800 dark:text-blue-200 font-mono text-sm mt-1 break-all">{{ base_path() }}</p>
            </div>
        </div>
        
        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">4</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Analysis Types</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">50+</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Quality Rules</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">∞</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Files Scanned</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">⚡</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Background</div>
            </div>
        </div>
    </div>
    @else
    <!-- File/Directory Selection -->
    <div class="space-y-4">
        <div class="flex flex-col space-y-2">
            <label for="target-input" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                @if($scanType === 'file') File Path @else Directory Path @endif
            </label>
            <div class="flex space-x-3">
                <div class="flex-1">
                    <input type="text" 
                           wire:model.live="target" 
                           id="target-input"
                           placeholder="@if($scanType === 'file') e.g., app/Http/Controllers/UserController.php @else e.g., app/Http/Controllers @endif"
                           class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                </div>
                <button type="button" 
                        wire:click="browseForPath"
                        class="inline-flex items-center px-4 py-3 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    Browse
                </button>
            </div>
        </div>

        @error('target')
        <div class="p-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400 dark:text-red-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm text-red-800 dark:text-red-300">{{ $message }}</p>
            </div>
        </div>
        @enderror

        @if($target && !$errors->has('target'))
        <div class="p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-green-600 dark:text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-green-800 dark:text-green-300">Target Selected</h4>
                    <p class="text-sm text-green-700 dark:text-green-400 mt-1 font-mono">{{ $target }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Suggestions -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Quick Suggestions</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @if($scanType === 'directory')
                <button type="button" 
                        wire:click="$set('target', 'app/Http/Controllers')"
                        class="text-left p-3 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors">
                    <div class="font-medium text-sm text-gray-900 dark:text-white">Controllers</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">app/Http/Controllers</div>
                </button>
                <button type="button" 
                        wire:click="$set('target', 'app/Models')"
                        class="text-left p-3 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors">
                    <div class="font-medium text-sm text-gray-900 dark:text-white">Models</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">app/Models</div>
                </button>
                <button type="button" 
                        wire:click="$set('target', 'app/Services')"
                        class="text-left p-3 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors">
                    <div class="font-medium text-sm text-gray-900 dark:text-white">Services</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">app/Services</div>
                </button>
                <button type="button" 
                        wire:click="$set('target', 'resources/views')"
                        class="text-left p-3 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors">
                    <div class="font-medium text-sm text-gray-900 dark:text-white">Views</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">resources/views</div>
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
