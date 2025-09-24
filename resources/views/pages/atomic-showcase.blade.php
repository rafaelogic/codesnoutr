@props(['title' => 'Atomic Design System'])

<x-templates.app-layout :title="$title">
    <div class="container-lg space-y-8">
        <!-- Introduction -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ $title }}
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                A comprehensive atomic design system built with utility-first CSS principles for maximum reusability, consistency, and maintainability.
            </p>
        </div>

        <!-- Design Tokens -->
        <section class="surface p-8 mb-8">
            <h2 class="text-2xl font-semibold mb-6">Design Tokens</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Colors -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Color Palette</h3>
                    <div class="space-y-3">
                        @foreach(['blue', 'green', 'red', 'yellow', 'gray'] as $color)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded bg-{{ $color }}-500"></div>
                                <span class="text-sm font-mono">{{ $color }}-500</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Typography -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Typography Scale</h3>
                    <div class="space-y-2">
                        <div class="text-xs">Extra Small (12px)</div>
                        <div class="text-sm">Small (14px)</div>
                        <div class="text-base">Base (16px)</div>
                        <div class="text-lg">Large (18px)</div>
                        <div class="text-xl">Extra Large (20px)</div>
                    </div>
                </div>
                
                <!-- Spacing -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Spacing Scale</h3>
                    <div class="space-y-2">
                        @foreach([1, 2, 3, 4, 6, 8] as $space)
                            <div class="flex items-center space-x-3">
                                <div class="w-{{ $space }} h-4 bg-blue-500"></div>
                                <span class="text-sm font-mono">{{ $space * 0.25 }}rem</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- Atoms -->
        <section class="surface p-8 mb-8">
            <h2 class="text-2xl font-semibold mb-6">Atoms</h2>
            
            <!-- Buttons -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4">Buttons</h3>
                <div class="flex flex-wrap gap-4 mb-4">
                    <x-atoms.button variant="primary">Primary</x-atoms.button>
                    <x-atoms.button variant="secondary">Secondary</x-atoms.button>
                    <x-atoms.button variant="danger">Danger</x-atoms.button>
                    <x-atoms.button variant="success">Success</x-atoms.button>
                    <x-atoms.button variant="warning">Warning</x-atoms.button>
                    <x-atoms.button variant="ghost">Ghost</x-atoms.button>
                </div>
                
                <div class="flex flex-wrap gap-4 mb-4">
                    <x-atoms.button variant="outline-primary">Outline Primary</x-atoms.button>
                    <x-atoms.button variant="outline-secondary">Outline Secondary</x-atoms.button>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <x-atoms.button size="xs">Extra Small</x-atoms.button>
                    <x-atoms.button size="sm">Small</x-atoms.button>
                    <x-atoms.button size="md">Medium</x-atoms.button>
                    <x-atoms.button size="lg">Large</x-atoms.button>
                    <x-atoms.button size="xl">Extra Large</x-atoms.button>
                </div>
            </div>
            
            <!-- Badges -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4">Badges</h3>
                <div class="flex flex-wrap gap-4 mb-4">
                    <x-atoms.badge variant="primary">Primary</x-atoms.badge>
                    <x-atoms.badge variant="secondary">Secondary</x-atoms.badge>
                    <x-atoms.badge variant="success">Success</x-atoms.badge>
                    <x-atoms.badge variant="danger">Danger</x-atoms.badge>
                    <x-atoms.badge variant="warning">Warning</x-atoms.badge>
                    <x-atoms.badge variant="info">Info</x-atoms.badge>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <x-atoms.badge size="xs">Extra Small</x-atoms.badge>
                    <x-atoms.badge size="sm">Small</x-atoms.badge>
                    <x-atoms.badge size="md">Medium</x-atoms.badge>
                    <x-atoms.badge size="lg">Large</x-atoms.badge>
                </div>
            </div>
            
            <!-- Avatars -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4">Avatars</h3>
                <div class="flex items-center space-x-4 mb-4">
                    <x-atoms.avatar size="xs" initials="XS" />
                    <x-atoms.avatar size="sm" initials="SM" />
                    <x-atoms.avatar size="md" initials="MD" />
                    <x-atoms.avatar size="lg" initials="LG" />
                    <x-atoms.avatar size="xl" initials="XL" />
                    <x-atoms.avatar size="2xl" initials="2XL" />
                </div>
                
                <div class="flex items-center space-x-4">
                    <x-atoms.avatar initials="JD" status="online" />
                    <x-atoms.avatar initials="AB" status="offline" />
                    <x-atoms.avatar initials="CD" status="busy" />
                    <x-atoms.avatar initials="EF" status="away" />
                </div>
            </div>
            
            <!-- Form Inputs -->
            <div>
                <h3 class="text-lg font-medium mb-4">Form Inputs</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-molecules.form-field label="Default Input" required>
                            <x-atoms.input name="default" placeholder="Enter text..." />
                        </x-molecules.form-field>
                    </div>
                    
                    <div>
                        <x-molecules.form-field label="Error State" error="This field is required">
                            <x-atoms.input name="error" state="error" placeholder="Enter text..." />
                        </x-molecules.form-field>
                    </div>
                    
                    <div>
                        <x-molecules.form-field label="Success State" success="Valid input">
                            <x-atoms.input name="success" state="success" placeholder="Enter text..." />
                        </x-molecules.form-field>
                    </div>
                    
                    <div>
                        <x-molecules.form-field label="Disabled Input">
                            <x-atoms.input name="disabled" disabled placeholder="Disabled input..." />
                        </x-molecules.form-field>
                    </div>
                </div>
            </div>
        </section>

        <!-- Molecules -->
        <section class="surface p-8 mb-8">
            <h2 class="text-2xl font-semibold mb-6">Molecules</h2>
            
            <!-- Cards -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4">Cards</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-molecules.card title="Basic Card" description="A simple card component">
                        <p class="text-gray-600 dark:text-gray-400">This is the card content area.</p>
                    </x-molecules.card>
                    
                    <x-molecules.card title="Interactive Card" variant="interactive" hover="true" icon="star">
                        <p class="text-gray-600 dark:text-gray-400">This card has hover effects and is interactive.</p>
                    </x-molecules.card>
                    
                    <x-molecules.card title="Collapsible Card" collapsible="true">
                        <p class="text-gray-600 dark:text-gray-400">This card can be collapsed to save space.</p>
                    </x-molecules.card>
                </div>
            </div>
            
            <!-- Metric Cards -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4">Metric Cards</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <x-molecules.metric-card
                        title="Total Users"
                        value="1,234"
                        change="12"
                        change-type="increase"
                        icon="users"
                        color="blue"
                    />
                    
                    <x-molecules.metric-card
                        title="Revenue"
                        value="$45,678"
                        change="8"
                        change-type="increase"
                        icon="currency-dollar"
                        color="green"
                    />
                    
                    <x-molecules.metric-card
                        title="Bounce Rate"
                        value="23.4%"
                        change="5"
                        change-type="decrease"
                        icon="arrow-trending-down"
                        color="red"
                    />
                    
                    <x-molecules.metric-card
                        title="Conversion"
                        value="3.2%"
                        change="0"
                        change-type="neutral"
                        icon="chart-bar"
                        color="gray"
                    />
                </div>
            </div>
            
            <!-- Forms -->
            <div>
                <h3 class="text-lg font-medium mb-4">Forms</h3>
                <div class="max-w-2xl">
                    <x-molecules.form variant="card">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-molecules.form-field label="First Name" required>
                                <x-atoms.input name="first_name" placeholder="John" />
                            </x-molecules.form-field>
                            
                            <x-molecules.form-field label="Last Name" required>
                                <x-atoms.input name="last_name" placeholder="Doe" />
                            </x-molecules.form-field>
                        </div>
                        
                        <x-molecules.form-field label="Email Address" required help="We'll never share your email">
                            <x-atoms.input type="email" name="email" placeholder="john@example.com" />
                        </x-molecules.form-field>
                        
                        <x-molecules.form-field label="Message" optional>
                            <textarea 
                                name="message" 
                                rows="4" 
                                class="input input--default" 
                                placeholder="Your message..."
                            ></textarea>
                        </x-molecules.form-field>
                        
                        <div class="form-actions">
                            <x-atoms.button type="submit" variant="primary">Submit</x-atoms.button>
                            <x-atoms.button type="button" variant="ghost">Cancel</x-atoms.button>
                        </div>
                    </x-molecules.form>
                </div>
            </div>
        </section>

        <!-- Organisms -->
        <section class="surface p-8 mb-8">
            <h2 class="text-2xl font-semibold mb-6">Organisms</h2>
            
            <!-- Data Table -->
            <div>
                <h3 class="text-lg font-medium mb-4">Enhanced Data Table</h3>
                <x-organisms.data-table-enhanced
                    :headers="[
                        ['label' => 'Name', 'sortable' => true],
                        ['label' => 'Status', 'sortable' => true],
                        ['label' => 'Role', 'sortable' => false],
                        ['label' => 'Actions', 'sortable' => false]
                    ]"
                    :rows="[
                        [
                            'id' => 1,
                            'cells' => [
                                [
                                    'type' => 'user',
                                    'value' => 'John Doe',
                                    'description' => 'john@example.com'
                                ],
                                [
                                    'type' => 'badge',
                                    'value' => 'Active',
                                    'variant' => 'success'
                                ],
                                'Administrator',
                                [
                                    'type' => 'actions',
                                    'actions' => [
                                        ['label' => 'Edit', 'icon' => 'pencil', 'variant' => 'ghost'],
                                        ['label' => 'Delete', 'icon' => 'trash', 'variant' => 'ghost']
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 2,
                            'cells' => [
                                [
                                    'type' => 'user',
                                    'value' => 'Jane Smith',
                                    'description' => 'jane@example.com'
                                ],
                                [
                                    'type' => 'badge',
                                    'value' => 'Inactive',
                                    'variant' => 'secondary'
                                ],
                                'Editor',
                                [
                                    'type' => 'actions',
                                    'actions' => [
                                        ['label' => 'Edit', 'icon' => 'pencil', 'variant' => 'ghost'],
                                        ['label' => 'Delete', 'icon' => 'trash', 'variant' => 'ghost']
                                    ]
                                ]
                            ]
                        ]
                    ]"
                    searchable="true"
                    selectable="true"
                    paginated="true"
                />
            </div>
        </section>

        <!-- Utilities -->
        <section class="surface p-8">
            <h2 class="text-2xl font-semibold mb-6">Atomic Utilities</h2>
            
            <!-- Layout -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4">Layout Utilities</h3>
                <div class="space-y-4">
                    <div>
                        <h4 class="text-sm font-medium mb-2">Stack (Vertical Layout)</h4>
                        <div class="stack stack--md border border-gray-200 dark:border-gray-700 rounded p-4">
                            <div class="bg-blue-100 dark:bg-blue-900/20 p-2 rounded text-center">Item 1</div>
                            <div class="bg-blue-100 dark:bg-blue-900/20 p-2 rounded text-center">Item 2</div>
                            <div class="bg-blue-100 dark:bg-blue-900/20 p-2 rounded text-center">Item 3</div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium mb-2">Inline (Horizontal Layout)</h4>
                        <div class="inline inline--md border border-gray-200 dark:border-gray-700 rounded p-4">
                            <div class="bg-green-100 dark:bg-green-900/20 p-2 rounded text-center">Item 1</div>
                            <div class="bg-green-100 dark:bg-green-900/20 p-2 rounded text-center">Item 2</div>
                            <div class="bg-green-100 dark:bg-green-900/20 p-2 rounded text-center">Item 3</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Progress -->
            <div class="mb-8">
                <h3 class="text-lg font-medium mb-4">Progress Indicators</h3>
                <div class="space-y-4">
                    <div>
                        <div class="progress progress--md">
                            <div class="progress__bar" style="width: 75%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="progress progress--md">
                            <div class="progress__bar progress__bar--success" style="width: 90%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="progress progress--md">
                            <div class="progress__bar progress__bar--warning" style="width: 45%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Skeletons -->
            <div>
                <h3 class="text-lg font-medium mb-4">Loading Skeletons</h3>
                <div class="space-y-4">
                    <div class="skeleton skeleton--title"></div>
                    <div class="skeleton skeleton--text"></div>
                    <div class="skeleton skeleton--text w-3/4"></div>
                    <div class="flex items-center space-x-4">
                        <div class="skeleton skeleton--avatar"></div>
                        <div class="flex-1 space-y-2">
                            <div class="skeleton skeleton--text"></div>
                            <div class="skeleton skeleton--text w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>Built with atomic design principles for CodeSnoutr</p>
        </div>
    </div>
</x-templates.app-layout>