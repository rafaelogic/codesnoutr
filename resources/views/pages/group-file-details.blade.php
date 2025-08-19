@extends('codesnoutr::layouts.app')

@section('title', 'Group File Details - ' . $title)

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        @livewire('codesnoutr-group-file-details', [
            'scanId' => $scanId,
            'title' => $title,
            'category' => $category,
            'severity' => $severity,
            'description' => $description,
            'rule' => $rule,
            'suggestion' => $suggestion,
        ])
    </div>
</div>
@endsection
