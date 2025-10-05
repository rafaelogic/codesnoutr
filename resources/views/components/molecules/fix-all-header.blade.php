@props([
    'status' => 'initializing',
    'sessionId' => 'N/A',
    'startedAt' => null,
    'completedAt' => null,
])

@php
    $statusColor = match($status) {
        'completed' => 'green',
        'failed' => 'red', 
        'processing' => 'blue',
        'starting' => 'yellow',
        default => 'gray'
    };
    
    $statusIcon = match($status) {
        'completed' => 'check-circle',
        'failed' => 'x-circle',
        'processing' => 'cog', 
        'starting' => 'play',
        default => 'clock'
    };
@endphp

<div class="flex items-center space-x-4">
    <div class="w-12 h-12 bg-{{ $statusColor }}-100 dark:bg-{{ $statusColor }}-600/30 rounded-xl flex items-center justify-center">
        <x-codesnoutr::atoms.icon :name="$statusIcon" class="w-6 h-6 text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400 {{ $status === 'processing' ? 'animate-spin' : '' }}" />
    </div>
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Fix All Issues with AI
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            AI-powered batch issue fixing in progress • Session: {{ Str::limit($sessionId, 8) }}
            @if($startedAt)
                @php
                    $start = \Carbon\Carbon::parse($startedAt);
                    $end = $completedAt ? \Carbon\Carbon::parse($completedAt) : now();
                    $elapsedTime = $start->diff($end)->format('%H:%I:%S');
                @endphp
                • Elapsed: {{ $elapsedTime }}
            @endif
        </p>
    </div>
</div>