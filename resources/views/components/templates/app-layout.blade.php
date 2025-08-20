@props([
    'title' => 'CodeSnoutr',
    'subtitle' => null,
    'showNavigation' => true,
    'showSidebar' => true,
    'maxWidth' => '[90%]',
    'pageType' => 'default', // default, dashboard, settings
    'stats' => [],
    'actions' => null,
    'navigation' => [],
    'activeSection' => ''
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
          darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
          mobileMenuOpen: false
      }" 
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" 
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - CodeSnoutr</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Development or fallback styles -->
        <script src="https://cdn.tailwindcss.com"></script>
        <!-- Alpine.js is included with Livewire, so commenting out to prevent conflicts -->
        <!-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> -->
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            primary: {
                                50: '#eff6ff',
                                500: '#3b82f6',
                                600: '#2563eb',
                                700: '#1d4ed8',
                                900: '#1e3a8a',
                            }
                        }
                    }
                }
            }
        </script>
    @endif
    
    <!-- Livewire Styles -->
    @livewireStyles

    <!-- Additional Styles -->
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen font-sans antialiased transition-colors duration-300"
      x-bind:class="{ 'dark': darkMode }">
    
    <!-- Navigation -->
    @if($showNavigation)
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
        <div class="max-w-{{ $maxWidth }} mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('codesnoutr.dashboard') }}" class="flex items-center space-x-2">
                            <x-atoms.icon name="code" size="lg" color="primary" />
                            <span class="text-xl font-bold text-gray-900 dark:text-white">CodeSnoutr</span>
                        </a>
                    </div>

                    <!-- Primary Navigation -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <!-- Navigation Links -->
                        <a href="{{ route('codesnoutr.dashboard') }}" 
                           class="{{ request()->routeIs('codesnoutr.dashboard') 
                               ? 'border-indigo-500 text-gray-900 dark:text-white' 
                               : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            Dashboard
                        </a>
                        <a href="{{ route('codesnoutr.scan') }}" 
                           class="{{ request()->routeIs('codesnoutr.scan') 
                               ? 'border-indigo-500 text-gray-900 dark:text-white' 
                               : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            New Scan
                        </a>
                        <a href="{{ route('codesnoutr.results') }}" 
                           class="{{ request()->routeIs('codesnoutr.results*') 
                               ? 'border-indigo-500 text-gray-900 dark:text-white' 
                               : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            Results
                        </a>
                        <a href="{{ route('codesnoutr.settings') }}" 
                           class="{{ request()->routeIs('codesnoutr.settings*') 
                               ? 'border-indigo-500 text-gray-900 dark:text-white' 
                               : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            Settings
                        </a>
                    </div>
                </div>

                <!-- Secondary Navigation -->
                <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <div class="flex items-center">
                        <button @click="darkMode = !darkMode" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                :class="darkMode ? 'bg-indigo-600' : 'bg-gray-200'">
                            <span class="sr-only">Toggle dark mode</span>
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                  :class="darkMode ? 'translate-x-5' : 'translate-x-0'">
                                <!-- Sun icon (light mode) -->
                                <svg x-show="!darkMode" class="h-3 w-3 text-yellow-500 absolute top-1 left-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                                </svg>
                                
                                <!-- Moon icon (dark mode) -->
                                <svg x-show="darkMode" class="h-3 w-3 text-indigo-600 absolute top-1 left-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                </svg>
                            </span>
                        </button>
                        
                        <!-- Optional text label -->
                        <span class="ml-3 text-sm text-gray-700 dark:text-gray-300 hidden lg:block">
                            <span x-text="darkMode ? 'Dark' : 'Light'"></span>
                        </span>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="sm:hidden flex items-center">
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition-colors duration-200">
                        <span class="sr-only">Open main menu</span>
                        <!-- Hamburger icon when menu is closed -->
                        <svg x-show="!mobileMenuOpen" class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <!-- X icon when menu is open -->
                        <svg x-show="mobileMenuOpen" class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.away="mobileMenuOpen = false"
             class="sm:hidden">
            <div class="pt-2 pb-3 space-y-1 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <!-- Mobile Navigation Links -->
                <a href="{{ route('codesnoutr.dashboard') }}" 
                   @click="mobileMenuOpen = false"
                   class="{{ request()->routeIs('codesnoutr.dashboard') 
                       ? 'bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-700 dark:text-indigo-200' 
                       : 'border-transparent text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200">
                    Dashboard
                </a>
                <a href="{{ route('codesnoutr.scan') }}" 
                   @click="mobileMenuOpen = false"
                   class="{{ request()->routeIs('codesnoutr.scan') 
                       ? 'bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-700 dark:text-indigo-200' 
                       : 'border-transparent text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200">
                    New Scan
                </a>
                <a href="{{ route('codesnoutr.results') }}" 
                   @click="mobileMenuOpen = false"
                   class="{{ request()->routeIs('codesnoutr.results*') 
                       ? 'bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-700 dark:text-indigo-200' 
                       : 'border-transparent text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200">
                    Results
                </a>
                <a href="{{ route('codesnoutr.settings') }}" 
                   @click="mobileMenuOpen = false"
                   class="{{ request()->routeIs('codesnoutr.settings*') 
                       ? 'bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-700 dark:text-indigo-200' 
                       : 'border-transparent text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200">
                    Settings
                </a>
                
                <!-- Mobile Dark Mode Toggle -->
                <div class="pl-3 pr-4 py-2 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-medium text-gray-600 dark:text-gray-300">Dark Mode</span>
                        <button @click="darkMode = !darkMode" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                :class="darkMode ? 'bg-indigo-600' : 'bg-gray-200'">
                            <span class="sr-only">Toggle dark mode</span>
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                  :class="darkMode ? 'translate-x-5' : 'translate-x-0'">
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    @endif

    <!-- Page Header -->
    @if(isset($header))
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
        <div class="max-w-{{ $maxWidth }} mx-auto py-6 px-4 sm:px-6 lg:px-8">
            {{ $header }}
        </div>
    </header>
    @elseif($pageType === 'dashboard')
    <!-- Dashboard Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
        <div class="max-w-{{ $maxWidth }} mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
                    @endif
                </div>
                
                @if($actions)
                    <div class="flex space-x-3">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    </header>
    @endif

    <!-- Stats Section for Dashboard -->
    @if($pageType === 'dashboard' && !empty($stats))
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
        <div class="max-w-{{ $maxWidth }} mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($stats as $stat)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 transition-colors duration-300">
                    <div class="flex items-center">
                        @if(isset($stat['icon']))
                        <div class="flex-shrink-0">
                            <x-atoms.icon :name="$stat['icon']" size="lg" :color="$stat['color'] ?? 'gray'" />
                        </div>
                        @endif
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    {{ $stat['label'] }}
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ $stat['value'] }}
                                    </div>
                                    @if(isset($stat['change']))
                                    <div class="ml-2 flex items-baseline text-sm font-semibold {{ $stat['change_color'] ?? 'text-green-600' }}">
                                        {{ $stat['change'] }}
                                    </div>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Sidebar for Settings -->
    @if($pageType === 'settings' && $showSidebar)
    <div class="flex min-h-0 flex-1 overflow-hidden">
        <!-- Sidebar -->
        <div class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex w-64 flex-col">
                <div class="flex min-h-0 flex-1 flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-colors duration-300">
                    <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                        <nav class="mt-5 px-3 space-y-1">
                            @foreach($navigation as $item)
                            <a href="{{ $item['route'] ?? '#' }}" 
                               class="@if(($item['active'] ?? false) || $activeSection === ($item['section'] ?? '')) bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white @else text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white @endif group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200">
                                @if(isset($item['icon']))
                                <x-atoms.icon :name="$item['icon']" size="sm" class="mr-3 flex-shrink-0 {{ ($item['active'] ?? false) || $activeSection === ($item['section'] ?? '') ? 'text-gray-500 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300' }}" />
                                @endif
                                {{ $item['label'] }}
                            </a>
                            @endforeach
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="max-w-{{ $maxWidth }} mx-auto px-4 sm:px-6 lg:px-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </div>
    @else
    <!-- Main Content -->
    <main class="flex-1">
        <div class="max-w-{{ $maxWidth }} mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </div>
    </main>
    @endif

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 transition-colors duration-300">
        <div class="max-w-{{ $maxWidth }} mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                <p>&copy; {{ date('Y') }} CodeSnoutr. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Dark Mode Enhancement Script -->
    <script>
        // Initialize dark mode immediately to prevent flash
        (function() {
            const isDark = localStorage.getItem('theme') === 'dark' || 
                          (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <!-- Livewire Scripts (single instance) -->
    @livewireScripts

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
