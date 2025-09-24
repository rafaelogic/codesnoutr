@props([
    'headers' => [],
    'rows' => [],
    'sortable' => true,
    'searchable' => true,
    'paginated' => false,
    'selectable' => false,
    'striped' => true,
    'hoverable' => true,
    'compact' => false,
    'loading' => false,
    'emptyMessage' => 'No data available',
    'emptyIcon' => 'table-cells'
])

@php
    $tableClasses = [
        'min-w-full',
        'divide-y divide-gray-200 dark:divide-gray-700',
        $striped ? '' : '',
        $hoverable ? '' : '',
        $compact ? 'text-sm' : ''
    ];
    
    $containerClasses = [
        'overflow-hidden',
        'shadow',
        'ring-1 ring-black ring-opacity-5',
        'md:rounded-lg'
    ];
@endphp

<div {{ $attributes->merge(['class' => 'surface']) }}>
    @if($searchable)
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <x-atoms.input 
                    type="search" 
                    placeholder="Search..." 
                    class="max-w-sm"
                    x-model="search"
                />
                
                <div class="flex items-center space-x-2">
                    <x-atoms.button variant="ghost" size="sm" icon="funnel">
                        Filter
                    </x-atoms.button>
                    
                    <x-atoms.button variant="ghost" size="sm" icon="arrow-down-tray">
                        Export
                    </x-atoms.button>
                </div>
            </div>
        </div>
    @endif
    
    <div class="{{ implode(' ', $containerClasses) }}">
        @if($loading)
            <div class="p-8 text-center">
                <div class="inline-flex items-center space-x-2">
                    <x-atoms.icon name="arrow-path" class="w-5 h-5 animate-spin text-gray-400" />
                    <span class="text-gray-500">Loading...</span>
                </div>
            </div>
        @elseif(empty($rows))
            <div class="p-8 text-center">
                <x-atoms.icon :name="$emptyIcon" class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">{{ $emptyMessage }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Get started by adding some data.</p>
            </div>
        @else
            <table class="{{ implode(' ', $tableClasses) }}">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        @if($selectable)
                            <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                                <input 
                                    type="checkbox" 
                                    class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6"
                                    x-model="selectAll"
                                >
                            </th>
                        @endif
                        
                        @foreach($headers as $header)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @if(is_array($header))
                                    @if($sortable && isset($header['sortable']) && $header['sortable'])
                                        <button class="group inline-flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-100">
                                            <span>{{ $header['label'] }}</span>
                                            <x-atoms.icon name="chevron-up-down" size="xs" class="text-gray-400 group-hover:text-gray-500" />
                                        </button>
                                    @else
                                        {{ $header['label'] }}
                                    @endif
                                @else
                                    {{ $header }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($rows as $index => $row)
                        <tr class="{{ $hoverable ? 'hover:bg-gray-50 dark:hover:bg-gray-800' : '' }} {{ $striped && $index % 2 === 1 ? 'bg-gray-50 dark:bg-gray-800/50' : '' }} transition-colors duration-150">
                            @if($selectable)
                                <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                    @if(isset($row['selectable']) && $row['selectable'])
                                        <input 
                                            type="checkbox" 
                                            class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6"
                                            value="{{ $row['id'] ?? $index }}"
                                        >
                                    @endif
                                </td>
                            @endif
                            
                            @foreach($row['cells'] ?? [] as $cellIndex => $cell)
                                <td class="px-6 py-4 whitespace-nowrap {{ $compact ? 'py-2' : '' }}">
                                    @if(is_array($cell))
                                        @if($cell['type'] === 'badge')
                                            <x-atoms.badge 
                                                :variant="$cell['variant'] ?? 'secondary'"
                                                :size="$cell['size'] ?? 'sm'"
                                            >
                                                {{ $cell['value'] }}
                                            </x-atoms.badge>
                                        @elseif($cell['type'] === 'avatar')
                                            <x-atoms.avatar 
                                                :src="$cell['src'] ?? null"
                                                :alt="$cell['alt'] ?? ''"
                                                :initials="$cell['initials'] ?? null"
                                                size="sm"
                                            />
                                        @elseif($cell['type'] === 'actions')
                                            <div class="flex items-center space-x-2">
                                                @foreach($cell['actions'] as $action)
                                                    <x-atoms.button
                                                        :variant="$action['variant'] ?? 'ghost'"
                                                        size="xs"
                                                        :icon="$action['icon'] ?? null"
                                                        :href="$action['href'] ?? null"
                                                        onclick="{{ $action['onclick'] ?? '' }}"
                                                    >
                                                        {{ $action['label'] ?? '' }}
                                                    </x-atoms.button>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-900 dark:text-white">{{ $cell['value'] }}</div>
                                            @if(isset($cell['description']))
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $cell['description'] }}</div>
                                            @endif
                                        @endif
                                    @else
                                        <div class="text-sm text-gray-900 dark:text-white">{{ $cell }}</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    
    @if($paginated && !empty($rows))
        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>Showing</span>
                    <span class="font-medium">1</span>
                    <span>to</span>
                    <span class="font-medium">10</span>
                    <span>of</span>
                    <span class="font-medium">100</span>
                    <span>results</span>
                </div>
                
                <div class="flex items-center space-x-1">
                    <x-atoms.button variant="ghost" size="sm" icon="chevron-left" disabled>
                        Previous
                    </x-atoms.button>
                    <x-atoms.button variant="ghost" size="sm" icon="chevron-right">
                        Next
                    </x-atoms.button>
                </div>
            </div>
        </div>
    @endif
</div>