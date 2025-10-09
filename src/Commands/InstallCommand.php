<?php

namespace Rafaelogic\CodeSnoutr\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'codesnoutr:install 
                           {--force : Force overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Install CodeSnoutr package assets and configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing CodeSnoutr...');

        try {
            // Publish configuration
            $this->publishConfig();

            // Publish assets
            $this->publishAssets();

            // Run migrations
            $this->runMigrations();

            // Create initial settings
            $this->createInitialSettings();

            $this->info('✅ CodeSnoutr installation completed successfully!');
            $this->newLine();
            $this->info('🌐 Access your dashboard at: ' . url('/codesnoutr'));
            $this->info('⚙️  Configure your settings at: ' . url('/codesnoutr/settings'));
            $this->newLine();
            $this->info('📖 Documentation: https://github.com/rafaelogic/codesnoutr');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Installation failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Publish configuration files
     */
    protected function publishConfig(): void
    {
        $this->info('Publishing configuration...');

        $force = $this->option('force');
        
        if ($force || !File::exists(config_path('codesnoutr.php'))) {
            $this->call('vendor:publish', [
                '--provider' => 'Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider',
                '--tag' => 'codesnoutr-config',
                '--force' => $force,
            ]);
            $this->info('✓ Configuration published');
        } else {
            $this->warn('⚠ Configuration already exists (use --force to overwrite)');
        }
    }

    /**
     * Publish asset files
     */
    protected function publishAssets(): void
    {
        $this->info('Publishing assets...');

        $force = $this->option('force');

        try {
            // Try publishing assets first
            $exitCode = $this->call('vendor:publish', [
                '--provider' => 'Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider',
                '--tag' => 'codesnoutr-assets',
                '--force' => $force,
            ]);

            if ($exitCode !== 0) {
                $this->warn('Asset publishing via tag failed, trying with provider only...');
                
                // Fallback: publish all from provider
                $this->call('vendor:publish', [
                    '--provider' => 'Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider',
                    '--force' => $force,
                ]);
            }

            // Verify assets were published (check for build directory or CSS files)
            $buildPublished = File::exists(public_path('vendor/codesnoutr/build/manifest.json'));
            $cssPublished = File::exists(public_path('vendor/codesnoutr/css/codesnoutr.css'));
            
            if (!$buildPublished && !$cssPublished) {
                $this->warn('Assets may not have been published correctly. Trying alternative method...');
                
                // Manual asset copying as fallback
                $this->copyAssetsManually();
            }

            $this->info('✓ Assets published');

        } catch (\Exception $e) {
            $this->warn('Asset publishing failed: ' . $e->getMessage());
            $this->warn('Attempting manual asset copying...');
            
            $this->copyAssetsManually();
        }
    }

    /**
     * Manually copy assets if publishing fails
     */
    protected function copyAssetsManually(): void
    {
        $packagePath = base_path('vendor/rafaelogic/codesnoutr');
        
        if (!File::exists($packagePath)) {
            throw new \Exception('Package not found in vendor directory. Please run "composer install".');
        }

        // Create target directories
        $publicDir = public_path('vendor/codesnoutr');
        File::makeDirectory($publicDir . '/css', 0755, true, true);
        File::makeDirectory($publicDir . '/js', 0755, true, true);
        File::makeDirectory($publicDir . '/images', 0755, true, true);
        File::makeDirectory($publicDir . '/build', 0755, true, true);

        // Copy built assets from public/build (compiled Vite assets)
        $buildDir = $packagePath . '/public/build';
        if (File::exists($buildDir)) {
            // Copy the entire build directory
            File::copyDirectory($buildDir, $publicDir . '/build');
            $this->info('✓ Copied compiled assets from build directory');
        }

        // Copy raw asset files as fallback
        $assetFiles = [
            'resources/css/codesnoutr.css' => 'css/codesnoutr.css',
            'resources/js/codesnoutr.js' => 'js/codesnoutr.js',
            'resources/images/codesnoutr-icon.svg' => 'images/codesnoutr-icon.svg',
        ];

        foreach ($assetFiles as $source => $target) {
            $sourcePath = $packagePath . '/' . $source;
            $targetPath = $publicDir . '/' . $target;

            if (File::exists($sourcePath)) {
                File::copy($sourcePath, $targetPath);
                $this->info("✓ Copied {$target}");
            } else {
                $this->warn("⚠ Source file not found: {$source}");
            }
        }

        $this->info('✓ Manual asset copying completed');
    }

    /**
     * Run database migrations
     */
    protected function runMigrations(): void
    {
        $this->info('Running migrations...');

        $this->call('vendor:publish', [
            '--provider' => 'Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider',
            '--tag' => 'codesnoutr-migrations',
            '--force' => $this->option('force'),
        ]);

        $this->call('migrate');

        $this->info('✓ Migrations completed');
    }

    /**
     * Create initial settings
     */
    protected function createInitialSettings(): void
    {
        $this->info('Creating initial settings...');

        // Check if we need to ask for OpenAI API key
        if ($this->confirm('Would you like to configure OpenAI integration now?', false)) {
            $apiKey = $this->secret('Please enter your OpenAI API key:');
            
            if ($apiKey) {
                \Rafaelogic\CodeSnoutr\Models\Setting::setOpenAiApiKey($apiKey);
                \Rafaelogic\CodeSnoutr\Models\Setting::set('ai_enabled', true, 'ai');
                $this->info('✓ OpenAI API key configured');
            }
        }

        // Set default theme
        $theme = $this->choice('Choose default theme:', ['light', 'dark', 'system'], 'system');
        \Rafaelogic\CodeSnoutr\Models\Setting::setTheme($theme);

        // Set default scan categories
        $defaultCategories = ['security', 'performance'];
        \Rafaelogic\CodeSnoutr\Models\Setting::set('scan_default_categories', $defaultCategories, 'scan');

        $this->info('✓ Initial settings created');
    }

    /**
     * Display post-installation instructions
     */
    protected function displayInstructions(): void
    {
        $this->newLine();
        $this->info('🎉 CodeSnoutr is ready to use!');
        $this->newLine();
        
        $this->info('Next steps:');
        $this->info('1. Visit ' . url('/codesnoutr') . ' to access the dashboard');
        $this->info('2. Configure your settings at ' . url('/codesnoutr/settings'));
        $this->info('3. Run your first scan: php artisan codesnoutr:scan');
        $this->newLine();
        
        $this->info('Useful commands:');
        $this->info('• php artisan codesnoutr:scan - Run a full codebase scan');
        $this->info('• php artisan codesnoutr:scan file path/to/file.php - Scan a specific file');
        $this->info('• php artisan codesnoutr:scan directory app/Models - Scan a directory');
        $this->newLine();
        
        if (!config('codesnoutr.ai.enabled')) {
            $this->warn('💡 Tip: Configure OpenAI API key in settings to enable AI-powered auto-fixes');
        }
    }
}
