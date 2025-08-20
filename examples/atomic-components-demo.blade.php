@php
    $navigationItems = [
        [
            'label' => 'Dashboard',
            'url' => '/dashboard',
            'route' => 'dashboard',
            'icon' => 'chart-bar'
        ],
        [
            'label' => 'Scans',
            'url' => '/scans',
            'route' => 'scans',
            'icon' => 'search'
        ],
        [
            'label' => 'Settings',
            'url' => '/settings',
            'route' => 'settings',
            'icon' => 'cog'
        ]
    ];
    
    $dashboardStats = [
        [
            'title' => 'Total Scans',
            'value' => '1,234',
            'change' => '+12%',
            'changeType' => 'positive',
            'icon' => 'search',
            'href' => '/scans'
        ],
        [
            'title' => 'Issues Found',
            'value' => '89',
            'change' => '-5%',
            'changeType' => 'positive',
            'icon' => 'exclamation-circle'
        ],
        [
            'title' => 'Files Scanned',
            'value' => '2,456',
            'change' => '+18%',
            'changeType' => 'positive',
            'icon' => 'document-text'
        ],
        [
            'title' => 'Success Rate',
            'value' => '98.5%',
            'change' => '+0.5%',
            'changeType' => 'positive',
            'icon' => 'check-circle'
        ]
    ];
    
    $user = [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ];
@endphp

<x-templates.dashboard-layout 
    title="CodeSnoutr Dashboard"
    :navigation="$navigationItems"
    :stats="$dashboardStats"
    :user="$user"
    currentRoute="dashboard"
>
    <x-slot name="subtitle">
        Monitor your code quality and scanning results
    </x-slot>
    
    <x-slot name="actions">
        <x-atoms.button variant="secondary" icon="cog" href="/settings">
            Settings
        </x-atoms.button>
        <x-atoms.button variant="primary" icon="plus-circle">
            New Scan
        </x-atoms.button>
    </x-slot>
    
    <!-- Alerts Section -->
    <div class="space-y-4 mb-8">
        <x-molecules.alert type="success" title="Scan Completed" dismissible>
            Your latest scan has completed successfully. 5 issues were found and resolved.
        </x-molecules.alert>
        
        <x-molecules.alert type="warning" title="High Priority Issues">
            There are 3 high-priority security issues that require immediate attention.
        </x-molecules.alert>
    </div>
    
    <!-- Search and Filters -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Recent Scans</h2>
            <div class="flex gap-3">
                <x-molecules.search-box placeholder="Search scans..." />
                <x-atoms.button variant="secondary" icon="cog" iconPosition="only" />
            </div>
        </div>
        
        <!-- Sample Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issues</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Scan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-atoms.icon name="document-text" size="sm" color="secondary" class="mr-3" />
                                <span class="text-sm font-medium text-gray-900">UserController.php</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-atoms.badge variant="success">Clean</x-atoms.badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 minutes ago</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <x-atoms.button variant="ghost" size="sm">View Details</x-atoms.button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <x-atoms.icon name="document-text" size="sm" color="secondary" class="mr-3" />
                                <span class="text-sm font-medium text-gray-900">ProductService.php</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-atoms.badge variant="warning">Issues Found</x-atoms.badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5 minutes ago</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <x-atoms.button variant="ghost" size="sm">View Details</x-atoms.button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Form Example -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Quick Scan</h2>
        
        <form class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-molecules.form-field 
                    label="Scan Type" 
                    required
                    help="Choose the type of scan to perform"
                >
                    <x-atoms.input placeholder="Select scan type..." />
                </x-molecules.form-field>
                
                <x-molecules.form-field 
                    label="Target Directory"
                    error="This field is required"
                >
                    <x-atoms.input placeholder="/path/to/directory" state="error" />
                </x-molecules.form-field>
            </div>
            
            <x-molecules.form-field 
                label="Scan Options"
                help="Additional options for the scan"
            >
                <x-atoms.input placeholder="--exclude=vendor --include-tests" />
            </x-molecules.form-field>
            
            <div class="flex justify-end space-x-3">
                <x-atoms.button variant="secondary">Cancel</x-atoms.button>
                <x-atoms.button variant="primary" icon="search">Start Scan</x-atoms.button>
            </div>
        </form>
    </div>
    
    <!-- Loading States -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Loading States</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3">Buttons</h3>
                <div class="space-y-2">
                    <x-atoms.button variant="primary" loading fullWidth>Loading...</x-atoms.button>
                    <x-atoms.button variant="secondary" loading fullWidth>Processing</x-atoms.button>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3">Spinners</h3>
                <div class="flex space-x-4 items-center">
                    <x-atoms.spinner size="sm" />
                    <x-atoms.spinner size="md" />
                    <x-atoms.spinner size="lg" />
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3">Progress</h3>
                <div class="space-y-3">
                    <x-atoms.progress-bar :value="30" :max="100" showLabel label="Scanning files..." />
                    <x-atoms.progress-bar :value="75" :max="100" variant="success" showLabel />
                </div>
            </div>
        </div>
    </div>
    
    <!-- Empty State Example -->
    <div class="bg-white p-6 rounded-lg shadow">
        <x-molecules.empty-state
            icon="search"
            title="No scans found"
            description="Get started by running your first code scan"
            actionText="Start New Scan"
            actionHref="/scans/new"
        />
    </div>
</x-templates.dashboard-layout>
