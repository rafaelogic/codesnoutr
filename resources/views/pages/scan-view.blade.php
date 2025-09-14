<x-templates.app-layout title="Scan Results - {{ $scan->path }}">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Scan Results
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        {{ $scan->path }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Scanned: {{ $scan->created_at->format('M j, Y g:i A') }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Status: <span class="font-medium {{ $scan->status === 'completed' ? 'text-green-600' : 'text-yellow-600' }}">
                            {{ ucfirst($scan->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-2xl font-bold text-red-600">{{ $scan->issues->where('severity', 'critical')->count() }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Critical</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-2xl font-bold text-orange-600">{{ $scan->issues->where('severity', 'high')->count() }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">High</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-2xl font-bold text-yellow-600">{{ $scan->issues->where('severity', 'medium')->count() }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Medium</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-2xl font-bold text-blue-600">{{ $scan->issues->where('severity', 'low')->count() }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Low</div>
            </div>
        </div>

        <!-- Simple Livewire Component -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Issues Overview
                </h2>
            </div>
            
            <!-- Livewire Component with minimal complexity -->
            @livewire('codesnoutr-simple-scan-results', ['scanId' => $scan->id], key('simple-scan-results-' . $scan->id))
        </div>
    </div>
</x-templates.app-layout>
