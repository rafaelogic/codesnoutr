@props([
    'title' => '',
    'description' => '',
    'footer' => false,
    'padding' => 'default', // 'default', 'none', 'sm', 'lg'
    'variant' => 'default', // default, elevated, bordered, ghost
    'shadow' => 'default'
])

<x-atoms.surface 
    :variant="$variant" 
    :shadow="$shadow"
    padding="none" 
    {{ $attributes }}
>
    @if($title || $description || isset($header))
        <x-molecules.card-header 
            :title="$title" 
            :description="$description"
        >
            @if(isset($header))
                <x-slot name="actions">{{ $header }}</x-slot>
            @endif
        </x-molecules.card-header>
    @endif

    <x-molecules.card-body :padding="$padding">
        {{ $slot }}
    </x-molecules.card-body>

    @if($footer)
        <x-molecules.card-footer>
            {{ $footer }}
        </x-molecules.card-footer>
    @endif
</x-atoms.surface>
