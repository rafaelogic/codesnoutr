<!-- File Browser Modal -->
<div class="fixed inset-0 z-50 overflow-y-auto" 
     x-data="{ show: false }" 
     x-init="$nextTick(() => show = true)" 
     x-show="show"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-cloak>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeFileBrowser"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
             x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Browse Files and Directories</h3>
                        @if($scanType === 'directory')
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Click on a directory to select it for scanning, or use the Browse button to navigate into it.</p>
                        @elseif($scanType === 'file')
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Click on a file to select it for scanning.</p>
                        @else
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Navigate through directories to find the files or folders you want to scan.</p>
                        @endif
                    </div>
                    <button type="button" wire:click="closeFileBrowser" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Breadcrumb -->
            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                <div class="flex items-center text-sm">
                    <button wire:click="navigateTo('{{ base_path() }}')" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                        Root
                    </button>
                    @php
                        $pathParts = explode('/', str_replace(base_path(), '', $browserCurrentPath));
                        $currentPath = base_path();
                    @endphp
                    @foreach($pathParts as $part)
                        @if(!empty($part))
                            @php $currentPath .= '/' . $part; @endphp
                            <span class="mx-2 text-gray-500">/</span>
                            <button wire:click="navigateTo('{{ $currentPath }}')" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                {{ $part }}
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- File list -->
            <div class="px-6 py-4 max-h-96 overflow-y-auto">
                <!-- Parent directory option -->
                @if($browserCurrentPath !== base_path())
                <div class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer" wire:click="navigateUp">
                    <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <span class="text-gray-600 dark:text-gray-300">.. (Parent Directory)</span>
                </div>
                @endif

                <!-- Directory and file items -->
                @forelse($this->browserItems as $item)
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded group">
                    <div class="flex items-center flex-1 cursor-pointer" 
                         @if($item['type'] === 'directory' && $scanType === 'directory') 
                             wire:click="selectPath('{{ $item['path'] }}')"
                         @elseif($item['type'] === 'directory') 
                             wire:click="navigateTo('{{ $item['path'] }}')"
                         @else 
                             wire:click="selectPath('{{ $item['path'] }}')"
                         @endif>
                        
                        @if($item['type'] === 'directory')
                        <svg class="h-5 w-5 mr-3 {{ $scanType === 'directory' ? 'text-indigo-500' : 'text-blue-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        @else
                        <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        @endif
                        
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['name'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                @if($item['type'] === 'directory')
                                    Directory{{ $scanType === 'directory' ? ' (Selectable)' : '' }}
                                @else
                                    {{ number_format($item['size'] / 1024, 1) }} KB{{ $scanType === 'file' ? ' (Selectable)' : '' }}
                                @endif
                                · Modified {{ date('M j, Y', $item['modified']) }}
                            </div>
                        </div>
                    </div>

                    @if($scanType === 'directory' && $item['type'] === 'directory')
                    <div class="flex space-x-2">
                        <button wire:click="selectPath('{{ $item['path'] }}')" 
                                class="ml-2 px-3 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                            Select
                        </button>
                        <button wire:click="navigateTo('{{ $item['path'] }}')" 
                                class="ml-2 px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                            Browse
                        </button>
                    </div>
                    @elseif($scanType === 'file' && $item['type'] === 'file')
                    <button wire:click="selectPath('{{ $item['path'] }}')" 
                            class="ml-2 px-3 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                        Select
                    </button>
                    @elseif($item['type'] === 'directory')
                    <button wire:click="navigateTo('{{ $item['path'] }}')" 
                            class="ml-2 px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                        Browse
                    </button>
                    @endif
                </div>
                @empty
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <p class="mt-2 text-sm">No accessible items in this directory</p>
                </div>
                @endforelse
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Current: {{ str_replace(base_path(), '', $browserCurrentPath) ?: '/' }}
                </div>
                <button wire:click="closeFileBrowser" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md hover:bg-gray-50 dark:hover:bg-gray-500">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
