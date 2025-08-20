<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider;

class PublishingTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [CodeSnoutrServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup testing database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /** @test */
    public function it_publishes_config_file()
    {
        $this->artisan('vendor:publish', [
            '--provider' => CodeSnoutrServiceProvider::class,
            '--tag' => 'codesnoutr-config'
        ]);

        $this->assertFileExists(config_path('codesnoutr.php'));
    }

    /** @test */
    public function it_publishes_migration_files()
    {
        $this->artisan('vendor:publish', [
            '--provider' => CodeSnoutrServiceProvider::class,
            '--tag' => 'codesnoutr-migrations'
        ]);

        $migrationFiles = [
            '2024_01_01_000001_create_codesnoutr_scans_table.php',
            '2024_01_01_000002_create_codesnoutr_issues_table.php',
            '2024_01_01_000003_create_codesnoutr_settings_table.php'
        ];

        foreach ($migrationFiles as $migration) {
            $this->assertTrue(
                File::exists(database_path('migrations/' . $migration)),
                "Migration file {$migration} was not published"
            );
        }
    }

    /** @test */
    public function it_publishes_assets()
    {
        $this->artisan('vendor:publish', [
            '--provider' => CodeSnoutrServiceProvider::class,
            '--tag' => 'codesnoutr-assets'
        ]);

        // Check views
        $this->assertDirectoryExists(resource_path('views/vendor/codesnoutr'));
        $this->assertDirectoryExists(resource_path('views/vendor/codesnoutr/components'));
        $this->assertDirectoryExists(resource_path('views/vendor/codesnoutr/components/atoms'));
        $this->assertDirectoryExists(resource_path('views/vendor/codesnoutr/components/molecules'));
        $this->assertDirectoryExists(resource_path('views/vendor/codesnoutr/components/organisms'));
        $this->assertDirectoryExists(resource_path('views/vendor/codesnoutr/components/templates'));

        // Check CSS
        $this->assertFileExists(resource_path('css/vendor/codesnoutr/codesnoutr.css'));

        // Check JS
        $this->assertFileExists(resource_path('js/vendor/codesnoutr/codesnoutr.js'));
    }

    /** @test */
    public function it_publishes_routes()
    {
        $this->artisan('vendor:publish', [
            '--provider' => CodeSnoutrServiceProvider::class,
            '--tag' => 'codesnoutr-routes'
        ]);

        $this->assertFileExists(base_path('routes/codesnoutr.php'));
    }

    /** @test */
    public function it_publishes_documentation()
    {
        $this->artisan('vendor:publish', [
            '--provider' => CodeSnoutrServiceProvider::class,
            '--tag' => 'codesnoutr-docs'
        ]);

        $this->assertFileExists(base_path('docs/codesnoutr-integration.md'));
        $this->assertFileExists(base_path('docs/codesnoutr-troubleshooting.md'));
        $this->assertFileExists(base_path('docs/codesnoutr-csrf-troubleshooting.md'));
    }

    /** @test */
    public function it_publishes_all_assets_at_once()
    {
        $this->artisan('vendor:publish', [
            '--provider' => CodeSnoutrServiceProvider::class
        ]);

        // Verify all assets are published
        $this->assertFileExists(config_path('codesnoutr.php'));
        $this->assertDirectoryExists(resource_path('views/vendor/codesnoutr'));
        $this->assertFileExists(resource_path('css/vendor/codesnoutr/codesnoutr.css'));
        $this->assertFileExists(resource_path('js/vendor/codesnoutr/codesnoutr.js'));
        $this->assertFileExists(base_path('routes/codesnoutr.php'));
    }

    /** @test */
    public function css_file_contains_atomic_design_classes()
    {
        $cssContent = File::get(__DIR__ . '/../resources/css/codesnoutr.css');
        
        // Check for atomic design system classes
        $this->assertStringContainsString('.btn', $cssContent);
        $this->assertStringContainsString('.btn--primary', $cssContent);
        $this->assertStringContainsString('.input', $cssContent);
        $this->assertStringContainsString('.badge', $cssContent);
        $this->assertStringContainsString('.alert', $cssContent);
        
        // Check for dark mode variants
        $this->assertStringContainsString('.dark', $cssContent);
        
        // Check for component specific styles
        $this->assertStringContainsString('.severity-critical', $cssContent);
        $this->assertStringContainsString('.progress-bar', $cssContent);
    }

    /** @test */
    public function js_file_contains_required_functions()
    {
        $jsContent = File::get(__DIR__ . '/../resources/js/codesnoutr.js');
        
        // Check for main functions
        $this->assertStringContainsString('initDarkMode', $jsContent);
        $this->assertStringContainsString('toggleDarkMode', $jsContent);
        $this->assertStringContainsString('copyToClipboard', $jsContent);
        $this->assertStringContainsString('highlightSearchTerms', $jsContent);
        $this->assertStringContainsString('formatFileSize', $jsContent);
        $this->assertStringContainsString('setupKeyboardShortcuts', $jsContent);
        
        // Check for CodeSnoutr namespace
        $this->assertStringContainsString('window.CodeSnoutr', $jsContent);
    }

    /** @test */
    public function atomic_design_components_exist()
    {
        $componentsPath = __DIR__ . '/../resources/views/components';
        
        // Check atoms
        $atoms = [
            'badge.blade.php',
            'button.blade.php', 
            'icon.blade.php',
            'input.blade.php',
            'label.blade.php',
            'progress-bar.blade.php',
            'select.blade.php',
            'spinner.blade.php',
            'toggle.blade.php'
        ];
        
        foreach ($atoms as $atom) {
            $this->assertFileExists("{$componentsPath}/atoms/{$atom}");
        }
        
        // Check molecules
        $molecules = [
            'alert.blade.php',
            'card.blade.php',
            'dropdown.blade.php',
            'empty-state.blade.php',
            'form-field.blade.php',
            'modal.blade.php',
            'pagination.blade.php',
            'search-box.blade.php',
            'settings-form.blade.php',
            'stat-card.blade.php',
            'tabs.blade.php'
        ];
        
        foreach ($molecules as $molecule) {
            $this->assertFileExists("{$componentsPath}/molecules/{$molecule}");
        }
        
        // Check organisms
        $organisms = [
            'data-table.blade.php',
            'navigation.blade.php',
            'scan-results.blade.php',
            'sidebar.blade.php'
        ];
        
        foreach ($organisms as $organism) {
            $this->assertFileExists("{$componentsPath}/organisms/{$organism}");
        }
        
        // Check templates
        $templates = [
            'app-layout.blade.php',
            'dashboard-layout.blade.php',
            'settings-layout.blade.php'
        ];
        
        foreach ($templates as $template) {
            $this->assertFileExists("{$componentsPath}/templates/{$template}");
        }
    }

    /** @test */
    public function icons_are_properly_organized()
    {
        $iconsPath = __DIR__ . '/../resources/views/components/icons/outline';
        
        $requiredIcons = [
            'search.blade.php',
            'x.blade.php',
            'check.blade.php',
            'plus-circle.blade.php',
            'cog.blade.php',
            'chart-bar.blade.php',
            'exclamation-triangle.blade.php',
            'bell.blade.php',
            'user.blade.php'
        ];
        
        foreach ($requiredIcons as $icon) {
            $this->assertFileExists("{$iconsPath}/{$icon}");
        }
    }

    protected function tearDown(): void
    {
        // Clean up published files after tests
        $this->cleanupPublishedFiles();
        parent::tearDown();
    }

    private function cleanupPublishedFiles()
    {
        $filesToClean = [
            config_path('codesnoutr.php'),
            base_path('routes/codesnoutr.php'),
            resource_path('views/vendor/codesnoutr'),
            resource_path('css/vendor/codesnoutr'),
            resource_path('js/vendor/codesnoutr'),
            database_path('migrations/2024_01_01_000001_create_codesnoutr_scans_table.php'),
            database_path('migrations/2024_01_01_000002_create_codesnoutr_issues_table.php'),
            database_path('migrations/2024_01_01_000003_create_codesnoutr_settings_table.php'),
            base_path('docs/codesnoutr-integration.md'),
            base_path('docs/codesnoutr-troubleshooting.md'),
            base_path('docs/codesnoutr-csrf-troubleshooting.md')
        ];

        foreach ($filesToClean as $file) {
            if (File::exists($file)) {
                if (File::isDirectory($file)) {
                    File::deleteDirectory($file);
                } else {
                    File::delete($file);
                }
            }
        }
    }
}
