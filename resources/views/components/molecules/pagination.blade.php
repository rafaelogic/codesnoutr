@props([
    'currentPage' => 1,
    'totalPages' => 1,
    'perPage' => 10,
    'total' => 0,
    'baseUrl' => '',
    'showInfo' => true,
    'showPageNumbers' => true,
    'maxLinks' => 7
])

@php
    $startItem = ($currentPage - 1) * $perPage + 1;
    $endItem = min($currentPage * $perPage, $total);
    
    $start = max(1, $currentPage - floor($maxLinks / 2));
    $end = min($totalPages, $start + $maxLinks - 1);
    
    if ($end - $start + 1 < $maxLinks) {
        $start = max(1, $end - $maxLinks + 1);
    }
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center justify-between']) }}>
    @if($showInfo)
        <div class="flex-1 flex justify-between sm:hidden">
            @if($currentPage > 1)
                <a href="{{ $baseUrl }}?page={{ $currentPage - 1 }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                </a>
            @endif
            
            @if($currentPage < $totalPages)
                <a href="{{ $baseUrl }}?page={{ $currentPage + 1 }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </a>
            @endif
        </div>
        
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium">{{ number_format($startItem) }}</span>
                    to
                    <span class="font-medium">{{ number_format($endItem) }}</span>
                    of
                    <span class="font-medium">{{ number_format($total) }}</span>
                    results
                </p>
            </div>
            
            @if($showPageNumbers && $totalPages > 1)
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        {{-- Previous Button --}}
                        @if($currentPage > 1)
                            <a href="{{ $baseUrl }}?page={{ $currentPage - 1 }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <x-atoms.icon name="chevron-left" size="sm" />
                            </a>
                        @else
                            <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-300 cursor-not-allowed">
                                <x-atoms.icon name="chevron-left" size="sm" />
                            </span>
                        @endif
                        
                        {{-- Page Numbers --}}
                        @if($start > 1)
                            <a href="{{ $baseUrl }}?page=1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>
                            @if($start > 2)
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                            @endif
                        @endif
                        
                        @for($page = $start; $page <= $end; $page++)
                            @if($page == $currentPage)
                                <span aria-current="page" class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $baseUrl }}?page={{ $page }}" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    {{ $page }}
                                </a>
                            @endif
                        @endfor
                        
                        @if($end < $totalPages)
                            @if($end < $totalPages - 1)
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                            @endif
                            <a href="{{ $baseUrl }}?page={{ $totalPages }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">{{ $totalPages }}</a>
                        @endif
                        
                        {{-- Next Button --}}
                        @if($currentPage < $totalPages)
                            <a href="{{ $baseUrl }}?page={{ $currentPage + 1 }}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <x-atoms.icon name="chevron-right" size="sm" />
                            </a>
                        @else
                            <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-300 cursor-not-allowed">
                                <x-atoms.icon name="chevron-right" size="sm" />
                            </span>
                        @endif
                    </nav>
                </div>
            @endif
        </div>
    @endif
</div>
