{{-- Settings Demo Page showcasing the complete Atomic Design System --}}
<x-templates.settings-layout 
    title="CodeSnoutr Settings"
    :navigation="[
        ['key' => 'general', 'label' => 'General', 'icon' => 'cog', 'url' => '#general'],
        ['key' => 'scanning', 'label' => 'Scanning Rules', 'icon' => 'search', 'url' => '#scanning', 'badge' => '12'],
        ['key' => 'ai', 'label' => 'AI Features', 'icon' => 'lightning-bolt', 'url' => '#ai', 'badge' => 'NEW'],
        ['key' => 'notifications', 'label' => 'Notifications', 'icon' => 'bell', 'url' => '#notifications'],
        ['key' => 'integrations', 'label' => 'Integrations', 'icon' => 'puzzle-piece', 'url' => '#integrations'],
        ['key' => 'advanced', 'label' => 'Advanced', 'icon' => 'adjustments', 'url' => '#advanced']
    ]"
    active-section="general"
>
    {{-- General Settings --}}
    <x-molecules.card 
        title="General Settings" 
        description="Configure basic CodeSnoutr preferences and behavior"
    >
        <x-molecules.settings-form action="#" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-molecules.form-field 
                    label="Project Name"
                    name="project_name"
                    help="A friendly name for your project"
                >
                    <x-atoms.input 
                        name="project_name" 
                        placeholder="My Laravel Project"
                        value="CodeSnoutr Demo"
                    />
                </x-molecules.form-field>

                <x-molecules.form-field 
                    label="Default Scan Type"
                    name="default_scan_type"
                >
                    <x-atoms.select 
                        name="default_scan_type"
                        :options="[
                            'full' => 'Full Codebase Scan',
                            'incremental' => 'Incremental Scan',
                            'custom' => 'Custom Rules Only'
                        ]"
                        value="full"
                    />
                </x-molecules.form-field>
            </div>

            <div class="space-y-4">
                <x-atoms.toggle
                    name="auto_scan"
                    label="Auto-scan on file changes"
                    description="Automatically trigger scans when files are modified"
                    :checked="true"
                />

                <x-atoms.toggle
                    name="detailed_reports"
                    label="Generate detailed reports"
                    description="Include comprehensive analysis in scan results"
                    :checked="false"
                />

                <x-atoms.toggle
                    name="send_notifications"
                    label="Enable notifications"
                    description="Receive alerts for scan completion and critical issues"
                    :checked="true"
                />
            </div>
        </x-molecules.settings-form>
    </x-molecules.card>

    {{-- AI Features Settings --}}
    <x-molecules.card 
        title="AI-Powered Features" 
        description="Configure AI assistance for code analysis and fixes"
    >
        <x-molecules.alert variant="info" class="mb-6">
            <x-slot name="title">AI Features Available</x-slot>
            <p>AI-powered code analysis and automatic fixes are now available. Configure your preferences below.</p>
        </x-molecules.alert>

        <x-molecules.settings-form action="#" method="POST">
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-molecules.form-field 
                        label="AI Provider"
                        name="ai_provider"
                    >
                        <x-atoms.select 
                            name="ai_provider"
                            :options="[
                                'openai' => 'OpenAI GPT-4',
                                'anthropic' => 'Anthropic Claude',
                                'local' => 'Local Model'
                            ]"
                            value="openai"
                        />
                    </x-molecules.form-field>

                    <x-molecules.form-field 
                        label="Confidence Threshold"
                        name="confidence_threshold"
                        help="Minimum confidence level for auto-fixes (0-100%)"
                    >
                        <x-atoms.input 
                            name="confidence_threshold" 
                            type="number"
                            min="0"
                            max="100"
                            value="85"
                        />
                    </x-molecules.form-field>
                </div>

                <div class="space-y-4">
                    <x-atoms.toggle
                        name="ai_auto_fix"
                        label="Enable AI Auto-Fix"
                        description="Automatically apply high-confidence fixes without manual review"
                        :checked="false"
                        color="warning"
                    />

                    <x-atoms.toggle
                        name="ai_suggestions"
                        label="AI Fix Suggestions"
                        description="Show AI-generated fix suggestions for manual review"
                        :checked="true"
                    />

                    <x-atoms.toggle
                        name="ai_explanations"
                        label="Detailed Explanations"
                        description="Include AI explanations for detected issues and fixes"
                        :checked="true"
                    />
                </div>
            </div>
        </x-molecules.settings-form>
    </x-molecules.card>

    {{-- Scanning Rules --}}
    <x-molecules.card 
        title="Scanning Rules Configuration" 
        description="Enable or disable specific code analysis rules"
    >
        <div class="space-y-6">
            {{-- Search and Filter --}}
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <x-molecules.search-box 
                        placeholder="Search rules..."
                        name="rule_search"
                    />
                </div>
                <div class="sm:w-48">
                    <x-atoms.select 
                        name="rule_category"
                        placeholder="All Categories"
                        :options="[
                            'security' => 'Security',
                            'performance' => 'Performance', 
                            'style' => 'Code Style',
                            'complexity' => 'Complexity'
                        ]"
                    />
                </div>
            </div>

            {{-- Rules Table --}}
            <x-organisms.data-table
                :headers="['Rule', 'Category', 'Severity', 'AI Enabled', 'Status']"
                :data="[
                    [
                        'rule' => 'SQL Injection Detection',
                        'category' => 'Security',
                        'severity' => 'High',
                        'ai_enabled' => true,
                        'status' => 'enabled'
                    ],
                    [
                        'rule' => 'Unused Variables',
                        'category' => 'Code Quality',
                        'severity' => 'Medium',
                        'ai_enabled' => true,
                        'status' => 'enabled'
                    ],
                    [
                        'rule' => 'Magic Numbers',
                        'category' => 'Best Practices',
                        'severity' => 'Low',
                        'ai_enabled' => false,
                        'status' => 'disabled'
                    ]
                ]"
                :actions="['edit', 'delete']"
                searchable
                sortable
            />
        </div>
    </x-molecules.card>

    {{-- Advanced Settings --}}
    <x-molecules.card 
        title="Advanced Configuration" 
        description="Expert-level settings for power users"
    >
        <x-molecules.alert variant="warning" class="mb-6">
            <x-slot name="title">Caution Required</x-slot>
            <p>These settings can significantly impact performance. Change only if you understand the implications.</p>
        </x-molecules.alert>

        <x-molecules.settings-form action="#" method="POST">
            <div class="space-y-6">
                <x-molecules.form-field 
                    label="Maximum Scan Duration (minutes)"
                    name="max_scan_duration"
                    help="Automatically terminate scans that exceed this duration"
                >
                    <x-atoms.input 
                        name="max_scan_duration" 
                        type="number"
                        min="1"
                        max="120"
                        value="30"
                    />
                </x-molecules.form-field>

                <x-molecules.form-field 
                    label="Concurrent Jobs"
                    name="concurrent_jobs"
                    help="Number of scanning jobs to run simultaneously"
                >
                    <x-atoms.input 
                        name="concurrent_jobs" 
                        type="number"
                        min="1"
                        max="10"
                        value="3"
                    />
                </x-molecules.form-field>

                <x-molecules.form-field 
                    label="Custom Scan Paths"
                    name="custom_paths"
                    help="Additional directories to include in scans (one per line)"
                >
                    <textarea 
                        name="custom_paths" 
                        rows="4"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        placeholder="/path/to/custom/directory&#10;/another/path"
                    ></textarea>
                </x-molecules.form-field>
            </div>
        </x-molecules.settings-form>
    </x-molecules.card>

    {{-- Component Showcase --}}
    <x-molecules.card 
        title="Atomic Design System Showcase" 
        description="Complete demonstration of all available components"
    >
        <div class="space-y-8">
            {{-- Atoms Demo --}}
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Atoms</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <x-atoms.label>Button Variants</x-atoms.label>
                        <div class="space-x-2">
                            <x-atoms.button variant="primary" size="sm">Primary</x-atoms.button>
                            <x-atoms.button variant="secondary" size="sm">Secondary</x-atoms.button>
                            <x-atoms.button variant="danger" size="sm">Danger</x-atoms.button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-atoms.label>Badges</x-atoms.label>
                        <div class="space-x-2">
                            <x-atoms.badge variant="success">Success</x-atoms.badge>
                            <x-atoms.badge variant="warning">Warning</x-atoms.badge>
                            <x-atoms.badge variant="danger">Error</x-atoms.badge>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-atoms.label>Progress Bar</x-atoms.label>
                        <x-atoms.progress-bar value="75" color="success" />
                    </div>
                </div>
            </div>

            {{-- Molecules Demo --}}
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Molecules</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-molecules.stat-card
                        title="Total Scans"
                        value="1,247"
                        change="+12.3%"
                        trend="up"
                        icon="search"
                    />

                    <x-molecules.stat-card
                        title="Issues Found"
                        value="43"
                        change="-8.1%"
                        trend="down"
                        icon="exclamation-triangle"
                        color="warning"
                    />
                </div>
            </div>

            {{-- Status Indicators --}}
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Status & Loading States</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center space-x-2">
                        <x-atoms.spinner size="sm" color="primary" />
                        <span class="text-sm text-gray-600">Scanning in progress...</span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <x-atoms.icon name="check-circle" color="success" />
                        <span class="text-sm text-gray-600">Scan completed</span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <x-atoms.icon name="x-circle" color="danger" />
                        <span class="text-sm text-gray-600">Scan failed</span>
                    </div>
                </div>
            </div>
        </div>
    </x-molecules.card>
</x-templates.settings-layout>
