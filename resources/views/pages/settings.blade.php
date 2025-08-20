<x-templates.app-layout title="Settings" pageType="settings" :navigation="[
    ['label' => 'General', 'section' => 'general', 'icon' => 'settings'],
    ['label' => 'Scanning', 'section' => 'scanning', 'icon' => 'search'],
    ['label' => 'Notifications', 'section' => 'notifications', 'icon' => 'bell'],
    ['label' => 'Advanced', 'section' => 'advanced', 'icon' => 'code']
]">
    @livewire('codesnoutr-settings')
</x-templates.app-layout>
