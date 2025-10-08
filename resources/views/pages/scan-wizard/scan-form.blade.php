<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">New Scan</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Configure and start a new code analysis scan</p>
    </div>

    @if($isScanning || $isCheckingQueue)
    <!-- Scanning Progress or Queue Check -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8 transition-theme">
        @if($isCheckingQueue)
        <!-- Queue Status Check -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Preparing Scan</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $queueMessage }}</p>
            </div>
            @php $badge = $this->getQueueStatusBadge(); @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $badge['class'] }}">
                {{ $badge['text'] }}
            </span>
        </div>
        
        <!-- Queue Check Progress -->
        <div class="mb-4">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                <span>Status</span>
                <span wire:loading wire:target="checkAndStartQueue">
                    <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </div>
            
            @if($queueStatus === 'checking')
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 50%"></div>
            </div>
            @elseif($queueStatus === 'ready')
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 100%"></div>
            </div>
            @elseif($queueStatus === 'error')
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-red-600 h-2 rounded-full" style="width: 100%"></div>
            </div>
            @endif
        </div>

        @if($queueStatus === 'error')
        <div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-md p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Queue Setup Failed</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <p>{{ $queueMessage }}</p>
                    </div>
                    <div class="mt-3">
                        <button wire:click="resetQueueStatus" 
                                class="bg-red-100 dark:bg-red-800 px-3 py-1 rounded-md text-sm font-medium text-red-800 dark:text-red-200 hover:bg-red-200 dark:hover:bg-red-700">
                            Retry Queue Check
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @elseif($queueStatus === 'ready')
        <div class="bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-md p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Queue Ready</h3>
                    <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                        <p>{{ $queueMessage }}. Starting scan...</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @else
        <!-- Scanning Progress -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Scanning in Progress</h3>
                @if($currentScan && $currentScan->path)
                <div class="mt-2 inline-flex items-center px-3 py-1 bg-indigo-50 dark:bg-indigo-900 border border-indigo-200 dark:border-indigo-800 rounded-md">
                    <svg class="w-4 h-4 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/>
                    </svg>
                    <span class="text-sm text-indigo-700 dark:text-indigo-300 font-medium">Path:</span>
                    <span class="text-sm text-indigo-800 dark:text-indigo-200 ml-1 font-mono">{{ $currentScan->path }}</span>
                </div>
                @endif
            </div>
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
                    <input type="radio" wire:model.live="scanType" value="file" class="peer sr-only" / />
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
                    <input type="radio" wire:model.live="scanType" value="directory" class="peer sr-only" / />
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
                    <input type="radio" wire:model.live="scanType" value="codebase" class="peer sr-only" / />
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
                <div class="flex space-x-2">
                    <input type="text" 
                           id="target"
                           wire:model.blur="target" 
                           placeholder="{{ $scanType === 'file' ? 'app/Http/Controllers/UserController.php' : 'app/Http/Controllers' }}"
                           class="flex-1 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    <button type="button" 
                            wire:click="browseForPath"
                            class="px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </button>
                </div>
                @error('target')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        @else
        <!-- Codebase Target Display -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-theme">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Scan Target</h3>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-4">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Full codebase scan will analyze all files in:
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-mono bg-white dark:bg-gray-800 px-2 py-1 rounded mt-1 break-all">
                            {{ $target ?: base_path() }}
                        </p>
                    </div>
                </div>
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
                <label class="relative flex items-start cursor-pointer">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               wire:model.live="ruleCategories" 
                               value="{{ $category }}"
                               @checked(in_array($category, $ruleCategories))
                               class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 focus:ring-offset-0" />
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
            
            @error('scan')
                <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-md">
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                </div>
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
                               class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500" />
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Ignore vendor/</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               wire:model.live="scanOptions.ignore_node_modules" 
                               class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500" />
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Ignore node_modules/</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               wire:model.live="scanOptions.ignore_storage" 
                               class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500" />
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Ignore storage/</span>
                    </label>
                </div>

                <!-- File Size Limit -->
                <div>
                    <label for="max_file_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Maximum File Size (bytes)
                    </label>
                    <input type="number" 
                           id="max_file_size"
                           wire:model.blur="scanOptions.max_file_size" 
                           min="1048576" 
                           max="104857600" 
                           step="1048576"
                           placeholder="10485760"
                           class="block w-full md:w-48 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default: 10MB (10485760 bytes)</p>
                </div>

                <!-- File Extensions -->
                <div>
                    <label for="file_extensions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        File Extensions (comma-separated)
                    </label>
                    <input type="text" 
                           id="file_extensions"
                           wire:model.blur="fileExtensionsString"
                           placeholder="php,blade.php"
                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default: php</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between">
            <div>
                @if($errors->any())
                <div class="text-sm text-red-600 dark:text-red-400">
                    <div class="font-medium">Please fix the following errors:</div>
                    <ul class="mt-1 list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="{{ route('codesnoutr.dashboard') }}" 
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="startScan">
                        Start Scan
                        @if(!empty($ruleCategories))
                            <span class="text-xs opacity-75">({{ count($ruleCategories) }} categories)</span>
                        @endif
                    </span>
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

    <!-- File Browser Modal -->
    @if($showFileBrowser)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeFileBrowser"></div>
            
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                                {{ $scanType === 'file' ? 'Select File' : 'Select Directory' }}
                            </h3>
                            
                            <!-- Current Path -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Current path:</p>
                                <p class="text-sm font-mono bg-gray-100 dark:bg-gray-700 p-2 rounded break-all">{{ $currentBrowsePath }}</p>
                            </div>
                            
                            <!-- Navigation -->
                            @if($currentBrowsePath !== base_path())
                            <button wire:click="browseToParent" 
                                    class="mb-4 flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                .. (Parent Directory)
                            </button>
                            @endif
                            
                            <!-- File/Directory List -->
                            <div class="max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded">
                                @if(empty($browseItems))
                                    <p class="p-4 text-gray-500 dark:text-gray-400 text-center">No items found</p>
                                @else
                                    @foreach($browseItems as $item)
                                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-600 last:border-b-0">
                                        <div class="flex items-center space-x-2 flex-1">
                                            @if($item['type'] === 'directory')
                                                <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            @endif
                                            <span class="text-sm text-gray-900 dark:text-white truncate">{{ $item['name'] }}</span>
                                        </div>
                                        <div class="flex space-x-2">
                                            @if($item['type'] === 'directory')
                                                <button wire:click="browseToDirectory('{{ $item['path'] }}')" 
                                                        class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                                    Browse
                                                </button>
                                                @if($scanType === 'directory')
                                                <button wire:click="selectPath('{{ $item['path'] }}')" 
                                                        class="text-xs text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300">
                                                    Select
                                                </button>
                                                @endif
                                            @else
                                                @if($scanType === 'file' && in_array($item['extension'], ['php']))
                                                <button wire:click="selectPath('{{ $item['path'] }}')" 
                                                        class="text-xs text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300">
                                                    Select
                                                </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="closeFileBrowser" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Auto-refresh scan progress
    let progressInterval;
    
    document.addEventListener('livewire:initialized', function () {
        Livewire.on('scan-progress-updated', (progress) => {
            // Additional progress handling if needed
        });

        Livewire.on('queue-status-updated', (event) => {
            // Handle different queue statuses
            if (event.status === 'ready') {
                // Queue is ready, scan will start automatically
            }
        });

        Livewire.on('delayed-scan-start', () => {
            setTimeout(() => {
                const component = Livewire.find('{{ $this->getId() }}');
                if (component) {
                    component.call('proceedWithScan');
                }
            }, 2000); // 2 second delay to show queue ready status
        });

        Livewire.on('scan-completed', (event) => {
            clearInterval(progressInterval);
            
            // Extract scanId from event data
            let scanId = null;
            if (event && typeof event === 'object') {
                scanId = event.scanId || event.detail?.scanId || event[0]?.scanId;
            }
            
            setTimeout(() => {
                if (scanId) {
                    const redirectUrl = `/codesnoutr/results/${scanId}`;
                    window.location.href = redirectUrl;
                }

        Livewire.on('scan-cancelled', () => {
            clearInterval(progressInterval);
        });
    });

    // Start progress polling when scan begins
    @if($isScanning)
        progressInterval = setInterval(() => {
            const component = Livewire.find('{{ $this->getId() }}');
            if (component) {
                component.call('checkScanProgress');
            }
        }, 1000);
    @endif
</script>
@endpush
