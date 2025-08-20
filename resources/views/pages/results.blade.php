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

    @livewire('codesnoutr-scan-results')
</x-templates.app-layout>
