@props([
    'fixedCount' => 0,
    'failedCount' => 0,
    'totalSteps' => 0,
])

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <x-codesnoutr::molecules.stat-card
        title="Fixed"
        :value="$fixedCount"
        variant="success"
        icon="check-circle"
    />
    
    <x-codesnoutr::molecules.stat-card
        title="Failed"
        :value="$failedCount"
        variant="danger"
        icon="x-circle"
    />
    
    <x-codesnoutr::molecules.stat-card
        title="Total"
        :value="$totalSteps"
        variant="secondary"
        icon="collection"
    />
</div>