@props([
    'results' => [],
])

@if(!empty($results))
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <!-- Results Header -->
    <div class="bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-800 dark:to-slate-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-600/30 rounded-lg flex items-center justify-center">
                    <x-codesnoutr::atoms.icon name="list" size="sm" class="text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Fix Results
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ count($results) }} {{ count($results) === 1 ? 'result' : 'results' }} found
                    </p>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="flex items-center space-x-4">
                @php
                    $successCount = collect($results)->where('status', 'success')->count();
                    $errorCount = collect($results)->where('status', '!=', 'success')->count();
                @endphp
                
                @if($successCount > 0)
                    <div class="flex items-center space-x-1 text-sm">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-green-700 dark:text-green-300 font-medium">{{ $successCount }}</span>
                    </div>
                @endif
                
                @if($errorCount > 0)
                    <div class="flex items-center space-x-1 text-sm">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <span class="text-red-700 dark:text-red-300 font-medium">{{ $errorCount }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Results Body -->
    <div class="max-h-96 overflow-y-auto">
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach(array_reverse($results) as $index => $result)
                @php
                    $isSuccess = ($result['status'] ?? 'error') === 'success';
                    $statusColor = $isSuccess ? 'green' : 'red';
                @endphp
                
                <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all duration-200 {{ $index === 0 ? 'bg-blue-50/30 dark:bg-blue-900/10' : '' }}">
                    <div class="flex items-start space-x-4">
                        <!-- Status Icon -->
                        <div class="flex-shrink-0 mt-0.5">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center {{ $isSuccess ? 'bg-green-100 dark:bg-green-600/20' : 'bg-red-100 dark:bg-red-600/20' }}">
                                <x-codesnoutr::atoms.icon 
                                    :name="$isSuccess ? 'check-circle' : 'x-circle'" 
                                    size="sm"
                                    class="{{ $isSuccess ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"
                                />
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <!-- File and Status -->
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center space-x-3 min-w-0 flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        {{ basename($result['file'] ?? 'Unknown file') }}
                                    </h4>
                                    <x-codesnoutr::atoms.badge 
                                        :variant="$isSuccess ? 'success' : 'danger'"
                                        size="sm"
                                    >
                                        {{ ucfirst($result['status'] ?? 'error') }}
                                    </x-codesnoutr::atoms.badge>
                                </div>
                                
                                <!-- Timestamp -->
                                <span class="text-xs text-gray-400 flex-shrink-0 ml-2">
                                    {{ isset($result['timestamp']) ? \Carbon\Carbon::parse($result['timestamp'])->format('H:i:s') : 'N/A' }}
                                </span>
                            </div>
                            
                            <!-- File Path -->
                            <div class="mb-2">
                                <p class="text-xs font-mono text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded px-2 py-1 truncate">
                                    {{ $result['file'] ?? 'Unknown file' }}
                                </p>
                            </div>
                            
                            <!-- Issue Details -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mb-3 text-xs">
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg px-2 py-1">
                                    <span class="text-gray-500 dark:text-gray-400 block">Line</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $result['line'] ?? 'N/A' }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg px-2 py-1">
                                    <span class="text-gray-500 dark:text-gray-400 block">Step</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $result['step'] ?? 'N/A' }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg px-2 py-1 col-span-2">
                                    <span class="text-gray-500 dark:text-gray-400 block">Rule ID</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $result['rule_id'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                            
                            <!-- Title and Message -->
                            @if($result['title'] ?? null)
                                <div class="mb-2">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $result['title'] }}
                                    </p>
                                </div>
                            @endif
                            
                            @if($result['message'] ?? null)
                                <div class="p-3 rounded-lg {{ $isSuccess ? 'bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800/30' : 'bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/30' }}">
                                    <p class="text-sm {{ $isSuccess ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
                                        {{ $result['message'] }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Results Footer -->
    @if(count($results) > 5)
        <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-3 border-t border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                Showing {{ count($results) }} results • Scroll to see all
            </p>
        </div>
    @endif
</div>
@endif