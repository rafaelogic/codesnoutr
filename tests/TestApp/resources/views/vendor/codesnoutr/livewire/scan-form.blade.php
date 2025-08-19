<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">New Scan</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Configure and start a new code analysis scan</p>
    </div>

    @if($isScanning)
    <!-- Scanning Progress -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8 transition-theme">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Scanning in Progress</h3>
            <button wire:click="cancelScan" 
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm">
                Cancel Scan
            </button>
        </div>
        
        <!-- Progress Bar -->
        <div class="mb-4">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                <span>Progress</span>
                <span>{{ $scanProgress }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" 
                     style="width: {{ $scanProgress }}%"></div>
            </div>
        </div>

        <!-- Scan Details -->
        @if($currentScan)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Type:</span>
                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $this->getScanTypeLabel($currentScan->type) }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Target:</span>
                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $currentScan->target ?: 'Full codebase' }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Started:</span>
                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $currentScan->started_at->format('H:i:s') }}</span>
            </div>
        </div>
        @endif
    </div>
    @else
    <!-- Scan Configuration Form -->
    <form wire:submit.prevent="startScan" class="space-y-8">
        <!-- Scan Type Selection -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Scan Type</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label class="relative">
                    <input type="radio" wire:model.live="scanType" value="file" class="peer sr-only">
                    <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-pointer peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/50 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400 peer-checked:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Single File</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Scan a specific PHP file</div>
                            </div>
                        </div>
                    </div>
                </label>

                <label class="relative">
                    <input type="radio" wire:model.live="scanType" value="directory" class="peer sr-only">
                    <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-pointer peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/50 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400 peer-checked:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Directory</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Scan all files in a directory</div>
                            </div>
                        </div>
                    </div>
                </label>

                <label class="relative">
                    <input type="radio" wire:model.live="scanType" value="codebase" class="peer sr-only">
                    <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-pointer peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/50 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400 peer-checked:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Full Codebase</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Scan entire project</div>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Target Input -->
        @if($scanType !== 'codebase')
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ $scanType === 'file' ? 'File Path' : 'Directory Path' }}
            </h3>
            <div>
                <label for="target" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ $scanType === 'file' ? 'Enter the path to the PHP file you want to scan' : 'Enter the directory path to scan' }}
                </label>
                <input type="text" 
                       id="target"
                       wire:model.blur="target" 
                       placeholder="{{ $scanType === 'file' ? 'app/Http/Controllers/UserController.php' : 'app/Http/Controllers' }}"
                       class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('target')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        @endif

        <!-- Rule Categories -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Rule Categories</h3>
                <div class="flex space-x-2">
                    <button type="button" wire:click="selectAllCategories" 
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                        Select All
                    </button>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <button type="button" wire:click="deselectAllCategories" 
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                        Deselect All
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach(['security', 'performance', 'quality', 'laravel'] as $category)
                <label class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               wire:model.live="ruleCategories" 
                               value="{{ $category }}"
                               class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                    </div>
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $this->getCategoryLabel($category) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $this->getCategoryDescription($category) }}
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
            
            @error('ruleCategories')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Advanced Options -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-theme">
            <div class="p-6">
                <button type="button" 
                        wire:click="toggleAdvanced"
                        class="flex items-center justify-between w-full text-left">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Advanced Options</h3>
                    <svg class="h-5 w-5 text-gray-400 transform transition-transform duration-200 {{ $showAdvanced ? 'rotate-180' : '' }}" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            @if($showAdvanced)
            <div class="border-t border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <!-- Ignore Options -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               wire:model.live="scanOptions.ignore_vendor" 
                               class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Ignore vendor/</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               wire:model.live="scanOptions.ignore_node_modules" 
                               class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Ignore node_modules/</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               wire:model.live="scanOptions.ignore_storage" 
                               class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Ignore storage/</span>
                    </label>
                </div>

                <!-- File Size Limit -->
                <div>
                    <label for="max_file_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Maximum File Size (MB)
                    </label>
                    <input type="number" 
                           id="max_file_size"
                           wire:model.blur="scanOptions.max_file_size" 
                           min="1" 
                           max="100" 
                           class="block w-full md:w-32 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- File Extensions -->
                <div>
                    <label for="file_extensions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        File Extensions (comma-separated)
                    </label>
                    <input type="text" 
                           id="file_extensions"
                           wire:model.blur="scanOptions.file_extensions" 
                           placeholder="php,js,vue"
                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>
            @endif
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between">
            <div>
                @if($errors->any())
                <div class="text-sm text-red-600 dark:text-red-400">
                    Please fix the errors above before starting the scan.
                </div>
                @endif
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="{{ route('codesnoutr.dashboard') }}" 
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="ruleCategories.length === 0">
                    <span wire:loading.remove wire:target="startScan">Start Scan</span>
                    <span wire:loading wire:target="startScan" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Starting...
                    </span>
                </button>
            </div>
        </div>
    </form>
    @endif
</div>

@push('scripts')
<script>
    // Auto-refresh scan progress
    let progressInterval;
    
    document.addEventListener('livewire:load', function () {
        Livewire.on('scan-progress-updated', (progress) => {
            // Additional progress handling if needed
        });

        Livewire.on('scan-completed', (data) => {
            clearInterval(progressInterval);
            setTimeout(() => {
                window.location.href = '{{ route("codesnoutr.results") }}/' + data.scanId;
            }, 2000);
        });

        Livewire.on('scan-cancelled', () => {
            clearInterval(progressInterval);
        });
    });

    // Start progress polling when scan begins
    @if($isScanning)
        progressInterval = setInterval(() => {
            @this.call('updateProgress');
        }, 1000);
    @endif
</script>
@endpush
