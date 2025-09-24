@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
    'action' => null
])

<div class="py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        @if(!empty($breadcrumbs))
            <nav class="mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    @foreach($breadcrumbs as $breadcrumb)
                        <li class="flex items-center">
                            @if($loop->index > 0)
                                <x-atoms.icon name="chevron-right" size="xs" class="mx-2 text-gray-400" />
                            @endif
                            
                            @if(isset($breadcrumb['href']) && !$loop->last)
                                <a href="{{ $breadcrumb['href'] }}" class="text-blue-600 hover:text-blue-700">
                                    {{ $breadcrumb['label'] }}
                                </a>
                            @else
                                <x-atoms.text color="muted">{{ $breadcrumb['label'] }}</x-atoms.text>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <!-- Header Content -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
                <x-atoms.text as="h1" size="3xl" weight="bold">
                    {{ $title }}
                </x-atoms.text>
                
                @if($description)
                    <x-atoms.text color="muted" class="mt-2">
                        {{ $description }}
                    </x-atoms.text>
                @endif
            </div>
            
            @if($action)
                <div class="mt-4 sm:mt-0 sm:ml-4 flex-shrink-0">
                    {{ $action }}
                </div>
            @endif
        </div>
        
        <!-- Additional Content -->
        @if(isset($slot) && trim($slot))
            <div class="mt-6">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>