<div class="p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Scan Results</h2>
        @if($scan)
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                Scan #{{ $scan->id }} - {{ $scan->created_at->format('M j, Y g:i A') }}
            </p>
        @endif
    </div>

    @if($issues && $issues->count() > 0)
        <div class="space-y-4">
            @foreach($issues as $issue)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                {{ $issue->title }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                {{ $issue->description }}
                            </p>
                            <div class="flex items-center space-x-4 text-sm">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($issue->severity === 'critical') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300
                                    @elseif($issue->severity === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300
                                    @elseif($issue->severity === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300
                                    @else bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300
                                    @endif">
                                    {{ ucfirst($issue->severity) }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ ucfirst($issue->category) }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    Line {{ $issue->line_number }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($issues instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="mt-6">
                {{ $issues->links() }}
            </div>
        @endif
    @else
        @include('codesnoutr::components.celebration-success', ['scan' => $scan])
    @endif
</div>