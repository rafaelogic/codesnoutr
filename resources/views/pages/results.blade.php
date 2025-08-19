@extends('codesnoutr::layouts.app')

@section('title', 'Scan Results - CodeSnoutr')

@section('content')
    <div class="p-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">All Scan Results</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Browse and manage your scan history</p>
                </div>
                <div class="mt-4 lg:mt-0">
                    <a href="{{ route('codesnoutr.scan') }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        New Scan
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8 transition-theme">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status" id="status" class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>Running</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                    <select name="type" id="type" class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Types</option>
                        <option value="file" {{ request('type') === 'file' ? 'selected' : '' }}>Single File</option>
                        <option value="directory" {{ request('type') === 'directory' ? 'selected' : '' }}>Directory</option>
                        <option value="codebase" {{ request('type') === 'codebase' ? 'selected' : '' }}>Full Codebase</option>
                    </select>
                </div>
                
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-gray-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Scans List -->
        @if($scans->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-theme">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Scan History</h3>
            </div>
            
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($scans as $scan)
                <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Status Icon -->
                            <div class="flex-shrink-0">
                                @if($scan->status === 'completed')
                                    <div class="h-10 w-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @elseif($scan->status === 'running')
                                    <div class="h-10 w-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                @elseif($scan->status === 'failed')
                                    <div class="h-10 w-10 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="h-10 w-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                        <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Scan Info -->
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ ucfirst($scan->type) }} Scan
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($scan->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($scan->status === 'running') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @elseif($scan->status === 'failed') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                        {{ ucfirst($scan->status) }}
                                    </span>
                                </div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    <div>{{ $scan->target ?: 'Full codebase' }}</div>
                                    <div class="flex items-center space-x-4 mt-1">
                                        <span>{{ $scan->created_at->format('M j, Y g:i A') }}</span>
                                        @if($scan->files_scanned)
                                            <span>{{ number_format($scan->files_scanned) }} files</span>
                                        @endif
                                        @if($scan->issues_found)
                                            <span>{{ number_format($scan->issues_found) }} issues</span>
                                        @endif
                                        @if($scan->scan_duration)
                                            <span>{{ number_format($scan->scan_duration, 1) }}s duration</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-3">
                            @if($scan->status === 'completed')
                                <a href="{{ route('codesnoutr.results.scan', $scan) }}" 
                                   class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    View Results
                                </a>
                                
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" 
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Export
                                        <svg class="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                    
                                    <div x-show="open" @click.away="open = false" 
                                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-600 z-10">
                                        <div class="py-1">
                                            <a href="{{ route('codesnoutr.export', ['scan' => $scan, 'format' => 'json']) }}" 
                                               class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                Export as JSON
                                            </a>
                                            <a href="{{ route('codesnoutr.export', ['scan' => $scan, 'format' => 'csv']) }}" 
                                               class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                Export as CSV
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @elseif($scan->status === 'running')
                                <span class="inline-flex items-center px-3 py-2 text-sm text-blue-600 dark:text-blue-400">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Scanning...
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $scans->appends(request()->query())->links() }}
        </div>
        @else
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center transition-theme">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No scans found</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">
                @if(request()->hasAny(['status', 'type', 'date_from', 'date_to']))
                    No scans match your current filters. Try adjusting your search criteria.
                @else
                    You haven't run any scans yet. Start your first scan to see results here.
                @endif
            </p>
            <div class="mt-6">
                @if(request()->hasAny(['status', 'type', 'date_from', 'date_to']))
                    <a href="{{ route('codesnoutr.results') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                        Clear Filters
                    </a>
                @endif
                <a href="{{ route('codesnoutr.scan') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Start First Scan
                </a>
            </div>
        </div>
        @endif
    </div>
@endsection
