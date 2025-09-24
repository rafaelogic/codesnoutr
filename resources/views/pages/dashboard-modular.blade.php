@extends('codesnoutr::templates.app-layout')

@section('title', 'Dashboard')

@section('content')
    <x-molecules.page-header
        title="Dashboard"
        description="Monitor your code quality metrics and recent scan activity"
    >
        <x-slot name="action">
            <x-atoms.button 
                href="{{ route('codesnoutr.wizard') }}" 
                icon="plus"
            >
                New Scan
            </x-atoms.button>
        </x-slot>
    </x-molecules.page-header>

    <x-atoms.container spacing="lg">
        <x-atoms.stack spacing="xl">
            <!-- Metrics Overview -->
            @livewire('codesnoutr-dashboard-metrics')
            
            <!-- Recent Activity & Quick Actions -->
            <x-atoms.grid columns="2" gap="lg">
                <div>
                    @livewire('codesnoutr-dashboard-activity')
                </div>
                
                <div>
                    <x-atoms.surface>
                        <x-molecules.card-header title="Quick Actions" />
                        <x-molecules.card-body>
                            <x-atoms.stack spacing="sm">
                                <x-atoms.button 
                                    href="{{ route('codesnoutr.wizard') }}" 
                                    variant="primary"
                                    fullWidth="true"
                                    icon="search"
                                >
                                    Start New Scan
                                </x-atoms.button>
                                
                                <x-atoms.button 
                                    href="{{ route('codesnoutr.results') }}" 
                                    variant="secondary"
                                    fullWidth="true"
                                    icon="document-report"
                                >
                                    View All Results
                                </x-atoms.button>
                                
                                <x-atoms.button 
                                    href="{{ route('codesnoutr.settings') }}" 
                                    variant="ghost"
                                    fullWidth="true"
                                    icon="cog"
                                >
                                    Settings
                                </x-atoms.button>
                            </x-atoms.stack>
                        </x-molecules.card-body>
                    </x-atoms.surface>
                </div>
            </x-atoms.grid>
        </x-atoms.stack>
    </x-atoms.container>
@endsection