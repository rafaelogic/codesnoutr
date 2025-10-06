@props([
    'currentFile',
])

@if($currentFile)
<x-codesnoutr::molecules.card variant="info" class="mb-6"></x-codesnoutr::molecules.card>
@endif