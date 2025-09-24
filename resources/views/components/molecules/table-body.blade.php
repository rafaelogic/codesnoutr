@props([
    'headers' => [],
    'rows' => [],
    'selectable' => false,
    'loading' => false,
    'emptyMessage' => 'No data available'
])

<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
    @if($loading)
        <x-molecules.table-loading-rows 
            :headers="$headers" 
            :selectable="$selectable"
            :hasActions="isset($actionsSlot)"
        />
    @elseif(empty($rows))
        <x-molecules.table-empty-row 
            :colspan="count($headers) + ($selectable ? 1 : 0) + (isset($actionsSlot) ? 1 : 0)"
            :message="$emptyMessage"
        />
    @else
        <template x-for="(row, index) in filteredData" :key="index">
            <tr :class="selectedRows.includes(index) ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700'">
                @if($selectable)
                    <x-molecules.table-checkbox-cell />
                @endif
                
                @foreach($headers as $headerIndex => $header)
                    <x-molecules.table-data-cell 
                        :header="$header" 
                        :index="$headerIndex"
                    />
                @endforeach
                
                @if(isset($actionsSlot))
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        {{ $actionsSlot }}
                    </td>
                @endif
            </tr>
        </template>
    @endif
</tbody>