@props([
    'header',
    'sortable' => true,
    'index' => 0
])

@php
    $headerKey = $header['key'] ?? $index;
    $headerLabel = $header['label'] ?? $header;
    $isSortable = $sortable && ($header['sortable'] ?? true);
@endphp

<th 
    scope="col" 
    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider {{ $isSortable ? 'cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-600' : '' }}"
    @if($isSortable) @click="sort('{{ $headerKey }}')" @endif
>
    <div class="flex items-center space-x-1">
        <x-atoms.text size="xs" weight="medium" color="muted" class="uppercase tracking-wider">
            {{ $headerLabel }}
        </x-atoms.text>
        
        @if($isSortable)
            <div class="flex flex-col">
                <x-atoms.icon 
                    name="chevron-up" 
                    size="xs" 
                    ::class="sortField === '{{ $headerKey }}' && sortDirection === 'asc' ? 'text-blue-600' : 'text-gray-400'"
                />
                <x-atoms.icon 
                    name="chevron-down" 
                    size="xs" 
                    ::class="sortField === '{{ $headerKey }}' && sortDirection === 'desc' ? 'text-blue-600' : 'text-gray-400'"
                />
            </div>
        @endif
    </div>
</th>