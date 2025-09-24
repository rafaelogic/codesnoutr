@props([
    'navigation' => [],
    'currentRoute' => '',
    'collapsible' => true,
    'collapsed' => false,
    'brand' => null,
    'user' => null,
    'footer' => null
])

<div 
    x-data="{ collapsed: @js($collapsed) }"
    :class="collapsed ? 'w-16' : 'w-64'"
    class="flex flex-col h-full bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300"
    {{ $attributes }}
>
    <!-- Brand/Header -->
    @if($brand || $collapsible)
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            @if($brand)
                <div :class="collapsed ? 'hidden' : 'block'">
                    {{ $brand }}
                </div>
            @endif
            
            @if($collapsible)
                <button 
                    @click="collapsed = !collapsed"
                    class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <x-atoms.icon 
                        x-show="!collapsed" 
                        name="chevron-left" 
                        size="md" 
                    />
                    <x-atoms.icon 
                        x-show="collapsed" 
                        name="chevron-right" 
                        size="md" 
                    />
                </button>
            @endif
        </div>
    @endif
    
    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        @foreach($navigation as $item)
            @if(isset($item['type']) && $item['type'] === 'divider')
                <hr class="my-3 border-gray-200">
                @if(isset($item['label']))
                    <div :class="collapsed ? 'hidden' : 'block'" class="px-3 py-2">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ $item['label'] }}
                        </h3>
                    </div>
                @endif
            @elseif(isset($item['children']))
                <!-- Group with children -->
                <div x-data="{ open: {{ $item['open'] ?? 'false' }} }">
                    <button
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 group focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <div class="flex items-center">
                            @if(isset($item['icon']))
                                <x-atoms.icon 
                                    :name="$item['icon']" 
                                    size="md" 
                                    class="mr-3 flex-shrink-0"
                                />
                            @endif
                            <span :class="collapsed ? 'hidden' : 'block'">{{ $item['label'] }}</span>
                        </div>
                        <x-atoms.icon 
                            name="chevron-down" 
                            size="sm" 
                            :class="collapsed ? 'hidden' : 'block'"
                            x-bind:class="open ? 'rotate-180' : ''"
                            class="transform transition-transform duration-200"
                        />
                    </button>
                    
                    <div 
                        x-show="open && !collapsed" 
                        x-collapse
                        class="ml-6 mt-1 space-y-1"
                    >
                        @foreach($item['children'] as $child)
                            @php
                                $isChildActive = $currentRoute === ($child['route'] ?? '');
                                $childClasses = $isChildActive 
                                    ? 'bg-blue-50 dark:bg-blue-900/20 border-r-2 border-blue-600 text-blue-600 dark:text-blue-400'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700';
                            @endphp
                            
                            <a 
                                href="{{ $child['url'] ?? '#' }}"
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $childClasses }} group"
                            >
                                @if(isset($child['icon']))
                                    <x-atoms.icon 
                                        :name="$child['icon']" 
                                        size="sm" 
                                        class="mr-3 flex-shrink-0"
                                    />
                                @endif
                                {{ $child['label'] }}
                                
                                @if(isset($child['badge']))
                                    <x-atoms.badge 
                                        variant="secondary" 
                                        size="sm" 
                                        class="ml-auto"
                                    >
                                        {{ $child['badge'] }}
                                    </x-atoms.badge>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Regular navigation item -->
                @php
                    $isActive = $currentRoute === ($item['route'] ?? '');
                    $itemClasses = $isActive 
                        ? 'bg-blue-50 dark:bg-blue-900/20 border-r-2 border-blue-600 text-blue-600 dark:text-blue-400'
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700';
                @endphp
                
                <a 
                    href="{{ $item['url'] ?? '#' }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $itemClasses }} group"
                    @if(isset($item['tooltip'])) title="{{ $item['tooltip'] }}" @endif
                >
                    @if(isset($item['icon']))
                        <x-atoms.icon 
                            :name="$item['icon']" 
                            size="md" 
                            class="mr-3 flex-shrink-0"
                        />
                    @endif
                    
                    <span :class="collapsed ? 'hidden' : 'block'" class="truncate">
                        {{ $item['label'] }}
                    </span>
                    
                    @if(isset($item['badge']))
                        <x-atoms.badge 
                            variant="secondary" 
                            size="sm" 
                            :class="collapsed ? 'hidden' : 'ml-auto'"
                        >
                            {{ $item['badge'] }}
                        </x-atoms.badge>
                    @endif
                </a>
            @endif
        @endforeach
    </nav>
    
    <!-- User Section -->
    @if($user)
        <div class="border-t border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    @if(isset($user['avatar']))
                        <img class="h-8 w-8 rounded-full" src="{{ $user['avatar'] }}" alt="{{ $user['name'] }}">
                    @else
                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                            <x-atoms.icon name="users" size="sm" color="secondary" />
                        </div>
                    @endif
                </div>
                <div :class="collapsed ? 'hidden' : 'ml-3'">
                    <p class="text-sm font-medium text-gray-700">{{ $user['name'] }}</p>
                    @if(isset($user['email']))
                        <p class="text-xs text-gray-500">{{ $user['email'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
    
    <!-- Footer -->
    @if($footer)
        <div class="border-t border-gray-200 p-4">
            <div :class="collapsed ? 'hidden' : 'block'">
                {{ $footer }}
            </div>
        </div>
    @endif
</div>
