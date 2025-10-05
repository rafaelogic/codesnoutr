<x-codesnoutr::templates.app-layout 
    title="Fix All Progress" 
    subtitle="AI-powered batch issue fixing"
    :showSidebar="true"
    pageType="fix-all">
    
    @livewire('codesnoutr-fix-all-progress', ['sessionId' => $sessionId])
    
</x-codesnoutr::templates.app-layout>