@props([
    'header',
    'index' => 0
])

<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
    @if(isset($header['component']))
        <!-- Custom component rendering would go here -->
        <span x-text="row[{{ $index }}]"></span>
    @else
        <span x-text="row[{{ $index }}]"></span>
    @endif
</td>