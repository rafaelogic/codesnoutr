@props([
    'title' => 'CodeSnoutr',
    'subtitle' => null,
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
        darkMode: localStorage.getItem('codesnoutr-theme') === 'dark' || (!localStorage.getItem('codesnoutr-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('codesnoutr-sidebar-collapsed') === 'true' || false,
        initDarkMode() {
                // Watch for dark mode changes
                this.$watch('darkMode', val => {
                    localStorage.setItem('codesnoutr-theme', val ? 'dark' : 'light');
                    document.documentElement.classList.toggle('dark', val);
                    
                    // Sync with any Livewire components
                    window.dispatchEvent(new CustomEvent('theme-updated', {
                        detail: { darkMode: val }
                    }));
                });
                
                // Listen for external theme changes from Livewire
                window.addEventListener('theme-sync', (e) => {
                    if (this.darkMode !== e.detail.darkMode) {
                        this.darkMode = e.detail.darkMode;
                    }
                });
                
                // Listen for system theme changes
                const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
                mediaQuery.addEventListener('change', (e) => {
                    // Only auto-switch if no manual preference is saved
                    if (!localStorage.getItem('codesnoutr-theme')) {
                        this.darkMode = e.matches;
                    }
                });
                
                // Apply initial theme
                document.documentElement.classList.toggle('dark', this.darkMode);
            }
        }" 
    x-init="initDarkMode()" 
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
    @php
        $manifestPath = public_path('vendor/codesnoutr/build/manifest.json');
        $hasBuiltAssets = file_exists($manifestPath);
        $manifest = $hasBuiltAssets ? json_decode(file_get_contents($manifestPath), true) : [];
        
        $cssFiles = [];
        $jsFile = null;
        
        if ($hasBuiltAssets && $manifest) {
            // Look for CSS files - they might be keyed by source path or file name
            if (isset($manifest['resources/css/codesnoutr.css'])) {
                $cssFiles[] = 'vendor/codesnoutr/build/' . $manifest['resources/css/codesnoutr.css']['file'];
            }
            if (isset($manifest['resources/css/app.css'])) {
                $cssFiles[] = 'vendor/codesnoutr/build/' . $manifest['resources/css/app.css']['file'];
            }
            
            // Look for JS file
            if (isset($manifest['resources/js/app.js'])) {
                $jsFile = 'vendor/codesnoutr/build/' . $manifest['resources/js/app.js']['file'];
            }
            
            // Fallback: iterate through all entries
            if (empty($cssFiles) || !$jsFile) {
                foreach ($manifest as $file => $details) {
                    if (str_contains($file, '.css') || str_contains($details['file'] ?? '', '.css')) {
                        $cssFiles[] = 'vendor/codesnoutr/build/' . $details['file'];
                    } elseif (str_contains($file, '.js') || str_contains($details['file'] ?? '', '.js')) {
                        $jsFile = 'vendor/codesnoutr/build/' . $details['file'];
                    }
                }
            }
        }
    @endphp
    
    @if($hasBuiltAssets && !empty($cssFiles))
        <!-- Use built assets from package -->
        @foreach($cssFiles as $cssFile)
            <link rel="stylesheet" href="{{ asset($cssFile) }}">
        @endforeach
        @if($jsFile)
            <script src="{{ asset($jsFile) }}" defer></script>
        @endif
    @elseif(file_exists(public_path('build/manifest.json')))
        <!-- Use main app Vite assets if available -->
        @vite(['resources/css/app.css', 'resources/css/codesnoutr.css', 'resources/js/app.js'])
    @else
        <!-- Development or fallback styles -->
        <script src="https://cdn.tailwindcss.com"></script>
        <!-- Alpine.js is included with Livewire -->
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
<body class="flex flex-col w-full bg-gray-50 dark:bg-gray-900 min-h-screen font-sans antialiased"
      x-bind:class="{ 'dark': darkMode }">
    
    <!-- Top Navigation Bar -->
    <nav class="fixed top-0 z-50 w-full bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start">
                    @if($showSidebar)
                    <!-- Sidebar toggle for mobile -->
                    <button @click="sidebarOpen = !sidebarOpen" 
                            type="button" 
                            class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-all duration-300">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                        </svg>
                    </button>
                    <!-- Desktop sidebar toggle -->
                    <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('codesnoutr-sidebar-collapsed', sidebarCollapsed)" 
                            type="button" 
                            class="hidden lg:inline-flex items-center p-2 text-sm text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-all duration-300 ml-3">
                        <span class="sr-only">Toggle sidebar</span>
                        <x-atoms.icon name="chevron-left" size="sm" x-show="!sidebarCollapsed" />
                        <x-atoms.icon name="chevron-right" size="sm" x-show="sidebarCollapsed" />
                    </button>
                    @endif
                    <!-- Logo -->
                    <a href="{{ route('codesnoutr.dashboard') }}" class="flex items-center ml-2 md:mr-24">
                        <svg xmlns="http://www.w3.org/2000/svg"
                                width="32" height="32" viewBox="0 0 128 128" role="img" aria-labelledby="title desc">
                            <title id="title">Code Snoutr icon</title>
                            <desc id="desc">Stylized pig snout with two code chevrons</desc>
                            <circle cx="64" cy="64" r="60" fill="#F7FBFF"/>
                            <path d="M34 48 L20 64 L34 80" fill="none" stroke="#2B6CB0" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M94 48 L108 64 L94 80" fill="none" stroke="#2B6CB0" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="36" y="46" width="56" height="36" rx="18" ry="18" fill="#F6AD55" stroke="#DD6B20" stroke-width="2"/>
                            <ellipse cx="52" cy="64" rx="5.5" ry="7" fill="#C05621"/>
                            <ellipse cx="76" cy="64" rx="5.5" ry="7" fill="#C05621"/>
                            <ellipse cx="62" cy="54" rx="10" ry="4" fill="#FFD8A8" opacity="0.6"/>
                            <ellipse cx="64" cy="86" rx="26" ry="6" fill="#000" opacity="0.06"/>
                        </svg>
                        <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white ml-2">CodeSnoutr</span>
                    </a>
                </div>
                <div class="flex items-center">
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode" 
                            type="button"
                            class="p-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-all duration-300">
                        <span class="sr-only">Toggle dark mode</span>
                        <x-atoms.icon name="sun" size="sm" x-show="darkMode" />
                        <x-atoms.icon name="moon" size="sm" x-show="!darkMode" />
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="flex" style="padding-top: 60px">
        @if($showSidebar)
        <!-- Sidebar -->
        <aside :class="sidebarCollapsed ? 'lg:w-16' : 'lg:w-64'" 
               class="fixed left-0 z-40 transition-all duration-300 hidden lg:block"
               style="top: 4rem; height: calc(100vh - 4rem);">
            <!-- Sidebar component -->
            <div class="flex flex-col h-full bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto transition-colors duration-300">
                
                <!-- Collapse/Expand Button -->
                <div class="flex items-center justify-end p-2 border-b border-gray-200 dark:border-gray-700">
                    <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('codesnoutr-sidebar-collapsed', sidebarCollapsed)" 
                            type="button"
                            :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                            class="p-1.5 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-all duration-300">
                        <span class="sr-only">Toggle sidebar</span>
                        <x-atoms.icon name="chevron-left" size="sm" x-show="!sidebarCollapsed" />
                        <x-atoms.icon name="chevron-right" size="sm" x-show="sidebarCollapsed" />
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 pt-2 px-3 space-y-1">
                    <!-- Main Navigation Items -->
                    <a href="{{ route('codesnoutr.dashboard') }}" 
                       :title="sidebarCollapsed ? 'Dashboard' : ''"
                       class="{{ request()->routeIs('codesnoutr.dashboard') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }} group flex items-center py-2 text-sm font-medium rounded-md transition-all duration-300"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('codesnoutr.dashboard') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" 
                             :class="sidebarCollapsed ? '' : 'mr-3'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Dashboard</span>
                    </a>

                    <a href="{{ route('codesnoutr.scan') }}" 
                       :title="sidebarCollapsed ? 'New Scan' : ''"
                       class="{{ request()->routeIs('codesnoutr.scan') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }} group flex items-center py-2 text-sm font-medium rounded-md transition-all duration-300"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('codesnoutr.scan') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" 
                             :class="sidebarCollapsed ? '' : 'mr-3'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">New Scan</span>
                    </a>

                    <a href="{{ route('codesnoutr.results') }}" 
                       :title="sidebarCollapsed ? 'Results' : ''"
                       class="{{ request()->routeIs('codesnoutr.results*') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }} group flex items-center py-2 text-sm font-medium rounded-md transition-all duration-300"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('codesnoutr.results*') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" 
                             :class="sidebarCollapsed ? '' : 'mr-3'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Results</span>
                    </a>

                    @if($pageType === 'settings')
                    <!-- Settings with Sub-menu -->
                    <div x-data="{ settingsOpen: {{ request()->routeIs('codesnoutr.settings*') ? 'true' : 'false' }} }" x-show="!sidebarCollapsed">
                        <button @click="settingsOpen = !settingsOpen" 
                                class="{{ request()->routeIs('codesnoutr.settings*') 
                                    ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' 
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }} group w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-all duration-300">
                            <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('codesnoutr.settings*') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="flex-1 text-left whitespace-nowrap">Settings</span>
                            <svg class="ml-3 h-4 w-4 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-90': settingsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        
                        <!-- Settings Sub-menu -->
                        <div x-show="settingsOpen" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="ml-6 mt-1 space-y-1">
                            @foreach($navigation as $item)
                            @php
                                $isActive = ($item['active'] ?? false) || $activeSection === ($item['section'] ?? '');
                                $linkClasses = $isActive 
                                    ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-200'
                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white';
                            @endphp
                            <a href="{{ $item['route'] ?? '#' }}" 
                               class="{{ $linkClasses }} group flex items-center px-3 py-2 text-sm transition-all duration-300">
                                @if(isset($item['icon']))
                                <svg class="mr-3 h-4 w-4 {{ ($item['active'] ?? false) || $activeSection === ($item['section'] ?? '') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($item['icon'] === 'cog')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    @elseif($item['icon'] === 'shield')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    @elseif($item['icon'] === 'bell')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    @elseif($item['icon'] === 'user')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    @endif
                                </svg>
                                @endif
                                {{ $item['label'] }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <!-- Regular Settings Link -->
                    <a href="{{ route('codesnoutr.settings') }}"
                       :title="sidebarCollapsed ? 'Settings' : ''" 
                       class="{{ request()->routeIs('codesnoutr.settings*') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }} group flex items-center py-2 text-sm font-medium rounded-md transition-all duration-300"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('codesnoutr.settings*') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" 
                             :class="sidebarCollapsed ? '' : 'mr-3'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Settings</span>
                    </a>
                    @endif
                </nav>

                <!-- Bottom section with Links (hidden when collapsed) -->
                <div x-show="!sidebarCollapsed" class="flex-shrink-0 px-3 pb-4 space-y-3">
                    <!-- Repository and Support Links -->
                    <div class="px-3 space-y-2">
                        <!-- Repository Link -->
                        <a href="https://github.com/rafaelogic/codesnoutr" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center px-2 py-1.5 text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-all duration-300 group">
                            <svg class="w-4 h-4 mr-2 group-hover:text-gray-900 dark:group-hover:text-gray-200" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.374 0 0 5.373 0 12 0 17.302 3.438 21.8 8.207 23.387c.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                            </svg>
                            <span>Repository</span>
                            <svg class="w-3 h-3 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>

                        <!-- Support Link -->
                        <a href="https://www.paypal.com/paypalme/rafarafael" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center px-2 py-1.5 text-xs text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-md transition-all duration-300 group">
                            <svg class="w-4 h-4 mr-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                            <span>Support</span>
                            <svg class="w-3 h-3 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>

                </div>
            </div>
        </aside>

        <!-- Mobile sidebar overlay -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"></div>

        <!-- Mobile sidebar -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 lg:hidden">
            <div class="flex flex-col h-full">
                <!-- Mobile sidebar content (same as desktop) -->
                <div class="flex items-center flex-shrink-0 px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                    <a href="{{ route('codesnoutr.dashboard') }}" class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            width="32" height="32" viewBox="0 0 128 128" role="img">
                        <circle cx="64" cy="64" r="60" fill="#F7FBFF"/>
                        <path d="M34 48 L20 64 L34 80" fill="none" stroke="#2B6CB0" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M94 48 L108 64 L94 80" fill="none" stroke="#2B6CB0" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="36" y="46" width="56" height="36" rx="18" ry="18" fill="#F6AD55" stroke="#DD6B20" stroke-width="2"/>
                        <ellipse cx="52" cy="64" rx="5.5" ry="7" fill="#C05621"/>
                        <ellipse cx="76" cy="64" rx="5.5" ry="7" fill="#C05621"/>
                        </svg>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">CodeSnoutr</span>
                    </a>
                </div>
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    <!-- Main Navigation Items -->
                    <a href="{{ route('codesnoutr.dashboard') }}" 
                       class="{{ request()->routeIs('codesnoutr.dashboard') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 border-indigo-400' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white border-transparent' }} group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-all duration-300">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('codesnoutr.dashboard') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('codesnoutr.scan') }}" 
                       class="{{ request()->routeIs('codesnoutr.scan') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 border-indigo-400' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white border-transparent' }} group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-all duration-300">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('codesnoutr.scan') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        New Scan
                    </a>

                    <a href="{{ route('codesnoutr.results') }}" 
                       class="{{ request()->routeIs('codesnoutr.results*') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 border-indigo-400' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white border-transparent' }} group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-all duration-300">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('codesnoutr.results*') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Results
                    </a>

                    <a href="{{ route('codesnoutr.settings') }}" 
                       class="{{ request()->routeIs('codesnoutr.settings*') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 border-indigo-400' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white border-transparent' }} group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-all duration-300">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('codesnoutr.settings*') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Settings
                    </a>

                    <!-- Spacer -->
                    <div class="pt-4">
                        <!-- Repository and Support Links -->
                        <div class="space-y-2">
                            <!-- Repository Link -->
                            <a href="https://github.com/rafaelogic/codesnoutr" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="flex items-center px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-all duration-300 group">
                                <svg class="w-5 h-5 mr-3 group-hover:text-gray-900 dark:group-hover:text-gray-200" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C5.374 0 0 5.373 0 12 0 17.302 3.438 21.8 8.207 23.387c.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                                </svg>
                                <span>Repository</span>
                                <svg class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>

                            <!-- Support Link -->
                            <a href="https://www.paypal.com/paypalme/rafarafael" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="flex items-center px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-md transition-all duration-300 group">
                                <svg class="w-5 h-5 mr-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                                <span>Support</span>
                                <svg class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        </div>

                        <!-- Separator -->
                        <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>

                        <!-- Dark Mode Toggle -->
                        <div class="flex items-center justify-between px-3 py-2 text-sm text-gray-600 dark:text-gray-400">
                            <span>Dark Mode</span>
                            <button @click="darkMode = !darkMode" 
                                    class="relative inline-flex justify-center items-center h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-all duration-300 ease-in-out"
                                    :class="darkMode ? 'bg-indigo-600 dark:bg-indigo-500' : 'bg-gray-200 dark:bg-gray-600'">
                                <span class="sr-only">Toggle dark mode</span>
                                <span class="pointer-events-none relative inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                      :class="darkMode ? 'translate-x-4' : 'translate-x-0'">
                                    <!-- Sun icon for light mode -->
                                    <svg x-show="!darkMode" class="absolute inset-0.5 h-3 w-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                                    </svg>
                                    <!-- Moon icon for dark mode -->
                                    <svg x-show="darkMode" class="absolute inset-0.5 h-3 w-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        @endif

        <!-- Main content area -->
        <div class="flex flex-col w-full transition-all duration-300"
             @if($showSidebar)
             x-bind:style="'margin-left: ' + (sidebarCollapsed ? '4rem' : '16rem') + ' !important'"
             @endif
             style="@if(!$showSidebar)margin-left: 0 !important;@endif">
            
            <!-- Stats Section for Dashboard -->
            @if($pageType === 'dashboard' && !empty($stats))
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($stats as $stat)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 transition-colors duration-300">
                            <div class="flex items-center">
                                @if(isset($stat['icon']))
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
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

            <div class="flex flex-col w-full">
                <!-- Main content -->
                <main class="flex-1 overflow-y-auto">
                    <div class="px-4 sm:px-6 lg:px-8 py-6 pt-8">
                        {{ $slot }}
                    </div>
                </main>

                <!-- Footer -->
                <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 transition-colors duration-300 mt-auto">
                    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                        <div class="flex flex-col items-center text-center text-sm text-gray-500 dark:text-gray-400">
                            <p>&copy; {{ date('Y') }} CodeSnoutr. All rights reserved.</p>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- Dark Mode Enhancement Script -->
    <script>
        // Initialize dark mode immediately to prevent flash
        (function() {
            const isDark = localStorage.getItem('codesnoutr-theme') === 'dark' || 
                          (!localStorage.getItem('codesnoutr-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            }
            
            // Listen for system theme changes
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', (e) => {
                // Only auto-switch if no manual preference is saved
                if (!localStorage.getItem('codesnoutr-theme')) {
                    document.documentElement.classList.toggle('dark', e.matches);
                    
                    // Update Alpine.js data if available
                    if (window.Alpine && window.Alpine.data) {
                        window.dispatchEvent(new CustomEvent('theme-updated', {
                            detail: { darkMode: e.matches }
                        }));
                    }
                }
            });
        })();
    </script>

    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- CodeSnoutr Livewire & Theme Management -->
    <script>
        // Global dark mode management
        window.CodeSnoutrTheme = {
            init() {
                // Listen for Livewire theme events
                document.addEventListener('livewire:initialized', () => {
                    Livewire.on('theme-changed', (data) => {
                        document.documentElement.classList.toggle('dark', data.darkMode);
                        localStorage.setItem('codesnoutr-theme', data.darkMode ? 'dark' : 'light');
                        
                        // Update Alpine.js if it exists
                        if (window.Alpine && window.Alpine.store) {
                            window.dispatchEvent(new CustomEvent('theme-sync', {
                                detail: { darkMode: data.darkMode }
                            }));
                        }
                    });
                });
                
                // Apply saved theme immediately
                const savedTheme = localStorage.getItem('codesnoutr-theme');
                const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = savedTheme === 'dark' || (!savedTheme && systemDark);
                
                document.documentElement.classList.toggle('dark', isDark);
            }
        };
        
        // Initialize theme management
        CodeSnoutrTheme.init();

        // Initialize when Livewire is ready
        document.addEventListener('livewire:init', function () {
            // Livewire initialized
        });
    </script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
