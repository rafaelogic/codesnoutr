<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">CodeSnoutr</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Advanced Code Analysis</p>
                    </div>
                </div>
                
                <!-- Progress Steps -->
                <div class="hidden md:flex items-center space-x-4">
                    @for($i = 1; $i <= $totalSteps; $i++)
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium
                            @if($i < $currentStep) bg-indigo-600 text-white
                            @elseif($i == $currentStep) bg-indigo-100 text-indigo-600 ring-2 ring-indigo-600
                            @else bg-gray-200 text-gray-500 @endif">
                            @if($i < $currentStep)
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                {{ $i }}
                            @endif
                        </div>
                        
                        @if($i < $totalSteps)
                        <div class="w-12 h-0.5 ml-2 
                            @if($i < $currentStep) bg-indigo-600 
                            @else bg-gray-200 @endif">
                        </div>
                        @endif
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @if($currentStep < 5)
        <!-- Step Content -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Step Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-white">
                            @if($currentStep == 1) Choose Scan Type
                            @elseif($currentStep == 2) Select Target
                            @elseif($currentStep == 3) Configure Rules
                            @elseif($currentStep == 4) Review & Start
                            @elseif($currentStep == 5) Scan Progress
                            @endif
                        </h2>
                        <p class="text-indigo-100 text-sm mt-1">
                            @if($currentStep == 1) What would you like to analyze?
                            @elseif($currentStep == 2) Specify the files or directories to scan
                            @elseif($currentStep == 3) Choose which analysis rules to apply
                            @elseif($currentStep == 4) Review your settings and start the scan
                            @elseif($currentStep == 5) Monitor your scan progress
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-indigo-100 text-sm">Step {{ $currentStep }} of {{ $totalSteps }}</div>
                        <div class="w-24 bg-indigo-800 rounded-full h-2 mt-1">
                            <div class="bg-white h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ ($currentStep / $totalSteps) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step Content -->
            <div class="p-8">
                @if($currentStep == 1)
                    @include('codesnoutr::components.wizard.step-scan-type')
                @elseif($currentStep == 2)
                    @include('codesnoutr::components.wizard.step-target-selection')
                @elseif($currentStep == 3)
                    @include('codesnoutr::components.wizard.step-rule-categories')
                @elseif($currentStep == 4)
                    @include('codesnoutr::components.wizard.step-review-start')
                @elseif($currentStep == 5)
                    @include('codesnoutr::components.wizard.step-progress')
                @endif
            </div>

            <!-- Navigation -->
            @if($currentStep < 5)
            <div class="bg-gray-50 dark:bg-gray-700 px-8 py-4 flex justify-between items-center">
                <button wire:click="previousStep" 
                        @if($currentStep == 1) disabled @endif
                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border 
                               @if($currentStep == 1) 
                                   bg-gray-100 text-gray-400 border-gray-300 cursor-not-allowed
                               @else 
                                   bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500
                               @endif
                               transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Previous
                </button>

                <div class="flex space-x-2">
                    @for($i = 1; $i <= $totalSteps; $i++)
                    <button wire:click="goToStep({{ $i }})" 
                            class="w-3 h-3 rounded-full transition-colors
                                   @if($i == $currentStep) bg-indigo-600
                                   @elseif($i < $currentStep) bg-indigo-400
                                   @else bg-gray-300 @endif">
                    </button>
                    @endfor
                </div>

                @if($currentStep < 4)
                <button wire:click="nextStep" 
                        class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    Next
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                @endif
            </div>
            @endif
        </div>
        @else
        <!-- Scanning Progress -->
        @include('codesnoutr::components.wizard.step-progress')
        @endif
    </div>

    <!-- File Browser Modal -->
    @if($showFileBrowser)
    @include('codesnoutr::components.wizard.file-browser-modal')
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    let progressInterval;
    
    Livewire.on('start-progress-polling', (data) => {
        console.log('Raw event data:', data);
        
        // Handle different ways Livewire might pass the data
        let scanId;
        if (Array.isArray(data) && data.length > 0) {
            scanId = data[0].scanId || data[0];
        } else if (data && typeof data === 'object') {
            scanId = data.scanId;
        } else {
            scanId = data;
        }
        
        console.log('Starting progress polling for scan:', scanId);
        
        if (!scanId) {
            console.error('No scan ID provided for progress polling');
            return;
        }
        
        progressInterval = setInterval(() => {
            @this.refreshProgress();
        }, 2000); // Poll every 2 seconds
    });
    
    Livewire.on('stop-progress-polling', () => {
        console.log('Stopping progress polling');
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
    });
    
    Livewire.on('scan-completed', (data) => {
        console.log('Raw completion data:', data);
        
        // Handle different ways Livewire might pass the data
        let scanId;
        if (Array.isArray(data) && data.length > 0) {
            scanId = data[0].scanId || data[0];
        } else if (data && typeof data === 'object') {
            scanId = data.scanId;
        } else {
            scanId = data;
        }
        
        console.log('Scan completed:', scanId);
        
        if (scanId) {
            // Show success message or redirect
            setTimeout(() => {
                window.location.href = '/codesnoutr/results/' + scanId;
            }, 2000);
        }
    });
});
</script>
@endpush
