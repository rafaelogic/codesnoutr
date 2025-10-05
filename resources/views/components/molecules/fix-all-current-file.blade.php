@props([
    'currentFile',
])

@if($currentFile)
<x-codesnoutr::molecules.card variant="info" class="mb-6">
    <x-slot name="body">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-600/30 rounded-lg flex items-center justify-center">
                    <x-codesnoutr::atoms.icon name="cog" class="w-5 h-5 text-blue-600 dark:text-blue-400 animate-spin" />
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">
                    Currently Processing
                </h4>
                <p class="text-sm text-blue-700 dark:text-blue-300 font-mono truncate">
                    {{ $currentFile['file'] ?? 'Unknown file' }}
                </p>
                <div class="flex items-center space-x-4 mt-2 text-xs text-blue-600 dark:text-blue-400">
                    <span>Line {{ $currentFile['line'] ?? 'N/A' }}</span>
                    <span>•</span>
                    <span>{{ $currentFile['rule_id'] ?? 'N/A' }}</span>
                    <span>•</span>
                    <span>ID: {{ $currentFile['id'] ?? 'N/A' }}</span>
                </div>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                    {{ $currentFile['title'] ?? 'No title' }}
                </p>
            </div>
        </div>
    </x-slot>
</x-codesnoutr::molecules.card>
@endif