@props([
    'brandName' => 'CodeSnoutr',
    'brandHref' => '/',
    'items' => [],
    'currentRoute' => '',
    'user' => null,
    'dark' => false
])

@php
    $bgClass = $dark ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200';
    $textClass = $dark ? 'text-gray-100' : 'text-gray-900';
    $linkClass = $dark ? 'text-gray-300 hover:text-white' : 'text-gray-600 hover:text-gray-900';
    $activeLinkClass = $dark ? 'text-white bg-gray-800' : 'text-blue-600 bg-blue-50';
@endphp

<nav {{ $attributes->merge(['class' => "border-b $bgClass"]) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Brand -->
            <div class="flex items-center">
                <a href="{{ $brandHref }}" class="flex items-center space-x-2">
                    <x-atoms.icon name="clipboard" size="lg" :color="$dark ? 'primary' : 'primary'" />
                    <span class="text-xl font-bold {{ $textClass }}">{{ $brandName }}</span>
                </a>
            </div>
            
            <!-- Navigation Items -->
            @if(!empty($items))
                <div class="hidden md:flex items-center space-x-8">
                    @foreach($items as $item)
                        @php
                            $isActive = $currentRoute === ($item['route'] ?? '');
                            $itemClass = $isActive ? $activeLinkClass : $linkClass;
                        @endphp
                        
                        <a 
                            href="{{ $item['url'] ?? '#' }}" 
                            class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ $itemClass }}"
                        >
                            @if(isset($item['icon']))
                                <x-atoms.icon :name="$item['icon']" size="sm" class="mr-2" />
                            @endif
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
            
            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                @if($user)
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            class="flex items-center space-x-3 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <span class="{{ $textClass }}">{{ $user['name'] ?? 'User' }}</span>
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <x-atoms.icon name="users" size="sm" color="secondary" />
                            </div>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div 
                            x-show="open" 
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                        >
                            <div class="py-1">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-atoms.icon name="cog" size="sm" class="mr-3 inline" />
                                    Settings
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    Sign out
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <x-atoms.button variant="primary" size="sm" href="/login">
                        Sign In
                    </x-atoms.button>
                @endif
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button 
                        type="button" 
                        class="inline-flex items-center justify-center p-2 rounded-md {{ $linkClass }} hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
                        x-data="{ open: false }"
                        @click="open = !open"
                    >
                        <span class="sr-only">Open main menu</span>
                        <x-atoms.icon name="menu" size="md" />
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Navigation -->
    <div class="md:hidden" x-data="{ open: false }" x-show="open">
        <div class="px-2 pt-2 pb-3 space-y-1 {{ $dark ? 'bg-gray-800' : 'bg-gray-50' }}">
            @foreach($items as $item)
                @php
                    $isActive = $currentRoute === ($item['route'] ?? '');
                    $mobileItemClass = $isActive 
                        ? ($dark ? 'bg-gray-900 text-white' : 'bg-blue-50 text-blue-600')
                        : ($dark ? 'text-gray-300 hover:bg-gray-700 hover:text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900');
                @endphp
                
                <a 
                    href="{{ $item['url'] ?? '#' }}" 
                    class="block px-3 py-2 rounded-md text-base font-medium {{ $mobileItemClass }}"
                >
                    @if(isset($item['icon']))
                        <x-atoms.icon :name="$item['icon']" size="sm" class="mr-3 inline" />
                    @endif
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</nav>
