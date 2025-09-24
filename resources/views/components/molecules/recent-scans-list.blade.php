@props([
    'scans' => [],
    'title' => 'Recent Scans'
])

<x-atoms.surface>
    <x-molecules.card-header :title="$title" />
    
    <x-molecules.card-body padding="none">
        @if(empty($scans))
            <x-molecules.empty-state 
                icon="search"
                title="No scans yet"
                description="Run your first scan to see results here"
                size="sm"
            />
        @else
            <x-atoms.stack spacing="xs">
                @foreach($scans as $scan)
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <x-atoms.badge 
                                    :variant="$scan['status'] === 'completed' ? 'success' : ($scan['status'] === 'failed' ? 'danger' : 'warning')"
                                >
                                    {{ ucfirst($scan['status']) }}
                                </x-atoms.badge>
                                
                                <div>
                                    <x-atoms.text weight="medium">
                                        {{ $scan['target'] }}
                                    </x-atoms.text>
                                    
                                    <x-atoms.text size="sm" color="muted">
                                        {{ $scan['created_at'] }}
                                    </x-atoms.text>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                @if($scan['issues_count'] > 0)
                                    <div class="flex items-center space-x-1">
                                        <x-atoms.icon name="exclamation-triangle" size="sm" class="text-yellow-500" />
                                        <x-atoms.text size="sm" weight="medium">
                                            {{ $scan['issues_count'] }} issues
                                        </x-atoms.text>
                                    </div>
                                @endif
                                
                                <x-atoms.button 
                                    variant="ghost" 
                                    size="sm"
                                    :href="route('codesnoutr.scan.show', $scan['id'])"
                                >
                                    View
                                </x-atoms.button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </x-atoms.stack>
        @endif
    </x-molecules.card-body>
</x-atoms.surface>