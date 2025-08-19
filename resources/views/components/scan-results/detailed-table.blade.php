<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <input type="checkbox" 
                               wire:click="selectAllIssues"
                               class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sortBy('severity')">
                        Severity 
                        @if($sortBy === 'severity')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sortBy('category')">
                        Category
                        @if($sortBy === 'category')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Issue
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sortBy('file_path')">
                        File
                        @if($sortBy === 'file_path')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Line
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($issues as $issue)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ $issue->fixed ? 'opacity-75' : '' }}">
                    <!-- Selection Checkbox -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" 
                               wire:click="toggleIssueSelection({{ $issue->id }})"
                               {{ in_array($issue->id, $selectedIssues) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                    </td>

                    <!-- Severity -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $issue->severity === 'critical' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                            {{ $issue->severity === 'high' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}
                            {{ $issue->severity === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                            {{ $issue->severity === 'low' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                            {{ $issue->severity === 'info' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : '' }}
                            {{ $issue->severity === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}">
                            {{ ucfirst($issue->severity) }}
                        </span>
                    </td>

                    <!-- Category -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                            {{ ucfirst($issue->category) }}
                        </span>
                    </td>

                    <!-- Issue Title & Description -->
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $issue->title }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-md" title="{{ $issue->description }}">
                            {{ Str::limit($issue->description, 100) }}
                        </div>
                    </td>

                    <!-- File Path -->
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        <div class="font-mono text-xs">
                            {{ basename($issue->file_path) }}
                        </div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 truncate max-w-xs" title="{{ $issue->file_path }}">
                            {{ Str::limit(dirname($issue->file_path), 30) }}
                        </div>
                    </td>

                    <!-- Line Number -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $issue->line_number ?? '-' }}
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($issue->fix_method === 'manual')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Resolved
                            </span>
                        @elseif($issue->fix_method === 'ignored')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                Ignored
                            </span>
                        @elseif($issue->fix_method === 'false_positive')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                False Positive
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                Open
                            </span>
                        @endif
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center space-x-2 justify-end">
                            <!-- Expand/Collapse -->
                            <button wire:click="toggleIssueExpansion({{ $issue->id }})" 
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1">
                                <svg class="h-4 w-4 transform transition-transform {{ in_array($issue->id, $expandedIssues) ? 'rotate-180' : '' }}" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Action Buttons -->
                            @if($issue->fix_method !== 'manual')
                            <button wire:click="resolveIssue({{ $issue->id }})" 
                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800">
                                Resolve
                            </button>
                            @endif
                            
                            @if($issue->fix_method !== 'ignored')
                            <button wire:click="markAsIgnored({{ $issue->id }})" 
                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                Ignore
                            </button>
                            @endif
                            
                            @if($issue->fix_method !== 'false_positive')
                            <button wire:click="markAsFalsePositive({{ $issue->id }})" 
                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800">
                                False Positive
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- Expanded Row -->
                @if(in_array($issue->id, $expandedIssues))
                <tr>
                    <td colspan="8" class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                        <div class="space-y-4">
                            <!-- Full Description -->
                            <div>
                                <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Description</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $issue->description }}</p>
                            </div>

                            <!-- Code Context -->
                            @if($issue->context)
                            <div>
                                <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Code Context</h5>
                                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                    <pre class="text-sm text-gray-100"><code>{{ is_array($issue->context) ? json_encode($issue->context, JSON_PRETTY_PRINT) : $issue->context }}</code></pre>
                                </div>
                            </div>
                            @endif
                            
                            <!-- Fix Suggestion -->
                            @if($issue->suggestion)
                            <div>
                                <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Fix Suggestion</h5>
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <p class="text-sm text-blue-800 dark:text-blue-200">{{ $issue->suggestion }}</p>
                                </div>
                            </div>
                            @endif
                            
                            <!-- Metadata -->
                            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center space-x-4">
                                <span>Rule: {{ $issue->rule_id }}</span>
                                <span>•</span>
                                <span>Found on {{ $issue->created_at->format('M j, Y g:i A') }}</span>
                                @if($issue->fixed_at)
                                    <span>•</span>
                                    <span>{{ ucfirst($issue->fix_method) }} on {{ $issue->fixed_at->format('M j, Y g:i A') }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="mt-8">
    {{ $issues->links() }}
</div>
