<x-templates.app-layout title="Scan Details - #{{ $scan->id }}">
    <div class="px-4 min-h-screen">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4 mb-4">
                <a href="{{ route('codesnoutr.results') }}" 
                   class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Results
                </a>
                <span class="text-gray-300 dark:text-gray-600">/</span>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Scan #{{ $scan->id }}</h1>
            </div>
            
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ ucfirst($scan->type) }} scan of {{ $scan->target ?: 'full codebase' }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Created {{ $scan->created_at->format('M j, Y \a\t g:i A') }}
                    </p>
                </div>
                
                @if($scan->status === 'completed' && $scan->issues->count() > 0)
                <div class="flex space-x-3">
                    <a href="{{ route('codesnoutr.results.scan', $scan->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        View Detailed Results
                    </a>
                </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Scan Status -->
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Scan Status</h2>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                                    'running' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300', 
                                    'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                    'failed' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300'
                                ];
                                $statusColor = $statusColors[$scan->status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                                {{ ucfirst($scan->status) }}
                            </span>
                        </div>
                        
                        @if($scan->scan_duration)
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Duration: {{ $scan->scan_duration }}s
                        </div>
                        @endif
                    </div>
                    
                    @if($scan->started_at)
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Started</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $scan->started_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        @if($scan->completed_at)
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Completed</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $scan->completed_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Issues Summary -->
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Issues Summary</h2>
                    
                    @if($scan->issues->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @php
                            $issuesBySeverity = $scan->issues->groupBy('severity');
                            $severityColors = [
                                'critical' => 'text-red-600 dark:text-red-400',
                                'high' => 'text-orange-600 dark:text-orange-400',
                                'medium' => 'text-yellow-600 dark:text-yellow-400',
                                'low' => 'text-blue-600 dark:text-blue-400',
                                'info' => 'text-gray-600 dark:text-gray-400'
                            ];
                        @endphp
                        
                        @foreach(['critical', 'high', 'medium', 'low', 'info'] as $severity)
                        @php $count = $issuesBySeverity->get($severity, collect())->count(); @endphp
                        <div class="text-center">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize">{{ $severity }}</dt>
                            <dd class="text-2xl font-semibold {{ $severityColors[$severity] ?? 'text-gray-600 dark:text-gray-400' }}">
                                {{ $count }}
                            </dd>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Total Issues</dt>
                                <dd class="text-xl font-semibold text-gray-900 dark:text-white">{{ $scan->issues->count() }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Resolved</dt>
                                <dd class="text-xl font-semibold text-green-600 dark:text-green-400">{{ $scan->issues->where('fixed', true)->count() }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Pending</dt>
                                <dd class="text-xl font-semibold text-red-600 dark:text-red-400">{{ $scan->issues->where('fixed', false)->count() }}</dd>
                            </div>
                        </div>
                    </div>
                    @else
                    @include('codesnoutr::components.celebration-success', ['scan' => $scan])
                    @endif
                </div>

                <!-- Recent Issues -->
                @if($scan->issues->count() > 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Recent Issues</h2>
                        @if($scan->issues->count() > 5)
                        <a href="{{ route('codesnoutr.results.scan', $scan->id) }}" 
                           class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                            View all {{ $scan->issues->count() }} issues →
                        </a>
                        @endif
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($scan->issues->take(5) as $issue)
                        <div class="flex items-start space-x-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex-shrink-0">
                                @php $severityColor = $severityColors[$issue->severity] ?? 'text-gray-600 dark:text-gray-400'; @endphp
                                <svg class="h-5 w-5 {{ $severityColor }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $issue->title }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColor }}">
                                        {{ $issue->severity }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ basename($issue->file_path) }}:{{ $issue->line_number }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Scan Details -->
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Scan Details</h3>
                    
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                            <dd class="text-sm text-gray-900 dark:text-white capitalize">{{ $scan->type }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Target</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $scan->target ?: 'Full codebase' }}</dd>
                        </div>
                        
                        @if($scan->files_scanned)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Files Scanned</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ number_format($scan->files_scanned) }}</dd>
                        </div>
                        @endif
                        
                        @if($scan->issues_found)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Issues Found</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ number_format($scan->issues_found) }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Actions</h3>
                    
                    <div class="space-y-3">
                        @if($scan->status === 'completed' && $scan->issues->count() > 0)
                        <a href="{{ route('codesnoutr.results.scan', $scan->id) }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            View Results
                        </a>
                        @endif
                        
                        <a href="{{ route('codesnoutr.export', ['scan' => $scan->id, 'format' => 'json']) }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Export JSON
                        </a>
                        
                        <a href="{{ route('codesnoutr.export', ['scan' => $scan->id, 'format' => 'csv']) }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Export CSV
                        </a>
                        
                        <button onclick="if(confirm('Are you sure you want to run a new scan?')) { window.location.href = '{{ route('codesnoutr.scan') }}'; }" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Run New Scan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-templates.app-layout>