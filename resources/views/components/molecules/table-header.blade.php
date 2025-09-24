@props([
    'headers' => [],
    'sortable' => true,
    'selectable' => false,
    'stickyHeader' => false
])

<thead class="bg-gray-50 dark:bg-gray-700 {{ $stickyHeader ? 'sticky top-0 z-10' : '' }}">
    <tr>
        @if($selectable)
            <th scope="col" class="relative px-6 py-3">
                <input 
                    type="checkbox" 
                    class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    x-model="selectAll"
                    @change="toggleSelectAll()"
                >
            </th>
        @endif
        
        @foreach($headers as $header)
            <x-molecules.table-header-cell
                :header="$header"
                :sortable="$sortable"
                :index="$loop->index"
            />
        @endforeach
        
        @if(isset($actionsSlot))
            <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
            </th>
        @endif
    </tr>
</thead>