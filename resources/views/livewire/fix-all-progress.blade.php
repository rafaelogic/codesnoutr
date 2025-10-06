<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- CSS Load Indicator (for debugging) -->
        <div class="max-w-7xl mx-auto mb-4">
            <x-atoms.badge variant="success" size="sm" :dot="true">
                Atomic UI Loaded
            </x-atoms.badge>
        </div>
        
        <div class="max-w-7xl mx-auto space-y-8" @if(in_array($status ?? 'idle', ['processing', 'starting'])) wire:poll.1s="refreshProgress" @endif>
        <!-- Header Section -->
        <x-atoms.surface variant="default" padding="default" rounded="lg" shadow="sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                @php
                    $currentStatus = $status ?? 'idle';
                    $statusColor = match($currentStatus) {
                        'completed' => 'green',
                        'failed' => 'red', 
                        'processing' => 'blue',
                        'starting' => 'yellow',
                        'stopping' => 'orange',
                        'stopped' => 'gray',
                        'idle' => 'gray',
                        default => 'gray'
                    };
                    $statusIcon = match($currentStatus) {
                        'completed' => '✓',
                        'failed' => '✗',
                        'processing' => '⚙️', 
                        'starting' => '▶️',
                        'stopping' => '⏹️',
                        'stopped' => '⏸️',
                        'idle' => '⏸️',
                        default => '🕒'
                    };
                @endphp
                
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-inner
                            {{ $currentStatus === 'completed' ? 'bg-green-100 dark:bg-green-900/30' : '' }}
                            {{ $currentStatus === 'failed' ? 'bg-red-100 dark:bg-red-900/30' : '' }}
                            {{ $currentStatus === 'processing' ? 'bg-blue-100 dark:bg-blue-900/30' : '' }}
                            {{ $currentStatus === 'starting' ? 'bg-yellow-100 dark:bg-yellow-900/30' : '' }}
                            {{ !in_array($currentStatus, ['completed', 'failed', 'processing', 'starting']) ? 'bg-gray-100 dark:bg-gray-900/30' : '' }}
                        ">
                            <span class="text-3xl {{ $currentStatus === 'processing' ? 'animate-spin' : '' }}">{{ $statusIcon }}</span>
                        </div>
                    </div>
                    
                    <div class="min-w-0">
                        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                            Fix All Issues with AI
                        </h1>
                        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <x-atoms.badge variant="primary" size="sm">
                                Session: {{ Str::limit($sessionId ?? 'N/A', 8) }}
                            </x-atoms.badge>
                            @if($startedAt)
                                @php
                                    $start = \Carbon\Carbon::parse($startedAt);
                                    $end = $completedAt ? \Carbon\Carbon::parse($completedAt) : now();
                                    $elapsedTime = $start->diff($end)->format('%H:%I:%S');
                                @endphp
                                <span class="text-gray-400">•</span>
                                <x-atoms.badge variant="gray" size="sm">
                                    <x-atoms.icon name="clock" size="xs" class="mr-1" />
                                    {{ $elapsedTime }}
                                </x-atoms.badge>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    @if(in_array($status ?? 'idle', ['completed', 'failed', 'stopped']))
                        <x-atoms.button 
                            wire:click="downloadResults"
                            variant="outline-secondary"
                            size="md"
                            icon="download"
                            :disabled="empty($results ?? [])"
                        >
                            <span class="hidden sm:inline">Download Results</span>
                        </x-atoms.button>
                    @endif
                    
                    @if(in_array($status ?? 'idle', ['processing', 'starting']))
                        <x-atoms.button 
                            wire:click="stopFixAll"
                            variant="danger"
                            size="md"
                            icon="x-circle"
                            wire:loading.attr="disabled"
                            class="animate-pulse"
                        >
                            <span class="hidden sm:inline" wire:loading.remove>Stop Process</span>
                            <span class="hidden sm:inline" wire:loading>Stopping...</span>
                        </x-atoms.button>
                    @endif
                    
                    @if(!in_array($status ?? 'idle', ['processing', 'starting']))
                        <x-atoms.button 
                            wire:click="toggleAutoRefresh"
                            :variant="($autoRefresh ?? true) ? 'warning' : 'success'"
                            size="md"
                            :icon="($autoRefresh ?? true) ? 'pause' : 'play'"
                        >
                            <span class="hidden sm:inline">{{ ($autoRefresh ?? true) ? 'Pause' : 'Resume' }}</span>
                        </x-atoms.button>
                    @endif
                    
                    <x-atoms.button 
                        wire:click="goToDashboard"
                        variant="secondary"
                        size="md"
                        icon="arrow-left"
                    >
                        <span class="hidden sm:inline">Back to Dashboard</span>
                    </x-atoms.button>
                </div>
            </div>
        </x-atoms.surface>

        <!-- Queue Worker Warning (shown when stuck at step 0 for >10 seconds) -->
        @if($status === 'processing' && $currentStep === 0 && $startedAt)
            @php
                $startTime = \Carbon\Carbon::parse($startedAt);
                $secondsSinceStart = now()->diffInSeconds($startTime);
            @endphp
            
            @if($secondsSinceStart > 10)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 rounded-lg animate-pulse" role="alert">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                ⚠️ Queue Worker May Not Be Running
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p class="mb-2">
                                    The job has been queued for {{ $secondsSinceStart }} seconds but hasn't started processing yet.
                                </p>
                                <p class="font-semibold mb-2">To fix this:</p>
                                <ol class="list-decimal list-inside space-y-1 ml-2">
                                    <li>Open a new terminal window</li>
                                    <li>Navigate to your Laravel app directory</li>
                                    <li>Run: <code class="bg-yellow-100 dark:bg-yellow-800 px-2 py-1 rounded font-mono text-xs">php artisan queue:work --queue=default</code></li>
                                    <li>Keep that terminal open and running</li>
                                    <li>Wait for this page to update automatically</li>
                                </ol>
                                <p class="mt-3 text-xs">
                                    <strong>Alternative:</strong> Change QUEUE_CONNECTION=sync in your .env file (only for testing)
                                </p>
                            </div>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="text-xs text-yellow-600 dark:text-yellow-400">
                                Waiting: {{ $secondsSinceStart }}s
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Progress Overview Card -->
        <x-atoms.surface variant="default" padding="none" rounded="lg" shadow="sm" class="overflow-hidden">
                @php
                    $currentStatus = $status ?? 'idle';
                    $statusColor = match($currentStatus) {
                        'completed' => 'green',
                        'failed' => 'red',
                        'processing' => 'blue', 
                        'starting' => 'yellow',
                        'stopping' => 'orange',
                        'stopped' => 'gray',
                        'idle' => 'gray',
                        default => 'gray'
                    };
                @endphp            <!-- Card Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800
                {{ $currentStatus === 'completed' ? 'bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/25 dark:to-green-800/25' : '' }}
                {{ $currentStatus === 'failed' ? 'bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/25 dark:to-red-800/25' : '' }}
                {{ $currentStatus === 'processing' ? 'bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/25 dark:to-blue-800/25' : '' }}
                {{ $currentStatus === 'starting' ? 'bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/25 dark:to-yellow-800/25' : '' }}
                {{ $currentStatus === 'stopping' ? 'bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/25 dark:to-orange-800/25' : '' }}
                {{ !in_array($currentStatus, ['completed', 'failed', 'processing', 'starting', 'stopping']) ? 'bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800' : '' }}
            ">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-4 h-4 rounded-full {{ $currentStatus === 'processing' ? 'animate-pulse' : '' }}
                            {{ $currentStatus === 'completed' ? 'bg-green-500' : '' }}
                            {{ $currentStatus === 'failed' ? 'bg-red-500' : '' }}
                            {{ $currentStatus === 'processing' ? 'bg-blue-500' : '' }}
                            {{ $currentStatus === 'starting' ? 'bg-yellow-500' : '' }}
                            {{ $currentStatus === 'stopping' ? 'bg-orange-500' : '' }}
                            {{ !in_array($currentStatus, ['completed', 'failed', 'processing', 'starting', 'stopping']) ? 'bg-gray-500' : '' }}
                        "></div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white capitalize">
                                {{ $currentStatus }} 
                                @if(($status ?? 'initializing') === 'processing' && ($totalSteps ?? 0) > 0)
                                    <span class="text-base font-normal text-gray-600 dark:text-gray-400">
                                        ({{ $currentStep ?? 0 }}/{{ $totalSteps ?? 0 }})
                                    </span>
                                @endif
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">{{ $message ?? 'Loading...' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card Body -->
            <div class="p-6 space-y-6">

                <!-- Progress Bar -->
                @if(($totalSteps ?? 0) > 0)
                    <div>
                        @php
                            $currentStep = $currentStep ?? 0;
                            $totalSteps = $totalSteps ?? 0;
                            $progressPercentage = $totalSteps > 0 ? min(100, round(($currentStep / $totalSteps) * 100, 1)) : 0;
                        @endphp
                        
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Overall Progress</h4>
                            <div class="flex items-center space-x-2">
                                <span class="text-2xl font-bold
                                    {{ $currentStatus === 'completed' ? 'text-green-600 dark:text-green-300' : '' }}
                                    {{ $currentStatus === 'failed' ? 'text-red-600 dark:text-red-300' : '' }}
                                    {{ $currentStatus === 'processing' ? 'text-blue-600 dark:text-blue-300' : '' }}
                                    {{ $currentStatus === 'starting' ? 'text-yellow-600 dark:text-yellow-300' : '' }}
                                    {{ !in_array($currentStatus, ['completed', 'failed', 'processing', 'starting']) ? 'text-gray-600 dark:text-gray-400' : '' }}
                                ">{{ $progressPercentage }}%</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">complete</span>
                            </div>
                        </div>
                        
                        <div class="relative">
                            <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-4 shadow-inner">
                                <div 
                                    class="h-4 rounded-full transition-all duration-500 ease-out shadow-sm {{ $currentStatus === 'processing' ? 'animate-pulse' : '' }}
                                        {{ $currentStatus === 'completed' ? 'bg-gradient-to-r from-green-500 to-green-600' : '' }}
                                        {{ $currentStatus === 'failed' ? 'bg-gradient-to-r from-red-500 to-red-600' : '' }}
                                        {{ $currentStatus === 'processing' ? 'bg-gradient-to-r from-blue-500 to-blue-600' : '' }}
                                        {{ $currentStatus === 'starting' ? 'bg-gradient-to-r from-yellow-500 to-yellow-600' : '' }}
                                        {{ !in_array($currentStatus, ['completed', 'failed', 'processing', 'starting']) ? 'bg-gradient-to-r from-gray-500 to-gray-600' : '' }}
                                    "
                                    style="width: {{ $progressPercentage }}%"
                                ></div>
                            </div>
                            @if($currentStatus === 'processing')
                                <div class="absolute inset-0 rounded-full bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
                            @endif
                        </div>
                        
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-2">
                            <span>{{ $currentStep ?? 0 }} completed</span>
                            <span>{{ $totalSteps ?? 0 }} total</span>
                        </div>
                    </div>
                @endif

                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Fixed Count -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/25 dark:to-emerald-900/25 rounded-xl p-6 border border-green-200 dark:border-green-800/40 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-3xl font-bold text-green-600 dark:text-green-300 mb-1">{{ $fixedCount ?? 0 }}</div>
                                <div class="text-sm font-medium text-green-700 dark:text-green-200">Issues Fixed</div>
                            </div>
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                                <x-atoms.icon name="check-circle" size="lg" class="text-green-600 dark:text-green-300" />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Failed Count -->
                    <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/25 dark:to-rose-900/25 rounded-xl p-6 border border-red-200 dark:border-red-800/40 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-3xl font-bold text-red-600 dark:text-red-300 mb-1">{{ $failedCount ?? 0 }}</div>
                                <div class="text-sm font-medium text-red-700 dark:text-red-200">Failed Fixes</div>
                            </div>
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
                                <x-atoms.icon name="x-circle" size="lg" class="text-red-600 dark:text-red-300" />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Count -->
                    <div class="bg-gradient-to-br from-gray-50 to-slate-50 dark:from-gray-900 dark:to-slate-900 rounded-xl p-6 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                @php
                                    // Calculate total count based on available data
                                    $totalCount = 0;
                                    $countLabel = 'Total Issues';
                                    
                                    if (($totalSteps ?? 0) > 0) {
                                        // If we have totalSteps from the job, use that
                                        $totalCount = $totalSteps;
                                        $countLabel = 'Total Issues';
                                    } elseif (!empty($results ?? [])) {
                                        // If we have results, count them
                                        $totalCount = count($results);
                                        $countLabel = 'Processed';
                                    } elseif (($fixedCount ?? 0) > 0 || ($failedCount ?? 0) > 0) {
                                        // If we have some counts, add them up
                                        $totalCount = ($fixedCount ?? 0) + ($failedCount ?? 0);
                                        $countLabel = 'Processed';
                                    } else {
                                        // Try to get unfixed issues count from database
                                        try {
                                            $totalCount = \Rafaelogic\CodeSnoutr\Models\Issue::where('fixed', false)->count();
                                            $countLabel = 'Pending Issues';
                                        } catch (\Exception $e) {
                                            $totalCount = 0;
                                            $countLabel = 'Total Issues';
                                        }
                                    }
                                @endphp
                                <div class="text-3xl font-bold text-gray-700 dark:text-gray-200 mb-1">{{ $totalCount }}</div>
                                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $countLabel }}</div>
                            </div>
                            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-900/40 rounded-xl flex items-center justify-center">
                                <x-atoms.icon name="list" size="lg" class="text-gray-600 dark:text-gray-300" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-atoms.surface>

        <!-- Start Fix All Button (if not started) -->
        @if(in_array($status ?? 'idle', ['idle', 'initializing']))
            <x-atoms.surface variant="default" padding="lg" rounded="lg" shadow="sm">
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-2xl flex items-center justify-center mb-6">
                        <x-atoms.icon name="bolt" size="xl" class="text-blue-600 dark:text-blue-400" />
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                        Ready to Fix All Issues
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                        Start the AI-powered batch fixing process to automatically resolve code issues across your project.
                    </p>
                    
                    <x-atoms.button 
                        wire:click="startFixAll"
                        type="button"
                        variant="primary"
                        size="lg"
                        icon="bolt"
                        :pulse="true"
                        rounded="lg"
                        class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-700 hover:via-indigo-700 hover:to-purple-700 focus:ring-4 focus:ring-indigo-500 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>Start Fix All Process</span>
                        <span wire:loading>Starting Process...</span>
                    </x-atoms.button>
                    
                    <!-- Debug buttons -->
                    <x-atoms.surface variant="bordered" padding="default" rounded="lg" class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800">
                        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-3">Debug Tools</h4>
                        <div class="flex flex-wrap gap-2">
                            <x-atoms.button 
                                wire:click="testStatusUpdate"
                                variant="outline-secondary"
                                size="sm"
                                class="border-yellow-300 dark:border-yellow-600 text-yellow-700 dark:text-yellow-200 bg-white dark:bg-yellow-800/20 hover:bg-yellow-50 dark:hover:bg-yellow-700/30"
                            >
                                Test Status Update
                            </x-atoms.button>
                            
                            <x-atoms.button 
                                wire:click="startFixAllSync"
                                variant="outline-secondary"
                                size="sm"
                                class="border-yellow-300 dark:border-yellow-600 text-yellow-700 dark:text-yellow-200 bg-white dark:bg-yellow-800/20 hover:bg-yellow-50 dark:hover:bg-yellow-700/30"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove>Run Sync (Debug)</span>
                                <span wire:loading>Running...</span>
                            </x-atoms.button>
                            
                            <x-atoms.button 
                                wire:click="checkQueueConfig"
                                variant="outline-secondary"
                                size="sm"
                                class="border-yellow-300 dark:border-yellow-600 text-yellow-700 dark:text-yellow-200 bg-white dark:bg-yellow-800/20 hover:bg-yellow-50 dark:hover:bg-yellow-700/30"
                            >
                                Check Queue Config
                            </x-atoms.button>
                        </div>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-2">These buttons help debug job execution issues. Check browser console and Laravel logs.</p>
                    </x-atoms.surface>
                </div>
            </x-atoms.surface>
        @endif

        <!-- Results List -->
        <x-codesnoutr::molecules.fix-all-results 
            :results="$results ?? []"
        />

        <!-- Action Buttons for Completed State -->
        @if(in_array($status ?? 'idle', ['completed', 'failed', 'stopped']))
            <x-atoms.surface variant="default" padding="default" rounded="lg" shadow="sm">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <x-atoms.button 
                        wire:click="clearResults"
                        variant="outline-secondary"
                        size="lg"
                        icon="refresh"
                        class="w-full sm:w-auto"
                    >
                        Start New Session
                    </x-atoms.button>
                    
                    <x-atoms.button 
                        href="{{ route('codesnoutr.dashboard') }}"
                        variant="primary"
                        size="lg"
                        icon="home"
                        class="w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-lg hover:shadow-xl"
                    >
                        Return to Dashboard
                    </x-atoms.button>
                </div>
            </x-atoms.surface>
        @endif
        </div>

        @push('scripts')
        <script>
        document.addEventListener('livewire:init', () => {
            console.log('Livewire Fix All Progress initialized');
            console.log('Session ID:', '{{ $sessionId }}');
            console.log('Initial status:', '{{ $status ?? "idle" }}');
            console.log('wire:poll should be active for statuses: processing, starting');
            
            // Track polling activity
            let pollCount = 0;
            let lastPollTime = Date.now();
            let lastValues = {
                status: '{{ $status ?? "idle" }}',
                currentStep: {{ $currentStep ?? 0 }},
                totalSteps: {{ $totalSteps ?? 0 }},
                fixedCount: {{ $fixedCount ?? 0 }},
                failedCount: {{ $failedCount ?? 0 }}
            };
            
            // Auto-scroll to bottom of results when new ones are added
            const resultsContainer = document.querySelector('.max-h-96.overflow-y-auto');
            if (resultsContainer) {
                const observer = new MutationObserver(() => {
                    resultsContainer.scrollTop = resultsContainer.scrollHeight;
                });
                observer.observe(resultsContainer, { childList: true, subtree: true });
            }
            
            // Handle show-notification event
            Livewire.on('show-notification', (data) => {
                const notification = Array.isArray(data) ? data[0] : data;
                const type = notification?.type || 'info';
                const message = notification?.message || 'Notification';
                
                console.log('Show notification:', type, message);
                
                // Show alert based on type
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: type === 'error' ? 'Error' : type === 'warning' ? 'Warning' : 'Notice',
                        html: message,
                        icon: type === 'error' ? 'error' : type === 'warning' ? 'warning' : 'info',
                        confirmButtonText: 'Got it',
                        width: 600
                    });
                } else {
                    // Fallback to browser alert
                    alert(message);
                }
                
                // Also show browser notification if permitted
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('CodeSnoutr - ' + (type === 'error' ? 'Error' : 'Notice'), {
                        body: message,
                        icon: '/favicon.ico'
                    });
                }
            });
            
            // Handle queue setup errors
            Livewire.on('queue-setup-error', (data) => {
                console.error('Queue setup error:', data);
                
                let recommendationsHtml = '';
                if (data[0]?.recommendations && data[0].recommendations.length > 0) {
                    recommendationsHtml = '<ul class="list-disc list-inside mt-2 text-sm">';
                    data[0].recommendations.forEach(rec => {
                        recommendationsHtml += `<li class="mt-1">${rec}</li>`;
                    });
                    recommendationsHtml += '</ul>';
                }
                
                // Show alert modal or notification
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Queue Not Ready',
                        html: `<p>${data[0]?.message || 'Queue configuration issue detected'}</p>${recommendationsHtml}`,
                        icon: 'error',
                        confirmButtonText: 'Got it',
                        width: 600
                    });
                } else {
                    // Fallback to browser alert
                    alert(`Queue Not Ready:\n\n${data[0]?.message || 'Queue configuration issue detected'}\n\nRecommendations:\n${data[0]?.recommendations?.join('\n') || 'Check documentation'}`);
                }
            });
            
            // Handle stopping event
            Livewire.on('fix-all-stopping', () => {
                console.log('Fix All process is stopping...');
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('CodeSnoutr', {
                        body: 'Fix All process is stopping...',
                        icon: '/favicon.ico'
                    });
                }
            });
            
            // Handle queue worker warning
            Livewire.on('show-queue-warning', (data) => {
                console.warn('Queue worker warning:', data);
                
                // Show persistent warning notification
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '⚠️ Queue Worker Not Running',
                        html: `
                            <div class="text-left">
                                <p class="mb-4">${data[0]?.message || 'Job is queued but not processing'}</p>
                                <div class="bg-gray-100 p-4 rounded-lg">
                                    <p class="font-bold mb-2">To start the queue worker:</p>
                                    <ol class="list-decimal list-inside space-y-2 text-sm">
                                        <li>Open a new terminal window</li>
                                        <li>Navigate to your Laravel app</li>
                                        <li>Run: <code class="bg-white px-2 py-1 rounded font-mono text-xs">php artisan queue:work</code></li>
                                        <li>Keep terminal open</li>
                                    </ol>
                                </div>
                                <p class="mt-4 text-xs text-gray-600">Or press F12 to see console for more details</p>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Got it',
                        confirmButtonColor: '#f59e0b',
                        cancelButtonText: 'View Docs',
                        width: 600
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.cancel) {
                            window.open('https://laravel.com/docs/queues#running-the-queue-worker', '_blank');
                        }
                    });
                } else {
                    // Fallback alert
                    alert(`⚠️ Queue Worker Not Running!\n\n${data[0]?.message}\n\n${data[0]?.action}`);
                }
            });
            
            // Track wire:poll refreshProgress calls
            Livewire.hook('morph.updated', ({ el, component }) => {
                if (component.name === 'fix-all-progress') {
                    pollCount++;
                    const now = Date.now();
                    const timeSinceLastPoll = now - lastPollTime;
                    lastPollTime = now;
                    
                    // Get current values from component
                    const currentValues = {
                        status: component.get('status'),
                        currentStep: component.get('currentStep'),
                        totalSteps: component.get('totalSteps'),
                        fixedCount: component.get('fixedCount'),
                        failedCount: component.get('failedCount')
                    };
                    
                    // Detect changes
                    const changes = {
                        status: lastValues.status !== currentValues.status,
                        currentStep: lastValues.currentStep !== currentValues.currentStep,
                        totalSteps: lastValues.totalSteps !== currentValues.totalSteps,
                        fixedCount: lastValues.fixedCount !== currentValues.fixedCount,
                        failedCount: lastValues.failedCount !== currentValues.failedCount
                    };
                    
                    const hasChanges = Object.values(changes).some(changed => changed);
                    
                    console.log(`[wire:poll #${pollCount}] ${hasChanges ? '✅ CHANGES DETECTED' : '⏸️ No changes'}`, {
                        pollCount,
                        timeSinceLastPoll: `${timeSinceLastPoll}ms`,
                        hasChanges,
                        changes,
                        oldValues: lastValues,
                        newValues: currentValues
                    });
                    
                    // Log significant changes in detail
                    if (changes.currentStep) {
                        console.log(`📊 Progress Update: ${lastValues.currentStep}/${lastValues.totalSteps} → ${currentValues.currentStep}/${currentValues.totalSteps}`);
                    }
                    if (changes.fixedCount) {
                        console.log(`✅ Fixed Count: ${lastValues.fixedCount} → ${currentValues.fixedCount} (+${currentValues.fixedCount - lastValues.fixedCount})`);
                    }
                    if (changes.failedCount) {
                        console.log(`❌ Failed Count: ${lastValues.failedCount} → ${currentValues.failedCount} (+${currentValues.failedCount - lastValues.failedCount})`);
                    }
                    if (changes.status) {
                        console.log(`🔄 Status Changed: ${lastValues.status} → ${currentValues.status}`);
                    }
                    
                    // Update last values
                    lastValues = currentValues;
                }
            });
            
            // Enhanced event handling
            Livewire.on('status-changed', (status) => {
                console.log('🔔 Status changed event fired:', status);
                if (status === 'completed') {
                    console.log('Fix All process completed!');
                    if ('Notification' in window && Notification.permission === 'granted') {
                        new Notification('CodeSnoutr', {
                            body: 'Fix All process completed!',
                            icon: '/favicon.ico'
                        });
                    }
                } else if (status === 'stopped') {
                    console.log('Fix All process stopped by user');
                    if ('Notification' in window && Notification.permission === 'granted') {
                        new Notification('CodeSnoutr', {
                            body: 'Fix All process stopped',
                            icon: '/favicon.ico'
                        });
                    }
                }
            });
            
            // Handle Livewire errors
            document.addEventListener('livewire:error', (event) => {
                console.error('Livewire error:', event.detail);
            });
            
            // Debug button clicks
            const startButton = document.querySelector('[wire\\:click="startFixAll"]');
            if (startButton) {
                startButton.addEventListener('click', function() {
                    console.log('🚀 Start Fix All button clicked');
                    pollCount = 0;
                    lastPollTime = Date.now();
                });
            }
            
            // Log polling summary every 10 seconds
            setInterval(() => {
                if (pollCount > 0) {
                    try {
                        const status = @this.get('status');
                        const currentStep = @this.get('currentStep');
                        const totalSteps = @this.get('totalSteps');
                        const fixedCount = @this.get('fixedCount');
                        const failedCount = @this.get('failedCount');
                        const isPollActive = ['processing', 'starting'].includes(status);
                        
                        console.log('📊 Polling Summary (last 10s):', {
                            totalPolls: pollCount,
                            currentStatus: status,
                            pollingShouldBeActive: isPollActive,
                            progress: `${currentStep}/${totalSteps}`,
                            fixed: fixedCount,
                            failed: failedCount,
                            lastUpdate: new Date(lastPollTime).toLocaleTimeString()
                        });
                    } catch (e) {
                        console.warn('⚠️ Could not get component data for summary:', e.message);
                    }
                }
            }, 10000);
        });
        
        // Request notification permission on page load
        document.addEventListener('DOMContentLoaded', () => {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        });
        </script>
        @endpush
    </div>
</div>