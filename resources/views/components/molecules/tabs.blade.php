@props([
    'tabs' => [],
    'activeTab' => '',
    'variant' => 'underline' // underline, pills, bordered
])

@php
    $containerClasses = [
        'underline' => 'border-b border-gray-200',
        'pills' => 'bg-gray-100 p-1 rounded-lg',
        'bordered' => 'border border-gray-200 rounded-lg'
    ];
    
    $listClasses = [
        'underline' => '-mb-px flex space-x-8',
        'pills' => 'flex space-x-1',
        'bordered' => 'flex'
    ];
    
    $containerClass = $containerClasses[$variant] ?? $containerClasses['underline'];
    $listClass = $listClasses[$variant] ?? $listClasses['underline'];
@endphp

<div {{ $attributes->merge(['class' => $containerClass]) }}>
    <nav class="{{ $listClass }}" aria-label="Tabs">
        @foreach($tabs as $tab)
            @php
                $isActive = $activeTab === ($tab['key'] ?? '');
                
                if ($variant === 'underline') {
                    $linkClass = $isActive 
                        ? 'border-blue-500 text-blue-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm';
                } elseif ($variant === 'pills') {
                    $linkClass = $isActive
                        ? 'bg-white text-gray-700 px-3 py-2 font-medium text-sm rounded-md shadow-sm'
                        : 'text-gray-500 hover:text-gray-700 px-3 py-2 font-medium text-sm rounded-md';
                } else { // bordered
                    $linkClass = $isActive
                        ? 'bg-gray-100 text-gray-900 px-4 py-2 font-medium text-sm border-r border-gray-200 first:rounded-l-lg last:rounded-r-lg last:border-r-0'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 px-4 py-2 font-medium text-sm border-r border-gray-200 first:rounded-l-lg last:rounded-r-lg last:border-r-0';
                }
            @endphp
            
            <a 
                href="{{ $tab['url'] ?? '#' }}" 
                class="{{ $linkClass }}"
                @if($isActive) aria-current="page" @endif
            >
                @if(isset($tab['icon']))
                    <x-atoms.icon :name="$tab['icon']" size="sm" class="mr-2 inline" />
                @endif
                {{ $tab['label'] }}
                
                @if(isset($tab['count']))
                    <x-atoms.badge variant="secondary" size="sm" class="ml-2">
                        {{ $tab['count'] }}
                    </x-atoms.badge>
                @endif
            </a>
        @endforeach
    </nav>
</div>
