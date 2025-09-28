<x-templates.app-layout title="Scan Results" subtitle="Browse and manage your scan history">
    <x-slot name="actions">
        <a href="{{ route('codesnoutr.scan') }}" 
           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            New Scan
        </a>
    </x-slot>

    <div class="space-y-6 min-h-[calc(100vh-180px)] h-full">
        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6 transition-colors duration-300">
            <form method="GET" action="{{ route('codesnoutr.results') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status" class="mt-1 p-2 block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>Running</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <select name="type" id="type" class="mt-1 p-2 block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Types</option>
                        <option value="file" {{ request('type') == 'file' ? 'selected' : '' }}>File</option>
                        <option value="directory" {{ request('type') == 'directory' ? 'selected' : '' }}>Directory</option>
                        <option value="codebase" {{ request('type') == 'codebase' ? 'selected' : '' }}>Codebase</option>
                    </select>
                </div>
                
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                           class="mt-1 p-2 block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Scans List -->
        @if($scans->count() > 0)
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden transition-colors duration-300">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Scan Details
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Issues
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Duration
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach($scans as $scan)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    @php
                                                        $scanTitle = 'Source code';
                                                        if ($scan->type === 'directory' && $scan->target) {
                                                            $scanTitle = basename(rtrim($scan->target, '/'));
                                                        } elseif ($scan->type === 'file' && $scan->target) {
                                                            $scanTitle = basename($scan->target);
                                                        }
                                                    @endphp
                                                    {{ $scanTitle }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    @if($scan->type === 'file' && $scan->target)
                                                        File: {{ $scan->target }}
                                                    @else
                                                        {{ ucfirst($scan->type) }} - {{ Str::limit($scan->target ?? 'Full codebase', 40) }}
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                                    {{ $scan->created_at->format('M d, Y H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                                                'running' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300', 
                                                'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                                'failed' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300'
                                            ];
                                            $statusColor = $statusColors[$scan->status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            {{ ucfirst($scan->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            @if($scan->issues_found > 0)
                                                <span class="font-medium">{{ number_format($scan->issues_found) }}</span> found
                                                @if($scan->issues->where('fixed', true)->count() > 0)
                                                    <br><span class="text-green-600 dark:text-green-400">{{ $scan->issues->where('fixed', true)->count() }} resolved</span>
                                                @endif
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">No issues</span>
                                            @endif
                                        </div>
                                        @if($scan->files_scanned > 0)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ number_format($scan->files_scanned) }} files scanned
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        @if($scan->scan_duration)
                                            {{ $scan->scan_duration }}s
                                        @elseif($scan->started_at && $scan->completed_at)
                                            {{ $scan->started_at->diffInSeconds($scan->completed_at) }}s
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($scan->status === 'completed' && $scan->issues_found > 0)
                                            <a href="{{ route('codesnoutr.results.scan', $scan->id) }}" 
                                               class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">
                                                View Results
                                            </a>
                                        @endif
                                        <a href="{{ route('codesnoutr.scan.show', $scan->id) }}" 
                                           class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                            Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $scans->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No scans found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if(request()->hasAny(['status', 'type', 'date_from', 'date_to']))
                        No scans match your current filters.
                    @else
                        Get started by running your first code scan.
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['status', 'type', 'date_from', 'date_to']))
                        <a href="{{ route('codesnoutr.results') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            Clear Filters
                        </a>
                    @else
                        <a href="{{ route('codesnoutr.scan') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Run Your First Scan
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-templates.app-layout>
