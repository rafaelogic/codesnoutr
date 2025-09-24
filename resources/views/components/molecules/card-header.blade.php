@props([
    'title' => '',
    'description' => ''
])

<div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between">
        <div>
            @if($title)
                <x-atoms.text as="h3" size="lg" weight="medium">
                    {{ $title }}
                </x-atoms.text>
            @endif
            
            @if($description)
                <x-atoms.text size="sm" color="muted" class="mt-1">
                    {{ $description }}
                </x-atoms.text>
            @endif
        </div>
        
        @if(isset($actions))
            <div>
                {{ $actions }}
            </div>
        @endif
    </div>
</div>