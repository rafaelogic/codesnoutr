<?php

namespace Rafaelogic\CodeSnoutr;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Rafaelogic\CodeSnoutr\Commands\ScanCommand;
use Rafaelogic\CodeSnoutr\Commands\InstallCommand;

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
            return new \Rafaelogic\CodeSnoutr\Services\AiAssistantService();
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

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/codesnoutr'),
            __DIR__.'/../resources/css' => resource_path('css/vendor/codesnoutr'),
            __DIR__.'/../resources/js' => resource_path('js/vendor/codesnoutr'),
        ], 'codesnoutr-assets');

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
            \Livewire\Livewire::component('codesnoutr-dashboard', \Rafaelogic\CodeSnoutr\Livewire\Dashboard::class);
            \Livewire\Livewire::component('codesnoutr-scan-form', \Rafaelogic\CodeSnoutr\Livewire\ScanForm::class);
            \Livewire\Livewire::component('codesnoutr-scan-wizard', \Rafaelogic\CodeSnoutr\Livewire\ScanWizard::class);
            \Livewire\Livewire::component('codesnoutr-scan-results', \Rafaelogic\CodeSnoutr\Livewire\ScanResults::class);
            \Livewire\Livewire::component('codesnoutr-settings', \Rafaelogic\CodeSnoutr\Livewire\Settings::class);
            \Livewire\Livewire::component('codesnoutr-dark-mode-toggle', \Rafaelogic\CodeSnoutr\Livewire\DarkModeToggle::class);
            \Livewire\Livewire::component('codesnoutr-smart-assistant', \Rafaelogic\CodeSnoutr\Livewire\SmartAssistant::class);
            \Livewire\Livewire::component('codesnoutr-ai-fix-suggestions', \Rafaelogic\CodeSnoutr\Livewire\AiFixSuggestions::class);
            \Livewire\Livewire::component('codesnoutr-group-file-details', \Rafaelogic\CodeSnoutr\Livewire\GroupFileDetails::class);
        }
    }

    /**
     * Register Blade components
     */
    protected function registerBladeComponents(): void
    {
        if (method_exists($this->app['blade.compiler'], 'component')) {
            // Register atomic design components
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.icon', 'atoms.icon');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.button', 'atoms.button');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.input', 'atoms.input');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.badge', 'atoms.badge');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.spinner', 'atoms.spinner');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.progress-bar', 'atoms.progress-bar');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.alert', 'atoms.alert');
            $this->app['blade.compiler']->component('codesnoutr::components.atoms.tooltip', 'atoms.tooltip');
            
            // Register molecule components
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.search-box', 'molecules.search-box');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.stat-card', 'molecules.stat-card');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.card', 'molecules.card');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.navigation', 'molecules.navigation');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.empty-state', 'molecules.empty-state');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.dropdown-item', 'molecules.dropdown-item');
            $this->app['blade.compiler']->component('codesnoutr::components.molecules.file-upload', 'molecules.file-upload');
            
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
