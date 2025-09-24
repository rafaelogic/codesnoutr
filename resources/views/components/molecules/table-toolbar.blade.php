@props([
    'searchable' => false,
    'placeholder' => 'Search...',
    'actions' => []
])

@if($searchable || !empty($actions))
    <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
            @if($searchable)
                <div class="flex-1 max-w-lg">
                    <x-molecules.search-box 
                        :placeholder="$placeholder"
                        x-model="searchTerm"
                        @input="filterData()"
                    />
                </div>
            @endif
            
            @if(!empty($actions))
                <div class="flex space-x-3">
                    @foreach($actions as $action)
                        <x-atoms.button
                            :variant="$action['variant'] ?? 'primary'"
                            :icon="$action['icon'] ?? null"
                            :href="$action['href'] ?? null"
                            @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                        >
                            {{ $action['label'] }}
                        </x-atoms.button>
                    @endforeach
                </div>
            @endif
            
            {{ $slot }}
        </div>
    </div>
@endif