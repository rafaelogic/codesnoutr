<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CodeSnoutr - Code Analysis Dashboard')</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        mono: ['Fira Code', 'Monaco', 'Menlo', 'monospace'],
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- CodeSnoutr Custom CSS -->
    @if(file_exists(public_path('css/vendor/codesnoutr/codesnoutr.css')))
        <link rel="stylesheet" href="{{ asset('css/vendor/codesnoutr/codesnoutr.css') }}">
    @endif
    
    <!-- CodeSnoutr Styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        .transition-theme {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .dark .gradient-bg {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dark .glass-effect {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .code-block {
            font-family: 'Fira Code', 'Monaco', 'Menlo', monospace;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        /* Severity badges */
        .severity-critical { 
            background-color: rgb(254 226 226); 
            color: rgb(153 27 27); 
        }
        .dark .severity-critical { 
            background-color: rgb(127 29 29); 
            color: rgb(252 165 165); 
        }
        
        .severity-high { 
            background-color: rgb(255 237 213); 
            color: rgb(154 52 18); 
        }
        .dark .severity-high { 
            background-color: rgb(124 45 18); 
            color: rgb(253 186 116); 
        }
        
        .severity-medium { 
            background-color: rgb(254 249 195); 
            color: rgb(133 77 14); 
        }
        .dark .severity-medium { 
            background-color: rgb(113 63 18); 
            color: rgb(250 204 21); 
        }
        
        .severity-low { 
            background-color: rgb(219 234 254); 
            color: rgb(30 64 175); 
        }
        .dark .severity-low { 
            background-color: rgb(30 58 138); 
            color: rgb(147 197 253); 
        }
        
        .severity-info { 
            background-color: rgb(243 244 246); 
            color: rgb(31 41 55); 
        }
        .dark .severity-info { 
            background-color: rgb(55 65 81); 
            color: rgb(229 231 235); 
        }

        /* Progress bars */
        .progress-bar {
            background: #e5e7eb;
            border-radius: 0.25rem;
            overflow: hidden;
            height: 0.5rem;
        }
        
        .dark .progress-bar {
            background: #374151;
        }
        
        .progress-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        
        .progress-fill.severity-critical { background: #ef4444; }
        .progress-fill.severity-high { background: #f97316; }
        .progress-fill.severity-medium { background: #eab308; }
        .progress-fill.severity-low { background: #3b82f6; }
        .progress-fill.severity-info { background: #6b7280; }

        /* Cards and panels */
        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }
        
        .dark .card {
            background: #1f2937;
            border-color: #374151;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }

        /* Forms */
        .form-input {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: border-color 0.2s;
            background: white;
        }
        
        .dark .form-input {
            background: #1f2937;
            border-color: #374151;
            color: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .dark .table th,
        .dark .table td {
            border-color: #374151;
        }
        
        .table th {
            background: #f9fafb;
            font-weight: 600;
            font-size: 0.875rem;
            color: #374151;
        }
        
        .dark .table th {
            background: #1f2937;
            color: #d1d5db;
        }

        /* Notifications */
        .notification {
            padding: 1rem;
            border-radius: 0.375rem;
            margin: 0.5rem 0;
            border-left: 4px solid;
        }
        
        .notification.info {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1e40af;
        }
        
        .notification.success {
            background: #f0fdf4;
            border-color: #22c55e;
            color: #15803d;
        }
        
        .notification.warning {
            background: #fffbeb;
            border-color: #f59e0b;
            color: #d97706;
        }
        
        .notification.error {
            background: #fef2f2;
            border-color: #ef4444;
            color: #dc2626;
        }
        
        .dark .notification.info {
            background: rgba(37, 99, 235, 0.1);
            color: #93c5fd;
        }
        
        .dark .notification.success {
            background: rgba(34, 197, 94, 0.1);
            color: #86efac;
        }
        
        .dark .notification.warning {
            background: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
        }
        
        .dark .notification.error {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
        }

        /* Print styles */
        @media print {
            .no-print { display: none !important; }
            .print-break { page-break-before: always; }
            .print-avoid-break { page-break-inside: avoid; }
        }
    </style>
    
    @stack('styles')
    @livewireStyles
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-theme">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-theme">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and Main Navigation -->
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center">
                            <div class="h-8 w-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="ml-3 text-xl font-bold text-gray-900 dark:text-white">CodeSnoutr</span>
                        </div>
                        
                        <!-- Desktop Navigation -->
                        <div class="hidden md:ml-10 md:flex md:space-x-8">
                            <a href="{{ route('codesnoutr.dashboard') }}" 
                               class="@if(request()->routeIs('codesnoutr.dashboard')) border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif dark:text-gray-300 dark:hover:text-white border-b-2 px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                                Dashboard
                            </a>
                            <a href="{{ route('codesnoutr.scan') }}" 
                               class="@if(request()->routeIs('codesnoutr.scan*')) border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif dark:text-gray-300 dark:hover:text-white border-b-2 px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                                Scan
                            </a>
                            <a href="{{ route('codesnoutr.results') }}" 
                               class="@if(request()->routeIs('codesnoutr.results*')) border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif dark:text-gray-300 dark:hover:text-white border-b-2 px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                                Results
                            </a>
                            <a href="{{ route('codesnoutr.settings') }}" 
                               class="@if(request()->routeIs('codesnoutr.settings*')) border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif dark:text-gray-300 dark:hover:text-white border-b-2 px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                                Settings
                            </a>
                        </div>
                    </div>

                    <!-- Right Side Navigation -->
                    <div class="flex items-center space-x-4">
                        <!-- Dark Mode Toggle -->
                        @livewire('codesnoutr-dark-mode-toggle')
                        
                        <!-- Quick Stats -->
                        <div class="hidden lg:flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex items-center space-x-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span>{{ \Rafaelogic\CodeSnoutr\Models\Scan::count() }} Scans</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <span>{{ \Rafaelogic\CodeSnoutr\Models\Issue::count() }} Issues</span>
                            </div>
                        </div>

                        <!-- Mobile menu button -->
                        <div class="md:hidden">
                            <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="bg-white dark:bg-gray-800 rounded-md p-2 inline-flex items-center justify-center text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                <span class="sr-only">Open main menu</span>
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                    <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div x-data="{ mobileMenuOpen: false }" x-show="mobileMenuOpen" x-cloak class="md:hidden">
                <div class="pt-2 pb-3 space-y-1 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('codesnoutr.dashboard') }}" class="@if(request()->routeIs('codesnoutr.dashboard')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 @endif dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Dashboard
                    </a>
                    <a href="{{ route('codesnoutr.scan') }}" class="@if(request()->routeIs('codesnoutr.scan*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 @endif dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Scan
                    </a>
                    <a href="{{ route('codesnoutr.results') }}" class="@if(request()->routeIs('codesnoutr.results*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 @endif dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Results
                    </a>
                    <a href="{{ route('codesnoutr.settings') }}" class="@if(request()->routeIs('codesnoutr.settings*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 @endif dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Settings
                    </a>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 transition-theme">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                    <div>
                        <span>CodeSnoutr v1.0</span>
                        <span class="mx-2">•</span>
                        <span>Code Analysis Tool</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span>Last scan: {{ \Rafaelogic\CodeSnoutr\Models\Scan::latest()->first()?->created_at?->diffForHumans() ?? 'Never' }}</span>
                        <div class="flex items-center space-x-1">
                            <div class="h-2 w-2 bg-green-400 rounded-full"></div>
                            <span>System Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Toast Notifications -->
    <div x-data="{ 
        notifications: [],
        addNotification(message, type = 'info') {
            const id = Date.now();
            this.notifications.push({ id, message, type });
            setTimeout(() => this.removeNotification(id), 5000);
        },
        removeNotification(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
    }" @notification.window="addNotification($event.detail.message, $event.detail.type)"
         class="fixed bottom-0 right-0 z-50 p-6 space-y-4">
        <template x-for="notification in notifications" :key="notification.id">
            <div class="bg-white dark:bg-gray-800 border-l-4 p-4 rounded-md shadow-lg max-w-md"
                 :class="{
                     'border-blue-400': notification.type === 'info',
                     'border-green-400': notification.type === 'success',
                     'border-yellow-400': notification.type === 'warning',
                     'border-red-400': notification.type === 'error'
                 }"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg x-show="notification.type === 'info'" class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="notification.type === 'success'" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="notification.type === 'warning'" class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="notification.type === 'error'" class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-700 dark:text-gray-300" x-text="notification.message"></p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="removeNotification(notification.id)" class="inline-flex text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @stack('scripts')
    @livewireScripts
    
    <script>
        // Global JavaScript for CodeSnoutr
        window.CodeSnoutr = {
            // Theme management
            initTheme() {
                const savedTheme = localStorage.getItem('codesnoutr-theme');
                if (savedTheme) {
                    document.documentElement.classList.toggle('dark', savedTheme === 'dark');
                }
            },
            
            // Notification helper
            notify(message, type = 'info') {
                window.dispatchEvent(new CustomEvent('notification', {
                    detail: { message, type }
                }));
            },
            
            // Download file helper
            downloadFile(content, filename, contentType = 'text/plain') {
                const blob = new Blob([content], { type: contentType });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            }
        };

        // Initialize theme on page load
        document.addEventListener('DOMContentLoaded', () => {
            CodeSnoutr.initTheme();
        });

        // Listen for Livewire events
        document.addEventListener('livewire:load', function () {
            // Theme storage updates
            Livewire.on('update-theme-storage', (data) => {
                localStorage.setItem('codesnoutr-theme', data.darkMode ? 'dark' : 'light');
            });

            // File downloads
            Livewire.on('download-file', (data) => {
                CodeSnoutr.downloadFile(data.content, data.filename, data.contentType);
            });

            // Notifications
            Livewire.on('scan-completed', () => {
                CodeSnoutr.notify('Scan completed successfully!', 'success');
            });

            Livewire.on('scan-cancelled', () => {
                CodeSnoutr.notify('Scan was cancelled', 'warning');
            });

            Livewire.on('settings-saved', () => {
                CodeSnoutr.notify('Settings saved successfully!', 'success');
            });

            Livewire.on('cache-cleared', () => {
                CodeSnoutr.notify('Cache cleared successfully!', 'success');
            });

            Livewire.on('maintenance-completed', () => {
                CodeSnoutr.notify('Maintenance completed successfully!', 'success');
            });
        });
    </script>
</body>
</html>
