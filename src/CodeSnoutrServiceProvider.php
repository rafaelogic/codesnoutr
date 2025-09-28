<?php

namespace Rafaelogic\CodeSnoutr;

use Illuminate\Support\ServiceProvider;
use Rafaelogic\CodeSnoutr\ScanManager;
use Rafaelogic\CodeSnoutr\Commands\ScanCommand;
use Rafaelogic\CodeSnoutr\Commands\InstallCommand;
use Rafaelogic\CodeSnoutr\Commands\AssetStatusCommand;
use Rafaelogic\CodeSnoutr\Commands\UpdateResolvedIssuesCounts;

// New service imports
use Rafaelogic\CodeSnoutr\Services\AI\{AiFixGenerator, AiAssistantService, AutoFixService};
use Rafaelogic\CodeSnoutr\Services\Issues\{
    IssueFilterService,
    IssueExportService,
    BulkActionService,
    IssueActionInvoker
};
use Rafaelogic\CodeSnoutr\Services\UI\CodeDisplayService;
use Rafaelogic\CodeSnoutr\Services\Scanning\ScanResultsViewService;

// New AI and UI services
use Rafaelogic\CodeSnoutr\Services\AI\{ConversationService, SuggestionService};
use Rafaelogic\CodeSnoutr\Services\UI\AssistantStateService;
use Rafaelogic\CodeSnoutr\Services\Wizard\{StepNavigationService, FileBrowserService, ScanExecutionService, ScanConfigurationService, WizardAiService};
use Rafaelogic\CodeSnoutr\Contracts\AI\{ConversationServiceInterface, SuggestionServiceInterface};
use Rafaelogic\CodeSnoutr\Contracts\UI\AssistantStateServiceInterface;
use Rafaelogic\CodeSnoutr\Contracts\Services\Wizard\{StepNavigationServiceContract, FileBrowserServiceContract, ScanExecutionServiceContract, ScanConfigurationServiceContract, WizardAiServiceContract};

use Rafaelogic\CodeSnoutr\Actions\IssueActions\{
    ResolveIssueAction,
    IgnoreIssueAction,
    MarkFalsePositiveAction,
    GenerateAiFixAction,
    ApplyAiFixAction
};

class CodeSnoutrServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/codesnoutr.php', 'codesnoutr'
        );

        // Register the main scanner service
        $this->app->singleton('codesnoutr', function ($app) {
            return new ScanManager($app);
        });

        // Register AI Assistant service
        $this->app->singleton('codesnoutr.ai', function ($app) {
            return new AiAssistantService();
        });

        // Register AI Fix service
        $this->app->singleton('codesnoutr.autofix', function ($app) {
            return new AutoFixService($app->make('codesnoutr.ai'));
        });

        // Register new service layer
        $this->registerNewServices();
    }

    /**
     * Register the new refactored services
     */
    protected function registerNewServices(): void
    {
        // Core services
        $this->app->singleton(AiFixGenerator::class);
        $this->app->singleton(IssueFilterService::class);
        $this->app->singleton(IssueExportService::class);
        $this->app->singleton(BulkActionService::class);
        $this->app->singleton(CodeDisplayService::class);
        $this->app->singleton(ScanResultsViewService::class);

        // Register AI services with contracts
        $this->app->singleton(ConversationServiceInterface::class, ConversationService::class);
        $this->app->singleton(SuggestionServiceInterface::class, SuggestionService::class);
        
        // Register UI services with contracts
        $this->app->singleton(AssistantStateServiceInterface::class, AssistantStateService::class);

        // Register Wizard services with contracts
        $this->app->singleton(StepNavigationServiceContract::class, StepNavigationService::class);
        $this->app->singleton(FileBrowserServiceContract::class, FileBrowserService::class);
        $this->app->singleton(ScanExecutionServiceContract::class, ScanExecutionService::class);
        $this->app->singleton(ScanConfigurationServiceContract::class, ScanConfigurationService::class);
        $this->app->singleton(WizardAiServiceContract::class, WizardAiService::class);

        // Issue Actions
        // Register Action classes
        $this->app->singleton(\Rafaelogic\CodeSnoutr\Actions\IssueActions\ResolveIssueAction::class);
        $this->app->singleton(\Rafaelogic\CodeSnoutr\Actions\IssueActions\IgnoreIssueAction::class);
        $this->app->singleton(\Rafaelogic\CodeSnoutr\Actions\IssueActions\MarkFalsePositiveAction::class);
        $this->app->singleton(\Rafaelogic\CodeSnoutr\Actions\IssueActions\GenerateAiFixAction::class);
        $this->app->singleton(\Rafaelogic\CodeSnoutr\Actions\IssueActions\ApplyAiFixAction::class);
        $this->app->singleton(IssueActionInvoker::class, function ($app) {
            return new IssueActionInvoker(
                $app->make(ResolveIssueAction::class),
                $app->make(IgnoreIssueAction::class),
                $app->make(MarkFalsePositiveAction::class),
                $app->make(GenerateAiFixAction::class),
                $app->make(ApplyAiFixAction::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/codesnoutr.php' => config_path('codesnoutr.php'),
        ], 'codesnoutr-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'codesnoutr-migrations');

        // Publish assets to public directory for direct access
        $this->publishes([
            __DIR__.'/../resources/css/codesnoutr.css' => public_path('vendor/codesnoutr/css/codesnoutr.css'),
            __DIR__.'/../resources/js/codesnoutr.js' => public_path('vendor/codesnoutr/js/codesnoutr.js'),
            __DIR__.'/../resources/images/codesnoutr-icon.svg' => public_path('vendor/codesnoutr/images/codesnoutr-icon.svg'),
            __DIR__.'/../public/build' => public_path('vendor/codesnoutr/build'),
        ], 'codesnoutr-assets');

        // Publish views for customization
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/codesnoutr'),
        ], 'codesnoutr-views');

        // Publish all resources in one command
        $this->publishes([
            __DIR__.'/../resources/css/codesnoutr.css' => public_path('vendor/codesnoutr/css/codesnoutr.css'),
            __DIR__.'/../resources/js/codesnoutr.js' => public_path('vendor/codesnoutr/js/codesnoutr.js'),
            __DIR__.'/../resources/images/codesnoutr-icon.svg' => public_path('vendor/codesnoutr/images/codesnoutr-icon.svg'),
            __DIR__.'/../resources/views' => resource_path('views/vendor/codesnoutr'),
            __DIR__.'/../public/build' => public_path('vendor/codesnoutr/build'),
        ], 'codesnoutr-resources');

        // Publish routes (optional - for custom integration)
        $this->publishes([
            __DIR__.'/../routes/web.php' => base_path('routes/codesnoutr.php'),
        ], 'codesnoutr-routes');

        // Publish integration guide and troubleshooting
        $this->publishes([
            __DIR__.'/../ROUTE_INTEGRATION.md' => base_path('docs/codesnoutr-integration.md'),
            __DIR__.'/../ROUTE_TROUBLESHOOTING.md' => base_path('docs/codesnoutr-troubleshooting.md'),
            __DIR__.'/../CSRF_TROUBLESHOOTING.md' => base_path('docs/codesnoutr-csrf-troubleshooting.md'),
        ], 'codesnoutr-docs');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes (only if not published or config allows it)
        // Defer route loading until after the kernel has booted to ensure middleware is available
        if (config('codesnoutr.auto_load_routes', true)) {
            $this->booted(function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
        }

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'codesnoutr');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ScanCommand::class,
                InstallCommand::class,
                AssetStatusCommand::class,
                UpdateResolvedIssuesCounts::class,
            ]);
        }

        // Register Livewire components
        $this->registerLivewireComponents();

        // Register Blade components
        $this->registerBladeComponents();

        // Register debugbar collector if debugbar is installed
        $this->registerDebugbarCollector();
    }

    /**
     * Register Livewire components
     */
    protected function registerLivewireComponents(): void
    {
        if (class_exists(\Livewire\Livewire::class)) {
            // Register CodeSnoutr Livewire components
            
            \Livewire\Livewire::component('codesnoutr-dashboard', \Rafaelogic\CodeSnoutr\Livewire\Dashboard::class);
            \Livewire\Livewire::component('codesnoutr-dashboard-metrics', \Rafaelogic\CodeSnoutr\Livewire\Dashboard\MetricsOverview::class);
            \Livewire\Livewire::component('codesnoutr-dashboard-activity', \Rafaelogic\CodeSnoutr\Livewire\Dashboard\RecentActivity::class);
            \Livewire\Livewire::component('codesnoutr-scan-form', \Rafaelogic\CodeSnoutr\Livewire\ScanForm::class);
            \Livewire\Livewire::component('codesnoutr-scan-wizard', \Rafaelogic\CodeSnoutr\Livewire\ScanWizard::class);
            \Livewire\Livewire::component('codesnoutr-scan-results', \Rafaelogic\CodeSnoutr\Livewire\ScanResults::class);
            \Livewire\Livewire::component('scan-results', \Rafaelogic\CodeSnoutr\Livewire\ScanResults::class);
            \Livewire\Livewire::component('codesnoutr-scan-results-view', \Rafaelogic\CodeSnoutr\Livewire\ScanResultsView::class);
            \Livewire\Livewire::component('codesnoutr-simple-scan-results', \Rafaelogic\CodeSnoutr\Livewire\SimpleScanResults::class);
            \Livewire\Livewire::component('codesnoutr-settings', \Rafaelogic\CodeSnoutr\Livewire\Settings::class);
            \Livewire\Livewire::component('codesnoutr-dark-mode-toggle', \Rafaelogic\CodeSnoutr\Livewire\DarkModeToggle::class);
            \Livewire\Livewire::component('codesnoutr-smart-assistant', \Rafaelogic\CodeSnoutr\Livewire\SmartAssistant::class);
            \Livewire\Livewire::component('codesnoutr-ai-fix-suggestions', \Rafaelogic\CodeSnoutr\Livewire\AiFixSuggestions::class);
            \Livewire\Livewire::component('codesnoutr-ai-auto-fix', \Rafaelogic\CodeSnoutr\Livewire\AiAutoFix::class);
            \Livewire\Livewire::component('codesnoutr-group-file-details', \Rafaelogic\CodeSnoutr\Livewire\GroupFileDetails::class);
            \Livewire\Livewire::component('codesnoutr-queue-status', \Rafaelogic\CodeSnoutr\Livewire\QueueStatus::class);
            \Illuminate\Support\Facades\Log::info('CodeSnoutr Livewire components registered successfully');
        } else {
            \Illuminate\Support\Facades\Log::error('Livewire class not found during CodeSnoutr component registration');
        }
    }

    /**
     * Register Blade components
     */
    protected function registerBladeComponents(): void
    {
        if (method_exists($this->app['blade.compiler'], 'component')) {
            // Register new atomic design components
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.icon', 'atoms.icon');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.button', 'atoms.button');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.input', 'atoms.input');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.select', 'atoms.select');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.badge', 'atoms.badge');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.spinner', 'atoms.spinner');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.progress-bar', 'atoms.progress-bar');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.alert', 'atoms.alert');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.tooltip', 'atoms.tooltip');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.avatar', 'atoms.avatar');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.label', 'atoms.label');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.toggle', 'atoms.toggle');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.notification', 'atoms.notification');
            
            // Register new utility atoms
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.surface', 'atoms.surface');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.text', 'atoms.text');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.stack', 'atoms.stack');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.container', 'atoms.container');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.grid', 'atoms.grid');
            
            // Register improved molecule components
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.search-box', 'molecules.search-box');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.stat-card', 'molecules.stat-card');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.card', 'molecules.card');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.card-header', 'molecules.card-header');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.card-body', 'molecules.card-body');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.card-footer', 'molecules.card-footer');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.form-field', 'molecules.form-field');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.page-header', 'molecules.page-header');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.metric-card', 'molecules.metric-card');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.recent-scans-list', 'molecules.recent-scans-list');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.navigation', 'molecules.navigation');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.empty-state', 'molecules.empty-state');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.dropdown-item', 'molecules.dropdown-item');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.file-upload', 'molecules.file-upload');
            
            // Register table molecule components
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.table-toolbar', 'molecules.table-toolbar');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.table-header', 'molecules.table-header');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.table-header-cell', 'molecules.table-header-cell');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.table-body', 'molecules.table-body');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.table-loading-rows', 'molecules.table-loading-rows');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.table-empty-row', 'molecules.table-empty-row');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.table-checkbox-cell', 'molecules.table-checkbox-cell');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.table-data-cell', 'molecules.table-data-cell');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.table-pagination', 'molecules.table-pagination');
            
            // Register organism components
            $this->app['blade.compiler']->component('codesnoutr::components.organisms.header', 'organisms.header');
            $this->app['blade.compiler']->component('codesnoutr::components.organisms.sidebar', 'organisms.sidebar');
            $this->app['blade.compiler']->component('codesnoutr::components.organisms.scan-form', 'organisms.scan-form');
            $this->app['blade.compiler']->component('codesnoutr::components.organisms.scan-results', 'organisms.scan-results');
            $this->app['blade.compiler']->component('codesnoutr::components.organisms.data-table', 'organisms.data-table');
            
            // Register template components
            $this->app['blade.compiler']->component('codesnoutr::components.templates.app-layout', 'templates.app-layout');
        }
    }

    /**
     * Register debugbar collector
     */
    protected function registerDebugbarCollector(): void
    {
        if (class_exists('Barryvdh\Debugbar\LaravelDebugbar') && config('codesnoutr.debugbar.enabled', true)) {
            $debugbar = $this->app->make('debugbar');
            $debugbar->addCollector(new \Rafaelogic\CodeSnoutr\Debugbar\CodeSnoutrCollector());
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['codesnoutr'];
    }
}
