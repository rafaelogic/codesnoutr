<x-atoms.container>
    <x-atoms.grid columns="4" gap="lg">
        <x-molecules.metric-card
            title="Total Scans"
            :value="$metrics['total_scans']['value']"
            :change="$metrics['total_scans']['change']"
            :changeType="$metrics['total_scans']['changeType']"
            icon="search"
            color="blue"
        />
        
        <x-molecules.metric-card
            title="Total Issues"
            :value="$metrics['total_issues']['value']"
            icon="exclamation-triangle"
            color="yellow"
        />
        
        <x-molecules.metric-card
            title="Critical Issues"
            :value="$metrics['critical_issues']['value']"
            icon="shield-exclamation"
            color="red"
        />
        
        <x-molecules.metric-card
            title="Resolution Rate"
            :value="$metrics['resolution_rate']['value']"
            icon="check-circle"
            color="green"
        />
    </x-atoms.grid>
</x-atoms.container>