@props([
    'results' => [],
    'fixedCount' => null,
    'skippedCount' => null,
    'failedCount' => null,
])

@php
    $resultsCollection = collect($results ?? []);
    $resultCount = $resultsCollection->count();

    $computedFixed = $resultsCollection->where('status', 'success')->count();
    $computedSkipped = $resultsCollection->where('status', 'skipped')->count();
    $computedFailed = $resultsCollection->filter(function ($item) {
        $status = data_get($item, 'status');

        return $status !== 'success' && $status !== 'skipped';
    })->count();

    $fixedCount = $fixedCount ?? $computedFixed;
    $skippedCount = $skippedCount ?? $computedSkipped;
    $failedCount = $failedCount ?? $computedFailed;
@endphp

@if($resultCount === 0)
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center">
        <div class="mx-auto mb-4 w-14 h-14 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center">
            <x-codesnoutr::atoms.icon name="inbox" size="lg" class="text-blue-500 dark:text-blue-300" />
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No results yet</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Start a Fix All session to see detailed progress and outcome information here.
        </p>
    </div>

    @php return; @endphp
@endif

<div
    class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden"
    x-data="{
        activeTab: 'all',
        filterResult(status) {
            if (this.activeTab === 'all') return true;
            if (this.activeTab === 'fixed') return status === 'success';
            if (this.activeTab === 'skipped') return status === 'skipped';

            return status !== 'success' && status !== 'skipped';
        }
    }"
>
    <div class="bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-900 dark:to-slate-900 border-b border-gray-200 dark:border-gray-800">
        <div class="px-6 py-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start space-x-3">
                    <div class="w-9 h-9 bg-blue-100 dark:bg-blue-600/30 rounded-lg flex items-center justify-center">
                        <x-codesnoutr::atoms.icon name="list" size="sm" class="text-blue-600 dark:text-blue-400" />
                    </div>

                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Fix Results</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $resultCount }} {{ $resultCount === 1 ? 'result' : 'results' }} found
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <div class="flex items-center space-x-1">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-green-500"></span>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $fixedCount }} fixed</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $skippedCount }} skipped</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $failedCount }} failed</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex space-x-1 bg-gray-100 dark:bg-gray-900/80 rounded-lg p-1 border border-transparent dark:border-gray-800">
                    <button
                        type="button"
                        x-on:click="activeTab = 'all'"
                        x-bind:class="activeTab === 'all' ? 'bg-white dark:bg-gray-800/80 shadow-sm' : 'hover:bg-gray-200 dark:hover:bg-gray-800/60'"
                        class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200"
                    >
                        <span x-bind:class="activeTab === 'all' ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300'">
                            All ({{ $resultCount }})
                        </span>
                    </button>

                    <button
                        type="button"
                        x-on:click="activeTab = 'fixed'"
                        x-bind:class="activeTab === 'fixed' ? 'bg-white dark:bg-gray-800/80 shadow-sm' : 'hover:bg-gray-200 dark:hover:bg-gray-800/60'"
                        class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200"
                    >
                        <span x-bind:class="activeTab === 'fixed' ? 'text-green-600 dark:text-green-300' : 'text-gray-600 dark:text-gray-300'">
                            ✓ Fixed ({{ $fixedCount }})
                        </span>
                    </button>

                    <button
                        type="button"
                        x-on:click="activeTab = 'skipped'"
                        x-bind:class="activeTab === 'skipped' ? 'bg-white dark:bg-gray-800/80 shadow-sm' : 'hover:bg-gray-200 dark:hover:bg-gray-800/60'"
                        class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200"
                    >
                        <span x-bind:class="activeTab === 'skipped' ? 'text-yellow-600 dark:text-yellow-300' : 'text-gray-600 dark:text-gray-300'">
                            ⊘ Skipped ({{ $skippedCount }})
                        </span>
                    </button>

                    <button
                        type="button"
                        x-on:click="activeTab = 'failed'"
                        x-bind:class="activeTab === 'failed' ? 'bg-white dark:bg-gray-800/80 shadow-sm' : 'hover:bg-gray-200 dark:hover:bg-gray-800/60'"
                        class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200"
                    >
                        <span x-bind:class="activeTab === 'failed' ? 'text-red-600 dark:text-red-300' : 'text-gray-600 dark:text-gray-300'">
                            ✗ Failed ({{ $failedCount }})
                        </span>
                    </button>
                </div>

                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="px-2 py-1 bg-white dark:bg-gray-800 rounded-full border border-gray-200 dark:border-gray-700">
                        Latest results shown first
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-h-96 overflow-y-auto">
    <div class="divide-y divide-gray-200 dark:divide-gray-800">
            @foreach($resultsCollection->reverse()->values() as $index => $result)
                @php
                    $status = data_get($result, 'status', 'error');
                    $isSuccess = $status === 'success';
                    $isSkipped = $status === 'skipped';
                    $isFailed = ! ($isSuccess || $isSkipped);

                    $statusIcon = match ($status) {
                        'success' => 'check-circle',
                        'skipped' => 'minus-circle',
                        default => 'x-circle',
                    };

                    $statusEscaped = e($status);
                    $issueId = data_get($result, 'issue_id');
                    $fixAttempts = data_get($result, 'fix_attempts', []);

                    if (empty($fixAttempts) && $issueId) {
                        try {
                            $issue = \Rafaelogic\CodeSnoutr\Models\Issue::find($issueId);
                            if ($issue) {
                                $fixAttempts = $issue->fix_attempts ?? [];
                            }
                        } catch (\Throwable $e) {
                            // Ignore database errors while rendering the view.
                        }
                    }
                @endphp

                <div
                    data-status="{{ $statusEscaped }}"
                    x-show="filterResult('{{ $statusEscaped }}')"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    class="p-5 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800/40 {{ $index === 0 ? 'bg-blue-50/30 dark:bg-blue-900/20' : '' }}"
                    x-data="{ showHistory: false }"
                >
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 mt-0.5">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center
                                {{ $isSuccess ? 'bg-green-100 dark:bg-green-900/30' : '' }}
                                {{ $isSkipped ? 'bg-yellow-100 dark:bg-yellow-900/30' : '' }}
                                {{ $isFailed ? 'bg-red-100 dark:bg-red-900/30' : '' }}
                            ">
                                <x-codesnoutr::atoms.icon
                                    :name="$statusIcon"
                                    size="sm"
                                    class="{{ $isSuccess ? 'text-green-600 dark:text-green-300' : '' }} {{ $isSkipped ? 'text-yellow-600 dark:text-yellow-300' : '' }} {{ $isFailed ? 'text-red-600 dark:text-red-300' : '' }}"
                                />
                            </div>
                        </div>

                        <div class="flex-1 min-w-0 space-y-3">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        {{ basename(data_get($result, 'file', 'Unknown file')) }}
                                    </h4>

                                    <x-codesnoutr::atoms.badge :variant="$isSuccess ? 'success' : ($isSkipped ? 'warning' : 'danger')" size="sm">
                                        {{ ucfirst($status) }}
                                    </x-codesnoutr::atoms.badge>
                                </div>

                                <span class="text-xs text-gray-400 flex-shrink-0">
                                    {{ data_get($result, 'timestamp') ? \Carbon\Carbon::parse(data_get($result, 'timestamp'))->format('H:i:s') : 'N/A' }}
                                </span>
                            </div>

                            <div class="text-xs grid grid-cols-2 gap-2 lg:grid-cols-4">
                                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg px-2 py-1">
                                    <span class="text-gray-500 dark:text-gray-400 block">Line</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ data_get($result, 'line', 'N/A') }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg px-2 py-1">
                                    <span class="text-gray-500 dark:text-gray-400 block">Step</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ data_get($result, 'step', 'N/A') }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg px-2 py-1 col-span-2">
                                    <span class="text-gray-500 dark:text-gray-400 block">Rule ID</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ data_get($result, 'rule_id', 'N/A') }}</span>
                                </div>
                            </div>

                            @if(data_get($result, 'title'))
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ data_get($result, 'title') }}
                                    </p>
                                </div>
                            @endif

                            @if(data_get($result, 'message'))
                                <div class="p-3 rounded-lg border
                                    {{ $isSuccess ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800/40' : '' }}
                                    {{ $isSkipped ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800/40' : '' }}
                                    {{ $isFailed ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40' : '' }}
                                ">
                                    <p class="text-sm
                                        {{ $isSuccess ? 'text-green-800 dark:text-green-200' : '' }}
                                        {{ $isSkipped ? 'text-yellow-800 dark:text-yellow-200' : '' }}
                                        {{ $isFailed ? 'text-red-800 dark:text-red-200' : '' }}
                                    ">
                                        {{ data_get($result, 'message') }}
                                    </p>
                                </div>
                            @endif

                            @if(!empty($fixAttempts))
                                <div class="border-t border-gray-200 dark:border-gray-800 pt-3">
                                    <button
                                        type="button"
                                        class="flex items-center space-x-2 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
                                        x-on:click="showHistory = !showHistory"
                                    >
                                        <svg x-show="!showHistory" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                        <svg x-show="showHistory" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        <span>Fix Attempt History ({{ count($fixAttempts) }})</span>
                                    </button>

                                    <div
                                        x-show="showHistory"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        class="mt-3 space-y-2"
                                    >
                                        @foreach(collect($fixAttempts)->reverse()->values() as $attemptIndex => $attempt)
                                            @php
                                                $attemptStatus = data_get($attempt, 'status', 'unknown');
                                                $attemptIsSuccess = $attemptStatus === 'success';
                                                $attemptIsSkipped = $attemptStatus === 'skipped';
                                                $attemptIsFailed = ! ($attemptIsSuccess || $attemptIsSkipped);
                                            @endphp

                                            <div class="flex items-start space-x-3 text-xs p-3 rounded-lg border
                                                {{ $attemptIsSuccess ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800/40' : '' }}
                                                {{ $attemptIsSkipped ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800/40' : '' }}
                                                {{ $attemptIsFailed ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40' : '' }}
                                            ">
                                                <div class="flex-shrink-0 mt-0.5">
                                                    <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold
                                                        {{ $attemptIsSuccess ? 'bg-green-200 dark:bg-green-800 text-green-700 dark:text-green-100' : '' }}
                                                        {{ $attemptIsSkipped ? 'bg-yellow-200 dark:bg-yellow-800 text-yellow-700 dark:text-yellow-100' : '' }}
                                                        {{ $attemptIsFailed ? 'bg-red-200 dark:bg-red-800 text-red-700 dark:text-red-100' : '' }}
                                                    ">
                                                        {{ count($fixAttempts) - $attemptIndex }}
                                                    </div>
                                                </div>

                                                <div class="flex-1 min-w-0 space-y-1">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-semibold
                                                            {{ $attemptIsSuccess ? 'text-green-700 dark:text-green-300' : '' }}
                                                            {{ $attemptIsSkipped ? 'text-yellow-700 dark:text-yellow-300' : '' }}
                                                            {{ $attemptIsFailed ? 'text-red-700 dark:text-red-300' : '' }}
                                                        ">
                                                            {{ $attemptIsSuccess ? '✓ Success' : ($attemptIsSkipped ? '⊘ Skipped' : '✗ Failed') }}
                                                        </span>
                                                        <span class="text-gray-500 dark:text-gray-300">
                                                            {{ data_get($attempt, 'timestamp') ? \Carbon\Carbon::parse(data_get($attempt, 'timestamp'))->format('M d, H:i:s') : 'N/A' }}
                                                        </span>
                                                    </div>

                                                    @if(data_get($attempt, 'error'))
                                                        <p class="text-gray-700 dark:text-gray-300">
                                                            <strong>Error:</strong> {{ data_get($attempt, 'error') }}
                                                        </p>
                                                    @endif

                                                    @if(data_get($attempt, 'data.reason'))
                                                        <p class="text-gray-700 dark:text-gray-300">
                                                            <strong>Reason:</strong> {{ data_get($attempt, 'data.reason') }}
                                                        </p>
                                                    @endif

                                                    @if(!is_null(data_get($attempt, 'data.confidence')))
                                                        <p class="text-gray-600 dark:text-gray-400">
                                                            <strong>Confidence:</strong> {{ data_get($attempt, 'data.confidence') }}%
                                                        </p>
                                                    @endif

                                                    @if(data_get($attempt, 'data.exception'))
                                                        <p class="text-gray-600 dark:text-gray-400">
                                                            <strong>Exception:</strong> {{ data_get($attempt, 'data.exception') }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if($resultCount > 5)
        <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-3 border-t border-gray-200 dark:border-gray-800 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Showing {{ $resultCount }} results • Use tabs to filter • Scroll to see all
            </p>
        </div>
    @endif
</div>
