@php
    $activeSection = request()->query('section', 'general');
@endphp

<x-templates.app-layout 
    title="Settings" 
    pageType="settings" 
    :activeSection="$activeSection"
    :navigation="[
        ['label' => 'General', 'section' => 'general', 'icon' => 'cog', 'route' => route('codesnoutr.settings', ['section' => 'general'])],
        ['label' => 'Scanning', 'section' => 'scanning', 'icon' => 'shield', 'route' => route('codesnoutr.settings', ['section' => 'scanning'])],
        ['label' => 'AI Integration', 'section' => 'ai', 'icon' => 'user', 'route' => route('codesnoutr.settings', ['section' => 'ai'])],
        ['label' => 'Interface', 'section' => 'ui', 'icon' => 'bell', 'route' => route('codesnoutr.settings', ['section' => 'ui'])],
        ['label' => 'Debug Bar', 'section' => 'debugbar', 'icon' => 'cog', 'route' => route('codesnoutr.settings', ['section' => 'debugbar'])],
        ['label' => 'Reports', 'section' => 'reports', 'icon' => 'bell', 'route' => route('codesnoutr.settings', ['section' => 'reports'])],
        ['label' => 'Advanced', 'section' => 'advanced', 'icon' => 'user', 'route' => route('codesnoutr.settings', ['section' => 'advanced'])]
    ]">
    @livewire('codesnoutr-settings', ['initialTab' => $activeSection])
</x-templates.app-layout>
