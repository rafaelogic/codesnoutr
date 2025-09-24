@props([])

<td class="px-6 py-4">
    <input 
        type="checkbox" 
        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        :checked="selectedRows.includes(index)"
        @change="toggleRowSelection(index)"
    >
</td>