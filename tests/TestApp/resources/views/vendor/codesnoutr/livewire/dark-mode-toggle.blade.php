<div class="flex items-center">
    <button wire:click="toggleDarkMode" 
            :disabled="isLoading"
            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed {{ $darkMode ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}"
            role="switch" 
            :aria-checked="darkMode"
            title="{{ $this->getToggleLabel() }}">
        <span class="sr-only">{{ $this->getToggleLabel() }}</span>
        
        <!-- Loading spinner -->
        <div wire:loading wire:target="toggleDarkMode" 
             class="absolute inset-0 flex items-center justify-center">
            <svg class="h-3 w-3 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <!-- Toggle indicator -->
        <span wire:loading.remove wire:target="toggleDarkMode"
              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $darkMode ? 'translate-x-5' : 'translate-x-0' }}">
            <!-- Sun icon (light mode) -->
            @if(!$darkMode)
            <svg class="h-3 w-3 text-yellow-500 absolute top-1 left-1" 
                 fill="currentColor" 
                 viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
            </svg>
            @endif
            
            <!-- Moon icon (dark mode) -->
            @if($darkMode)
            <svg class="h-3 w-3 text-indigo-600 absolute top-1 left-1" 
                 fill="currentColor" 
                 viewBox="0 0 20 20">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
            </svg>
            @endif
        </span>
    </button>

    <!-- Optional text label -->
    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300 hidden lg:block">
        {{ $this->getToggleText() }} Mode
    </span>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        // Listen for theme initialization
        Livewire.on('theme-initialized', (data) => {
            document.documentElement.classList.toggle('dark', data.darkMode);
            localStorage.setItem('codesnoutr-theme', data.darkMode ? 'dark' : 'light');
        });

        // Listen for theme changes
        Livewire.on('theme-changed', (data) => {
            document.documentElement.classList.toggle('dark', data.darkMode);
            localStorage.setItem('codesnoutr-theme', data.darkMode ? 'dark' : 'light');
            
            // Emit theme change to other components
            window.dispatchEvent(new CustomEvent('theme-updated', {
                detail: { darkMode: data.darkMode }
            }));
        });

        // Listen for theme errors
        Livewire.on('theme-error', (data) => {
            window.CodeSnoutr.notify(data.message, 'error');
        });
    });

    // Apply theme from localStorage on page load
    const savedTheme = localStorage.getItem('codesnoutr-theme');
    if (savedTheme) {
        document.documentElement.classList.toggle('dark', savedTheme === 'dark');
    }

    // Listen for system theme changes
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addListener((e) => {
        // Only auto-switch if no manual preference is saved
        if (!localStorage.getItem('codesnoutr-theme')) {
            document.documentElement.classList.toggle('dark', e.matches);
            @this.call('setDarkMode', e.matches);
        }
    });
</script>
@endpush
