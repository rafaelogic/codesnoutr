@props([
    'colspan' => 1,
    'message' => 'No data available'
])

<tr>
    <td colspan="{{ $colspan }}" class="px-6 py-12 text-center">
        <x-molecules.empty-state 
            icon="search"
            title="No results found"
            :description="$message"
            size="sm"
        />
    </td>
</tr>