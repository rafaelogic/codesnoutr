@props([
    'headers' => [],
    'selectable' => false,
    'hasActions' => false,
    'rows' => 5
])

@for($i = 0; $i < $rows; $i++)
    <tr>
        @if($selectable)
            <td class="px-6 py-4">
                <div class="h-4 w-4 bg-gray-200 dark:bg-gray-600 rounded animate-pulse"></div>
            </td>
        @endif
        
        @foreach($headers as $header)
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded animate-pulse w-{{ rand(16, 32) }}"></div>
            </td>
        @endforeach
        
        @if($hasActions)
            <td class="px-6 py-4">
                <div class="h-4 w-8 bg-gray-200 dark:bg-gray-600 rounded animate-pulse"></div>
            </td>
        @endif
    </tr>
@endfor