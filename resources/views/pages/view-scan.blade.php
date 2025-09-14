<x-templates.app-layout title="Scan Results - {{ $scan->id ?? 'Unknown' }}">
    @if(isset($scan) && $scan)
        @livewire('codesnoutr-scan-results-view', ['scanId' => $scan->id], key('scan-view-' . $scan->id))
    @else
        <div class="max-w-7xl mx-auto p-6">
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 text-center">
                <h2 class="text-xl font-semibold text-red-800 dark:text-red-200 mb-2">
                    Scan Not Found
                </h2>
                <p class="text-red-600 dark:text-red-400 mb-4">
                    The requested scan could not be found or you don't have permission to view it.
                </p>
                <a href="{{ route('codesnoutr.dashboard') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Return to Dashboard
                </a>
            </div>
        </div>
    @endif
</x-templates.app-layout>
