<!-- Success Celebration Component -->
<div class="text-center py-16 px-4 sm:px-6 lg:px-8" x-data="{ showConfetti: true }" x-init="showConfetti = true">
    <!-- Confetti Animation -->
    <div x-show="showConfetti" x-transition:enter="transition ease-out duration-1000" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="fixed inset-0 pointer-events-none z-50">
        <div class="confetti-container">
            <!-- Confetti pieces -->
            @for ($i = 0; $i < 50; $i++)
            <div class="confetti confetti-{{ $i % 6 + 1 }}" style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 3000) }}ms; animation-duration: {{ rand(2000, 4000) }}ms;"></div>
            @endfor
        </div>
    </div>

    <!-- Success Icon -->
    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gradient-to-r from-green-400 to-emerald-500 mb-6 shadow-lg animate-bounce">
        <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    </div>

    <!-- Main Message -->
    <div class="mb-8">
        <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4 animate-pulse">
            🎉 Congratulations! 🎉
        </h2>
        <p class="text-xl text-green-600 dark:text-green-400 font-semibold mb-2">
            Your code is squeaky clean!
        </p>
        <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            We've thoroughly scanned your codebase and found <strong class="text-green-600 dark:text-green-400">zero issues</strong>. 
            Your code quality is exceptional! 🏆
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-2 border-green-200 dark:border-green-700">
            <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">{{ $scan->total_files ?? 'N/A' }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Files Scanned</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-2 border-green-200 dark:border-green-700">
            <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">0</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Issues Found</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-2 border-green-200 dark:border-green-700">
            <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">100%</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Quality Score</div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
        <a href="{{ route('codesnoutr.dashboard') }}" 
           class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 7 5-5 5 5"></path>
            </svg>
            Back to Dashboard
        </a>
        
        <button wire:click="$emit('startNewScan')" 
                class="inline-flex items-center px-6 py-3 border-2 border-green-500 text-base font-medium rounded-lg text-green-600 dark:text-green-400 bg-transparent hover:bg-green-50 dark:hover:bg-green-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            Run Another Scan
        </button>
    </div>

    <!-- Fun Messages -->
    <div class="max-w-2xl mx-auto" style="margin-top: 3rem;">
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-6 border border-green-200 dark:border-green-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">🌟 Keep Up the Great Work!</h3>
            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                <p>• Your code follows best practices</p>
                <p>• No security vulnerabilities detected</p>
                <p>• Performance looks optimal</p>
                <p>• Code quality standards are met</p>
            </div>
        </div>
    </div>
</div>

<!-- Confetti CSS Animation -->
<style>
@keyframes confetti-fall {
    0% {
        transform: translateY(-100vh) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(720deg);
        opacity: 0;
    }
}

.confetti-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.confetti {
    position: absolute;
    top: -10px;
    width: 10px;
    height: 10px;
    animation: confetti-fall linear infinite;
}

.confetti-1 { background: #f43f5e; }
.confetti-2 { background: #10b981; }
.confetti-3 { background: #3b82f6; }
.confetti-4 { background: #f59e0b; }
.confetti-5 { background: #8b5cf6; }
.confetti-6 { background: #06b6d4; }

/* Make confetti pieces different shapes */
.confetti:nth-child(odd) {
    border-radius: 50%;
}

.confetti:nth-child(even) {
    transform: rotate(45deg);
}

/* Responsive animations */
@media (prefers-reduced-motion: reduce) {
    .confetti, .animate-bounce, .animate-pulse {
        animation: none;
    }
}
</style>