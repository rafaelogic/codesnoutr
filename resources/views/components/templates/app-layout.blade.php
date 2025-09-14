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
          darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
          sidebarOpen: false
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
    @php
        $manifestPath = public_path('vendor/codesnoutr/build/manifest.json');
        $hasBuiltAssets = file_exists($manifestPath);
        $manifest = $hasBuiltAssets ? json_decode(file_get_contents($manifestPath), true) : [];
        
        $cssFile = null;
        $jsFile = null;
        
        if ($hasBuiltAssets && $manifest) {
            foreach ($manifest as $file => $details) {
                if (str_ends_with($file, '.css')) {
                    $cssFile = 'vendor/codesnoutr/build/' . $details['file'];
                } elseif (str_ends_with($file, '.js')) {
                    $jsFile = 'vendor/codesnoutr/build/' . $details['file'];
                }
            }
        }
    @endphp
    
    @if($hasBuiltAssets && $cssFile && $jsFile)
        <!-- Use built assets from package -->
        <link rel="stylesheet" href="{{ asset($cssFile) }}">
        <script src="{{ asset($jsFile) }}" defer></script>
    @elseif(file_exists(public_path('build/manifest.json')))
        <!-- Use main app Vite assets if available -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
<body class="bg-gray-50 dark:bg-gray-900 h-screen overflow-hidden font-sans antialiased"
      x-bind:class="{ 'dark': darkMode }">
    
    <div class="flex h-screen">
        @if($showSidebar)
        <!-- Sidebar -->
        <div class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0">
            <!-- Sidebar component -->
            <div class="flex flex-col flex-grow bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 pt-5 pb-4 overflow-y-auto transition-colors duration-300">
                <!-- Logo -->
                <div class="flex flex-col items-center justify-center mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg"
                            width="48" height="48" viewBox="0 0 128 128" role="img" aria-labelledby="title desc">
                        <title id="title">Code Snoutr icon</title>
                        <desc id="desc">Stylized pig snout with two code chevrons</desc>

                        <!-- Background circle (subtle) -->
                        <circle cx="64" cy="64" r="60" fill="#F7FBFF"/>

                        <!-- Left chevron -->
                        <path d="M34 48 L20 64 L34 80" fill="none" stroke="#2B6CB0" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Right chevron -->
                        <path d="M94 48 L108 64 L94 80" fill="none" stroke="#2B6CB0" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Snout base (rounded capsule) -->
                        <rect x="36" y="46" width="56" height="36" rx="18" ry="18" fill="#F6AD55" stroke="#DD6B20" stroke-width="2"/>

                        <!-- Nostril left -->
                        <ellipse cx="52" cy="64" rx="5.5" ry="7" fill="#C05621"/>

                        <!-- Nostril right -->
                        <ellipse cx="76" cy="64" rx="5.5" ry="7" fill="#C05621"/>

                        <!-- Small highlight on snout -->
                        <ellipse cx="62" cy="54" rx="10" ry="4" fill="#FFD8A8" opacity="0.6"/>

                        <!-- Optional subtle drop shadow under snout -->
                        <ellipse cx="64" cy="86" rx="26" ry="6" fill="#000" opacity="0.06"/>

                    </svg>
                    <a href="{{ route('codesnoutr.dashboard') }}" class="flex items-center">
                        <span class="text-lg font-bold text-gray-900 dark:text-white">CodeSnoutr</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-3 space-y-1">
                    <!-- Main Navigation Items -->
                    <a href="{{ route('codesnoutr.dashboard') }}" 
                       class="{{ request()->routeIs('codesnoutr.dashboard') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 border-indigo-400' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white border-transparent' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('codesnoutr.dashboard') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('codesnoutr.scan') }}" 
                       class="{{ request()->routeIs('codesnoutr.scan') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 border-indigo-400' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white border-transparent' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('codesnoutr.scan') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        New Scan
                    </a>

                    <a href="{{ route('codesnoutr.results') }}" 
                       class="{{ request()->routeIs('codesnoutr.results*') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 border-indigo-400' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white border-transparent' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('codesnoutr.results*') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Results
                    </a>

                    @if($pageType === 'settings')
                    <!-- Settings with Sub-menu -->
                    <div x-data="{ settingsOpen: {{ request()->routeIs('codesnoutr.settings*') ? 'true' : 'false' }} }">
                        <button @click="settingsOpen = !settingsOpen" 
                                class="{{ request()->routeIs('codesnoutr.settings*') 
                                    ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 border-indigo-400' 
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white border-transparent' }} group w-full flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200">
                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('codesnoutr.settings*') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="flex-1 text-left">Settings</span>
                            <svg class="ml-3 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-90': settingsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <a href="{{ $item['route'] ?? '#' }}" 
                               class="@if(($item['active'] ?? false) || $activeSection === ($item['section'] ?? '')) bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-200 @else text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white @endif group flex items-center px-3 py-2 text-sm rounded-md transition-colors duration-200">
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
                       class="{{ request()->routeIs('codesnoutr.settings*') 
                           ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 border-indigo-400' 
                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white border-transparent' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('codesnoutr.settings*') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Settings
                    </a>
                    @endif
                </nav>

                <!-- Bottom section with Dark Mode Toggle -->
                <div class="flex-shrink-0 px-3 pb-4">
                    <div class="flex items-center justify-between px-3 py-2 text-sm text-gray-600 dark:text-gray-400">
                        <span>Dark Mode</span>
                        <button @click="darkMode = !darkMode" 
                                class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                :class="darkMode ? 'bg-indigo-600' : 'bg-gray-200'">
                            <span class="sr-only">Toggle dark mode</span>
                            <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                  :class="darkMode ? 'translate-x-4' : 'translate-x-0'">
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

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
                    <!-- Same navigation as desktop -->
                </nav>
            </div>
        </div>
        @endif

        <!-- Main content area -->
        <div class="flex-1 flex flex-col {{ $showSidebar ? 'lg:ml-64' : '' }} min-h-0">
            <!-- Top bar with mobile menu button and page header -->
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
                <div class="flex items-center justify-between px-4 py-4">
                    @if($showSidebar)
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = true" 
                            class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    @endif

                    <!-- Page title -->
                    <div class="flex-1 {{ $showSidebar ? 'lg:ml-0' : '' }}">
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
                        @if($subtitle)
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
                        @endif
                    </div>

                    <!-- Page actions -->
                    @if($actions)
                        <div class="flex space-x-3">
                            {{ $actions }}
                        </div>
                    @endif
                </div>
            </div>

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

            <!-- Main content -->
            <main class="flex-1 overflow-y-auto">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 transition-colors duration-300 {{ $showSidebar ? 'lg:ml-64' : '' }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
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

    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- CodeSnoutr Livewire Debugging -->
    <script>
        // Debug functions - Global scope
        window.testLivewireDirect = function() {
            try {
                console.log('🔧 Testing direct Livewire method calls...');
                
                if (typeof window.Livewire === 'undefined') {
                    console.error('❌ Livewire not available');
                    return false;
                }
                
                const components = window.Livewire.all();
                console.log('Found components:', components.length);
                
                if (components.length === 0) {
                    console.error('❌ No Livewire components found');
                    return false;
                }
                
                // List all components
                components.forEach((comp, i) => {
                    console.log(`Component ${i}: ${comp.fingerprint?.name || 'unnamed'} (ID: ${comp.id})`);
                });
                
                const scanResultsComponent = components.find(c => 
                    c.fingerprint && c.fingerprint.name === 'codesnoutr-scan-results'
                );
                
                if (!scanResultsComponent) {
                    console.error('❌ ScanResults component not found');
                    return false;
                }
                
                console.log('✅ Found ScanResults component:', scanResultsComponent.id);
                
                // Test simpleTest method
                try {
                    console.log('📞 Calling simpleTest...');
                    scanResultsComponent.call('simpleTest');
                    console.log('✅ simpleTest call initiated - check Laravel logs');
                } catch (error) {
                    console.error('❌ simpleTest failed:', error);
                }
                
                return true;
                
            } catch (error) {
                console.error('❌ Critical error in testLivewireDirect:', error);
                return false;
            }
        };

        window.checkWireClicks = function() {
            try {
                const wireButtons = document.querySelectorAll('[wire\\:click]');
                console.log('🔘 Found wire:click buttons:', wireButtons.length);
                
                wireButtons.forEach((btn, i) => {
                    const wireClick = btn.getAttribute('wire:click');
                    const wireId = btn.closest('[wire\\:id]')?.getAttribute('wire:id');
                    
                    console.log(`Button ${i}: ${wireClick} (Component: ${wireId})`);
                });
                
                return wireButtons.length;
                
            } catch (error) {
                console.error('❌ Error in checkWireClicks:', error);
                return 0;
            }
        };

        // Initialize when Livewire is ready
        document.addEventListener('livewire:init', function () {
            console.log('🚀 CodeSnoutr: Livewire initialized successfully');
            
            // Wait a bit for components to be ready, then run diagnostics
            setTimeout(() => {
                console.log('🔍 Running initial diagnostics...');
                
                const buttonCount = checkWireClicks();
                console.log(`Found ${buttonCount} wire:click buttons`);
                
                // Listen for Livewire events
                document.addEventListener('livewire:before', function(event) {
                    console.log('📡 Livewire request:', event.detail.component.fingerprint.name, '->', event.detail.message.method);
                });
                
                document.addEventListener('livewire:after', function(event) {
                    console.log('📡 Livewire completed:', event.detail.component.fingerprint.name);
                });
                
                document.addEventListener('livewire:error', function(event) {
                    console.error('❌ Livewire error:', event.detail);
                });
                
            }, 1000);
        });
    </script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
