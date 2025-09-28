<!-- Step 2: Target Selection -->
<div class="space-y-6">
    <div class="text-center mb-8">
        <x-atoms.text as="h3" size="lg" weight="medium" class="mb-2">
            @if($scanType === 'file') Select File to Analyze
            @elseif($scanType === 'directory') Choose Directory to Scan
            @else Codebase Location
            @endif
        </x-atoms.text>
        <x-atoms.text color="muted">
            @if($scanType === 'file') Browse and select a specific PHP file to analyze
            @elseif($scanType === 'directory') Choose the directory containing files you want to scan
            @else Your entire Laravel application will be analyzed
            @endif
        </x-atoms.text>
    </div>

    @if($scanType === 'codebase')
    <!-- Codebase Scan Info -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
        <div class="flex items-center">
            <div class="h-12 w-12 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="ml-4 flex-1">
                <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100">Full Application Analysis</h4>
                <p class="text-blue-700 dark:text-blue-300 text-sm">We'll analyze your entire Laravel application located at:</p>
                <p class="text-blue-800 dark:text-blue-200 font-mono text-sm mt-1 break-all">{{ base_path() }}</p>
            </div>
        </div>
        
        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">4</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Analysis Types</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">50+</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Quality Rules</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">∞</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Files Scanned</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">⚡</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Background</div>
            </div>
        </div>
    </div>
    @else
    <!-- File/Directory Selection -->
    <div class="space-y-4">
        <x-molecules.form-field 
            :label="$scanType === 'file' ? 'File Path' : 'Directory Path'"
            for="target-input"
        >
            <div class="flex space-x-3">
                <div class="flex-1">
                    <x-atoms.input 
                        wire:model.live="target" 
                        id="target-input"
                        name="target"
                        :placeholder="$scanType === 'file' ? 'e.g., app/Http/Controllers/UserController.php' : 'e.g., app/Http/Controllers'"
                        size="lg"
                    />
                </div>
                <x-atoms.button 
                    type="button"
                    wire:click="browseForPath"
                    variant="primary"
                    size="lg"
                    icon="folder"
                >
                    Browse
                </x-atoms.button>
            </div>
        </x-molecules.form-field>

        @error('target')
        <x-atoms.alert variant="danger" size="md" class="mt-4">
            <x-slot name="icon">x-circle</x-slot>
            {{ $message }}
        </x-atoms.alert>
        @enderror

        @if($target && !$errors->has('target'))
        <x-atoms.alert variant="success" size="md" class="mt-4">
            <x-slot name="icon">check-circle</x-slot>
            <x-slot name="title">Target Selected</x-slot>
            <x-atoms.text class="font-mono text-sm">{{ $target }}</x-atoms.text>
        </x-atoms.alert>
        @endif

        <!-- Quick Suggestions -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Quick Suggestions</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @if($scanType === 'directory')
                <button type="button" 
                        wire:click="$set('target', 'app/Http/Controllers')"
                        class="text-left p-3 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors">
                    <div class="font-medium text-sm text-gray-900 dark:text-white">Controllers</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">app/Http/Controllers</div>
                </button>
                <button type="button" 
                        wire:click="$set('target', 'app/Models')"
                        class="text-left p-3 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors">
                    <div class="font-medium text-sm text-gray-900 dark:text-white">Models</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">app/Models</div>
                </button>
                <button type="button" 
                        wire:click="$set('target', 'app/Services')"
                        class="text-left p-3 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors">
                    <div class="font-medium text-sm text-gray-900 dark:text-white">Services</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">app/Services</div>
                </button>
                <button type="button" 
                        wire:click="$set('target', 'resources/views')"
                        class="text-left p-3 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors">
                    <div class="font-medium text-sm text-gray-900 dark:text-white">Views</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">resources/views</div>
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
