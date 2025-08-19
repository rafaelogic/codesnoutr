<!-- Step 4: Review and Start -->
<div class="space-y-6">
    <div class="text-center mb-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Review Configuration</h3>
        <p class="text-gray-600 dark:text-gray-400">Verify your scan settings before starting the analysis</p>
    </div>

    <!-- Configuration Summary -->
    <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden">
        <!-- Scan Type -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    @if($scanType === 'quick')
                    <div class="h-10 w-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    @elseif($scanType === 'deep')
                    <div class="h-10 w-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    @else
                    <div class="h-10 w-10 bg-gray-100 dark:bg-gray-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        </svg>
                    </div>
                    @endif
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">{{ ucfirst($scanType) }} Scan</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            @if($scanType === 'quick')
                                Fast scan focusing on critical issues
                            @elseif($scanType === 'deep')
                                Comprehensive analysis of all code patterns
                            @else
                                Balanced scan with custom rule selection
                            @endif
                        </p>
                    </div>
                </div>
                <button type="button" 
                        wire:click="goToStep(1)"
                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm font-medium">
                    Edit
                </button>
            </div>
        </div>

        <!-- Target Selection -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    @if($scanTarget === 'directory')
                    <div class="h-10 w-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>
                    @elseif($scanTarget === 'single_file')
                    <div class="h-10 w-10 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    @else
                    <div class="h-10 w-10 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    @endif
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">
                            @if($scanTarget === 'directory')
                                Directory Scan
                            @elseif($scanTarget === 'single_file')
                                Single File
                            @else
                                Full Codebase
                            @endif
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 max-w-md truncate">
                            {{ $scanPath ?: 'Current directory' }}
                        </p>
                    </div>
                </div>
                <button type="button" 
                        wire:click="goToStep(2)"
                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm font-medium">
                    Edit
                </button>
            </div>
        </div>

        <!-- Rule Categories -->
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="h-10 w-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">Rule Categories</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ count($ruleCategories) }} categories selected</p>
                    </div>
                </div>
                <button type="button" 
                        wire:click="goToStep(3)"
                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm font-medium">
                    Edit
                </button>
            </div>
            
            @if(count($ruleCategories) > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($ruleCategories as $category)
                @php
                    $categoryInfo = $allCategories[$category] ?? ['title' => ucfirst($category), 'color' => 'gray'];
                @endphp
                <span class="inline-flex items-center px-3 py-1 text-xs font-medium bg-{{ $categoryInfo['color'] }}-100 dark:bg-{{ $categoryInfo['color'] }}-900 text-{{ $categoryInfo['color'] }}-800 dark:text-{{ $categoryInfo['color'] }}-200 rounded-full">
                    {{ $categoryInfo['title'] }}
                </span>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400 italic">No categories selected</p>
            @endif
        </div>
    </div>

    <!-- Estimated Time -->
    <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">Estimated Scan Time</h4>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    @if($scanType === 'quick')
                        1-3 minutes
                    @elseif($scanType === 'deep')
                        5-15 minutes
                    @else
                        2-8 minutes
                    @endif
                    (depending on project size)
                </p>
            </div>
        </div>
    </div>

    <!-- Start Scan Button -->
    <div class="text-center pt-4">
        <button type="button" 
                wire:click="startScan"
                wire:loading.attr="disabled"
                class="inline-flex items-center px-8 py-3 text-base font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-lg">
            
            <div wire:loading.remove wire:target="startScan" class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m-6-8h1m4 0h1M9 6h1m4 0h1"/>
                </svg>
                Start Code Analysis
            </div>
            
            <div wire:loading wire:target="startScan" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Starting Scan...
            </div>
        </button>
    </div>

    <!-- Validation Errors -->
    @if($errors->any())
    <div class="mt-6 p-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-800 rounded-lg">
        <div class="flex">
            <svg class="h-5 w-5 text-red-400 dark:text-red-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-red-800 dark:text-red-300">Please fix the following issues:</h4>
                <ul class="mt-2 text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif
</div>
