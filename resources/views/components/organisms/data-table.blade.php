@props([
    'headers' => [],
    'rows' => [],
    'sortable' => true,
    'searchable' => true,
    'paginated' => true,
    'selectable' => false,
    'actions' => [],
    'emptyMessage' => 'No data available',
    'loading' => false,
    'stickyHeader' => false
])

@php
    $tableId = uniqid('table_');
@endphp

<div 
    x-data="dataTable()"
    class="bg-white shadow overflow-hidden sm:rounded-md"
    {{ $attributes }}
>
    <!-- Table Header with Search and Actions -->
    @if($searchable || !empty($actions))
        <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                @if($searchable)
                    <div class="flex-1 max-w-lg">
                        <x-molecules.search-box 
                            placeholder="Search..." 
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
            </div>
        </div>
    @endif
    
    <!-- Table Container -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="{{ $tableId }}">
            <!-- Table Head -->
            <thead class="bg-gray-50 {{ $stickyHeader ? 'sticky top-0 z-10' : '' }}">
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
                        <th 
                            scope="col" 
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider {{ $sortable && ($header['sortable'] ?? true) ? 'cursor-pointer select-none hover:bg-gray-100' : '' }}"
                            @if($sortable && ($header['sortable'] ?? true))
                                @click="sort('{{ $header['key'] ?? $loop->index }}')"
                            @endif
                        >
                            <div class="flex items-center space-x-1">
                                <span>{{ $header['label'] ?? $header }}</span>
                                @if($sortable && ($header['sortable'] ?? true))
                                    <div class="flex flex-col">
                                        <x-atoms.icon 
                                            name="chevron-up" 
                                            size="xs" 
                                            ::class="sortField === '{{ $header['key'] ?? $loop->index }}' && sortDirection === 'asc' ? 'text-blue-600' : 'text-gray-400'"
                                        />
                                        <x-atoms.icon 
                                            name="chevron-down" 
                                            size="xs" 
                                            ::class="sortField === '{{ $header['key'] ?? $loop->index }}' && sortDirection === 'desc' ? 'text-blue-600' : 'text-gray-400'"
                                        />
                                    </div>
                                @endif
                            </div>
                        </th>
                    @endforeach
                    
                    @if(!empty($actions) || isset($rowActions))
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    @endif
                </tr>
            </thead>
            
            <!-- Table Body -->
            <tbody class="bg-white divide-y divide-gray-200">
                @if($loading)
                    @for($i = 0; $i < 5; $i++)
                        <tr>
                            @if($selectable)
                                <td class="px-6 py-4">
                                    <div class="h-4 w-4 bg-gray-200 rounded animate-pulse"></div>
                                </td>
                            @endif
                            @foreach($headers as $header)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="h-4 bg-gray-200 rounded animate-pulse w-{{ rand(16, 32) }}"></div>
                                </td>
                            @endforeach
                            @if(!empty($actions) || isset($rowActions))
                                <td class="px-6 py-4">
                                    <div class="h-4 w-8 bg-gray-200 rounded animate-pulse"></div>
                                </td>
                            @endif
                        </tr>
                    @endfor
                @elseif(empty($rows))
                    <tr>
                        <td colspan="{{ count($headers) + ($selectable ? 1 : 0) + (!empty($actions) || isset($rowActions) ? 1 : 0) }}" class="px-6 py-12 text-center">
                            <x-molecules.empty-state 
                                icon="search"
                                title="No results found"
                                :description="$emptyMessage"
                                size="sm"
                            />
                        </td>
                    </tr>
                @else
                    <template x-for="(row, index) in filteredData" :key="index">
                        <tr :class="selectedRows.includes(index) ? 'bg-blue-50' : 'hover:bg-gray-50'">
                            @if($selectable)
                                <td class="px-6 py-4">
                                    <input 
                                        type="checkbox" 
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        :checked="selectedRows.includes(index)"
                                        @change="toggleRowSelection(index)"
                                    >
                                </td>
                            @endif
                            
                            @foreach($headers as $headerIndex => $header)
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(isset($header['component']))
                                        <!-- Custom component rendering would go here -->
                                        <span x-text="row[{{ $headerIndex }}]"></span>
                                    @else
                                        <span x-text="row[{{ $headerIndex }}]"></span>
                                    @endif
                                </td>
                            @endforeach
                            
                            @if(isset($rowActions))
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    {{ $rowActions }}
                                </td>
                            @endif
                        </tr>
                    </template>
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($paginated && !$loading && !empty($rows))
        <div class="border-t border-gray-200 px-4 py-3 sm:px-6">
            <x-molecules.pagination 
                :currentPage="1"
                :totalPages="10"
                :total="100"
                :perPage="10"
                baseUrl=""
            />
        </div>
    @endif
</div>

<script>
function dataTable() {
    return {
        searchTerm: '',
        sortField: '',
        sortDirection: 'asc',
        selectedRows: [],
        selectAll: false,
        data: @json($rows),
        filteredData: @json($rows),
        
        filterData() {
            if (!this.searchTerm) {
                this.filteredData = this.data;
                return;
            }
            
            this.filteredData = this.data.filter(row => {
                return row.some(cell => 
                    String(cell).toLowerCase().includes(this.searchTerm.toLowerCase())
                );
            });
        },
        
        sort(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            
            this.filteredData.sort((a, b) => {
                const aVal = a[field] || '';
                const bVal = b[field] || '';
                
                if (this.sortDirection === 'asc') {
                    return aVal.localeCompare(bVal);
                } else {
                    return bVal.localeCompare(aVal);
                }
            });
        },
        
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedRows = this.filteredData.map((_, index) => index);
            } else {
                this.selectedRows = [];
            }
        },
        
        toggleRowSelection(index) {
            const rowIndex = this.selectedRows.indexOf(index);
            if (rowIndex > -1) {
                this.selectedRows.splice(rowIndex, 1);
            } else {
                this.selectedRows.push(index);
            }
            
            this.selectAll = this.selectedRows.length === this.filteredData.length;
        }
    }
}
</script>
