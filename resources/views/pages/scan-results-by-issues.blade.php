<x-templates.app-layout title="Scan Results - Grouped by Issues">
    @if(isset($scan) && $scan)
        @livewire('codesnoutr-scan-results-by-issues', ['scanId' => $scan->id], key('scan-results-by-issues-' . $scan->id))
    @else
        <div class="p-6 text-center">
            <p class="text-red-600">Error: No scan data available</p>
            <a href="{{ route('codesnoutr.results') }}" class="text-blue-600 hover:underline">Return to Results</a>
        </div>
    @endif
</x-templates.app-layout>