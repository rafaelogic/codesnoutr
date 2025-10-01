<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <x-atoms.text as="h2" size="2xl" weight="bold">Scan Results View</x-atoms.text>
            @if($scan)
                <x-atoms.text color="muted" class="mt-1">
                    Scan #{{ $scan->id }} - {{ $scan->created_at->format('M j, Y g:i A') }}
                </x-atoms.text>
            @endif
        </div>
        
        <div class="flex space-x-3">
            <x-atoms.button 
                wire:click="exportResults('json')"
                variant="primary"
                size="md"
                icon="document-arrow-down"
            >
                Export JSON
            </x-atoms.button>
        </div>
    </div>

    @if($issues && $issues->count() > 0)
        <x-molecules.card variant="elevated" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <x-atoms.text as="h3" size="lg" weight="semibold">
                    Issues Found ({{ $issues->count() }})
                </x-atoms.text>
            </div>

            <x-atoms.stack size="md">
                @foreach($issues as $issue)
                    <div class="border-l-4 border-gray-200 dark:border-gray-600 pl-4 py-2">
                        <div class="flex items-center justify-between">
                            <x-atoms.text as="h4" weight="medium">
                                {{ $issue->title }}
                            </x-atoms.text>
                            @php
                                $severityVariant = match($issue->severity) {
                                    'critical' => 'danger',
                                    'high' => 'warning',
                                    'medium' => 'warning',
                                    'low', 'info' => 'info',
                                    default => 'secondary'
                                };
                            @endphp
                            <x-atoms.badge :variant="$severityVariant" size="xs">
                                {{ ucfirst($issue->severity) }}
                            </x-atoms.badge>
                        </div>
                        <x-atoms.text size="sm" color="muted" class="mt-1">
                            {{ $issue->description }}
                        </x-atoms.text>
                        <div class="flex items-center space-x-4 mt-2">
                            <x-atoms.text size="xs" color="muted">{{ $issue->file_path }}</x-atoms.text>
                            <x-atoms.text size="xs" color="muted">Line {{ $issue->line_number }}</x-atoms.text>
                            <x-atoms.text size="xs" color="muted">{{ ucfirst($issue->category) }}</x-atoms.text>
                        </div>
                    </div>
                @endforeach
            </x-atoms.stack>
        </x-molecules.card>
    @else
        @include('codesnoutr::components.celebration-success', ['scan' => $scan])
    @endif
</div>