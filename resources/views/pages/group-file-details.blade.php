<x-templates.app-layout :title="'Group File Details - ' . $title">
    @livewire('codesnoutr-group-file-details', [
        'scanId' => $scanId,
        'title' => $title,
        'category' => $category,
        'severity' => $severity,
        'description' => $description,
        'rule' => $rule,
        'suggestion' => $suggestion,
    ])
</x-templates.app-layout>
