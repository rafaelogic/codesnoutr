@props([
    'scan' => null,
    'issues' => [],
    'loading' => false,
    'filters' => [],
    'groupBy' => 'file', // file, type, severity
    'showActions' => true,
    'aiEnabled' => false
])

<div 
    x-data="scanResults()"
    class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md"
    {{ $attributes }}
>
    <!-- Header with Filters and Actions -->
    <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <!-- Scan Info -->
            <div class="flex-1">
                @if($scan)
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Scan Results: {{ $scan['name'] ?? 'Unknown' }}
                    </h3>
                    <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                        <span>Completed {{ $scan['completed_at'] ?? 'Unknown' }}</span>
                        <span>•</span>
                        <span>{{ count($issues) }} issues found</span>
                        @if($aiEnabled && isset($scan['ai_suggestions']))
                            <span>•</span>
                            <span>{{ $scan['ai_suggestions'] }} AI suggestions</span>
                        @endif
                    </div>
                @else
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Scan Results</h3>
                @endif
            </div>
            
            <!-- Filters and Actions -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-3">
                <!-- Search -->
                <x-molecules.search-box 
                    placeholder="Search issues..." 
                    size="sm"
                    x-model="searchTerm"
                    @input="filterIssues()"
                />
                
                <!-- Severity Filter -->
                <x-molecules.dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <x-atoms.button variant="secondary" size="sm" icon="filter">
                            <span x-text="selectedSeverity === 'all' ? 'All Severities' : selectedSeverity.charAt(0).toUpperCase() + selectedSeverity.slice(1)"></span>
                            <x-atoms.icon name="chevron-down" size="sm" class="ml-1" />
                        </x-atoms.button>
                    </x-slot>
                    
                    <x-molecules.dropdown-item @click="filterBySeverity('all')">All Severities</x-molecules.dropdown-item>
                    <x-molecules.dropdown-item @click="filterBySeverity('critical')">Critical</x-molecules.dropdown-item>
                    <x-molecules.dropdown-item @click="filterBySeverity('high')">High</x-molecules.dropdown-item>
                    <x-molecules.dropdown-item @click="filterBySeverity('medium')">Medium</x-molecules.dropdown-item>
                    <x-molecules.dropdown-item @click="filterBySeverity('low')">Low</x-molecules.dropdown-item>
                </x-molecules.dropdown>
                
                <!-- Group By -->
                <x-molecules.dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <x-atoms.button variant="secondary" size="sm" icon="view-grid">
                            Group by {{ groupBy }}
                            <x-atoms.icon name="chevron-down" size="sm" class="ml-1" />
                        </x-atoms.button>
                    </x-slot>
                    
                    <x-molecules.dropdown-item @click="changeGrouping('file')">Group by File</x-molecules.dropdown-item>
                    <x-molecules.dropdown-item @click="changeGrouping('type')">Group by Type</x-molecules.dropdown-item>
                    <x-molecules.dropdown-item @click="changeGrouping('severity')">Group by Severity</x-molecules.dropdown-item>
                </x-molecules.dropdown>
                
                @if($showActions)
                    <!-- Bulk Actions -->
                    <x-molecules.dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <x-atoms.button variant="primary" size="sm" icon="cog">
                                Actions
                                <x-atoms.icon name="chevron-down" size="sm" class="ml-1" />
                            </x-atoms.button>
                        </x-slot>
                        
                        <x-molecules.dropdown-item icon="download">Export Results</x-molecules.dropdown-item>
                        <x-molecules.dropdown-item icon="refresh">Re-scan</x-molecules.dropdown-item>
                        @if($aiEnabled)
                            <x-molecules.dropdown-divider />
                            <x-molecules.dropdown-item icon="lightning-bolt">Generate AI Fixes</x-molecules.dropdown-item>
                            <x-molecules.dropdown-item icon="check">Apply Safe Fixes</x-molecules.dropdown-item>
                        @endif
                        <x-molecules.dropdown-divider />
                        <x-molecules.dropdown-item icon="archive" destructive>Archive Issues</x-molecules.dropdown-item>
                    </x-molecules.dropdown>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Loading State -->
    @if($loading)
        <div class="px-4 py-12">
            <div class="text-center">
                <x-atoms.spinner size="lg" class="mb-4" />
                <p class="text-sm text-gray-500">Loading scan results...</p>
            </div>
        </div>
    @elseif(empty($issues))
        <!-- Empty State -->
        <div class="px-4 py-12">
            <x-molecules.empty-state
                icon="check-circle"
                title="No issues found"
                description="Your code looks clean! No issues were detected in this scan."
                size="md"
            />
        </div>
    @else
        <!-- Results List -->
        <div class="divide-y divide-gray-200">
            <template x-for="(group, groupKey) in groupedIssues" :key="groupKey">
                <div class="px-4 py-4">
                    <!-- Group Header -->
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <template x-if="groupBy === 'file'">
                                <x-atoms.icon name="document-text" size="sm" class="mr-2" />
                            </template>
                            <template x-if="groupBy === 'type'">
                                <x-atoms.icon name="tag" size="sm" class="mr-2" />
                            </template>
                            <template x-if="groupBy === 'severity'">
                                <x-atoms.icon name="exclamation-triangle" size="sm" class="mr-2" />
                            </template>
                            <span x-text="groupKey"></span>
                        </h4>
                        <x-atoms.badge 
                            variant="secondary" 
                            size="sm"
                            x-text="group.length + ' issue' + (group.length !== 1 ? 's' : '')"
                        ></x-atoms.badge>
                    </div>
                    
                    <!-- Issues in Group -->
                    <div class="space-y-3">
                        <template x-for="(issue, issueIndex) in group" :key="issueIndex">
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <!-- Issue Header -->
                                        <div class="flex items-center space-x-3 mb-2">
                                            <x-atoms.badge 
                                                :variant="'danger'"
                                                x-text="issue.severity"
                                                ::class="{
                                                    'bg-red-100 text-red-800': issue.severity === 'critical',
                                                    'bg-orange-100 text-orange-800': issue.severity === 'high',
                                                    'bg-yellow-100 text-yellow-800': issue.severity === 'medium',
                                                    'bg-blue-100 text-blue-800': issue.severity === 'low'
                                                }"
                                            ></x-atoms.badge>
                                            
                                            <span class="text-sm text-gray-500" x-text="'Line ' + issue.line"></span>
                                            
                                            @if($aiEnabled)
                                                <template x-if="issue.ai_suggestion">
                                                    <x-atoms.badge variant="info" size="sm">
                                                        <x-atoms.icon name="lightning-bolt" size="xs" class="mr-1" />
                                                        AI Fix Available
                                                    </x-atoms.badge>
                                                </template>
                                            @endif
                                        </div>
                                        
                                        <!-- Issue Title and Description -->
                                        <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1" x-text="issue.title"></h5>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3" x-text="issue.description"></p>
                                        
                                        <!-- Code Preview -->
                                        <template x-if="issue.code_snippet">
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-3 mb-3">
                                                <pre class="text-xs text-gray-800 overflow-x-auto"><code x-text="issue.code_snippet"></code></pre>
                                            </div>
                                        </template>
                                        
                                        <!-- File Path -->
                                        <div class="flex items-center text-xs text-gray-500">
                                            <x-atoms.icon name="folder" size="xs" class="mr-1" />
                                            <span x-text="issue.file_path"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Issue Actions -->
                                    <div class="flex items-center space-x-2 ml-4">
                                        @if($aiEnabled)
                                            <template x-if="issue.ai_suggestion">
                                                <x-molecules.dropdown align="right" width="48">
                                                    <x-slot name="trigger">
                                                        <x-atoms.button variant="primary" size="sm">
                                                            <x-atoms.icon name="lightning-bolt" size="sm" class="mr-1" />
                                                            AI Fix
                                                        </x-atoms.button>
                                                    </x-slot>
                                                    
                                                    <x-molecules.dropdown-item icon="eye">Preview Fix</x-molecules.dropdown-item>
                                                    <x-molecules.dropdown-item icon="check">Apply Fix</x-molecules.dropdown-item>
                                                    <x-molecules.dropdown-item icon="information-circle">View Explanation</x-molecules.dropdown-item>
                                                </x-molecules.dropdown>
                                            </template>
                                        @endif
                                        
                                        <x-molecules.dropdown align="right" width="48">
                                            <x-slot name="trigger">
                                                <x-atoms.button variant="secondary" size="sm" iconPosition="only" icon="dots-vertical" />
                                            </x-slot>
                                            
                                            <x-molecules.dropdown-item icon="eye">View Details</x-molecules.dropdown-item>
                                            <x-molecules.dropdown-item icon="external-link">Open File</x-molecules.dropdown-item>
                                            <x-molecules.dropdown-item icon="flag">Mark as False Positive</x-molecules.dropdown-item>
                                            <x-molecules.dropdown-divider />
                                            <x-molecules.dropdown-item icon="archive" destructive>Ignore Issue</x-molecules.dropdown-item>
                                        </x-molecules.dropdown>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    @endif
</div>

<script>
function scanResults() {
    return {
        searchTerm: '',
        selectedSeverity: 'all',
        groupBy: @js($groupBy),
        issues: @json($issues),
        filteredIssues: @json($issues),
        groupedIssues: {},
        
        init() {
            this.filterIssues();
        },
        
        filterIssues() {
            let filtered = this.issues;
            
            // Apply search filter
            if (this.searchTerm) {
                filtered = filtered.filter(issue => 
                    issue.title.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                    issue.description.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                    issue.file_path.toLowerCase().includes(this.searchTerm.toLowerCase())
                );
            }
            
            // Apply severity filter
            if (this.selectedSeverity !== 'all') {
                filtered = filtered.filter(issue => issue.severity === this.selectedSeverity);
            }
            
            this.filteredIssues = filtered;
            this.groupIssues();
        },
        
        filterBySeverity(severity) {
            this.selectedSeverity = severity;
            this.filterIssues();
        },
        
        changeGrouping(groupBy) {
            this.groupBy = groupBy;
            this.groupIssues();
        },
        
        groupIssues() {
            const grouped = {};
            
            this.filteredIssues.forEach(issue => {
                let key;
                if (this.groupBy === 'file') {
                    key = issue.file_path;
                } else if (this.groupBy === 'type') {
                    key = issue.type;
                } else if (this.groupBy === 'severity') {
                    key = issue.severity;
                } else {
                    key = 'All Issues';
                }
                
                if (!grouped[key]) {
                    grouped[key] = [];
                }
                grouped[key].push(issue);
            });
            
            this.groupedIssues = grouped;
        }
    }
}
</script>
