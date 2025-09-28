@props([
    'columns' => 1, // 1, 2, 3, 4, 6, 12
    'cols' => null, // alias for columns
    'lgCols' => null, // lg:grid-cols-*
    'gap' => 'default', // sm, default, lg, md
    'responsive' => true
])

@php
    $actualColumns = $cols ?? $columns ?? 1;
    
    $gapClasses = [
        'sm' => 'gap-4',
        'default' => 'gap-6',
        'md' => 'gap-6',
        'lg' => 'gap-8'
    ];
    
    $columnClasses = [
        1 => 'grid-cols-1',
        2 => $responsive ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-2',
        3 => $responsive ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' : 'grid-cols-3',
        4 => $responsive ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4' : 'grid-cols-4',
        6 => $responsive ? 'grid-cols-2 md:grid-cols-3 lg:grid-cols-6' : 'grid-cols-6',
        12 => $responsive ? 'grid-cols-4 md:grid-cols-6 lg:grid-cols-12' : 'grid-cols-12'
    ];
    
    $lgColumnClasses = [
        1 => 'lg:grid-cols-1',
        2 => 'lg:grid-cols-2',
        3 => 'lg:grid-cols-3',
        4 => 'lg:grid-cols-4',
        6 => 'lg:grid-cols-6',
        12 => 'lg:grid-cols-12'
    ];
    
    $classes = implode(' ', array_filter([
        'grid',
        $columnClasses[$actualColumns] ?? $columnClasses[1],
        $lgCols ? $lgColumnClasses[$lgCols] : null,
        $gapClasses[$gap] ?? $gapClasses['default']
    ]));
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>