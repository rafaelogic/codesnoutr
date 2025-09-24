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

<x-atoms.surface 
    variant="default"
    shadow="default" 
    padding="none"
    class="overflow-hidden sm:rounded-md"
    x-data="dataTable()"
    {{ $attributes }}
>
    <!-- Table Toolbar -->
    <x-molecules.table-toolbar 
        :searchable="$searchable"
        :actions="$actions"
    />
    
    <!-- Table Container -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="{{ $tableId }}">
            <!-- Table Header -->
            <x-molecules.table-header 
                :headers="$headers"
                :sortable="$sortable"
                :selectable="$selectable"
                :stickyHeader="$stickyHeader"
            >
                @if(isset($rowActions))
                    <x-slot name="actionsSlot">true</x-slot>
                @endif
            </x-molecules.table-header>
            
            <!-- Table Body -->
            <x-molecules.table-body 
                :headers="$headers"
                :rows="$rows"
                :selectable="$selectable"
                :loading="$loading"
                :emptyMessage="$emptyMessage"
            >
                @if(isset($rowActions))
                    <x-slot name="actionsSlot">{{ $rowActions }}</x-slot>
                @endif
            </x-molecules.table-body>
        </table>
    </div>
    
    <!-- Table Pagination -->
    @if($paginated && !$loading && !empty($rows))
        <x-molecules.table-pagination />
    @endif
</x-atoms.surface>

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
