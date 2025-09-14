<?php

namespace Rafaelogic\CodeSnoutr\Commands;

use Illuminate\Console\Command;

class AssetStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'codesnoutr:asset-status';

    /**
     * The console command description.
     */
    protected $description = 'Check the status of CodeSnoutr assets and provide troubleshooting information';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('🔍 <comment>Checking CodeSnoutr asset status...</comment>');
        $this->newLine();

        // Check for package built assets
        $packageManifest = public_path('vendor/codesnoutr/build/manifest.json');
        $hasPackageAssets = file_exists($packageManifest);
        
        if ($hasPackageAssets) {
            $this->line('✅ <info>Package assets found</info> at: ' . $packageManifest);
            
            $manifest = json_decode(file_get_contents($packageManifest), true);
            if ($manifest) {
                foreach ($manifest as $file => $details) {
                    $fullPath = public_path('vendor/codesnoutr/build/' . $details['file']);
                    $status = file_exists($fullPath) ? '✅' : '❌';
                    $this->line("   {$status} {$details['file']} " . ($status === '✅' ? '(' . $this->formatFileSize(filesize($fullPath)) . ')' : '(missing)'));
                }
            }
        } else {
            $this->line('❌ <error>Package assets NOT found</error> at: ' . $packageManifest);
            $this->line('   Run: <comment>php artisan vendor:publish --tag=codesnoutr-assets</comment>');
        }
        
        $this->newLine();

        // Check for main app Vite assets
        $appManifest = public_path('build/manifest.json');
        $hasAppAssets = file_exists($appManifest);
        
        if ($hasAppAssets) {
            $this->line('ℹ️  <info>Main app Vite assets found</info> at: ' . $appManifest);
        } else {
            $this->line('ℹ️  <comment>Main app Vite assets not found</comment> (this is normal)');
        }
        
        $this->newLine();

        // Check asset loading logic
        $this->line('📋 <comment>Asset Loading Priority:</comment>');
        $this->line('   1. Package built assets (vendor/codesnoutr/build/*)');
        $this->line('   2. Main app Vite assets (@vite directive)');
        $this->line('   3. Tailwind CDN fallback');
        
        $this->newLine();

        // Provide recommendations
        if (!$hasPackageAssets) {
            $this->line('🔧 <comment>Recommended Actions:</comment>');
            $this->line('   1. Publish package assets: <info>php artisan vendor:publish --tag=codesnoutr-assets</info>');
            $this->line('   2. If assets still missing, force publish: <info>php artisan vendor:publish --tag=codesnoutr-assets --force</info>');
            $this->line('   3. Clear view cache: <info>php artisan view:clear</info>');
        } else {
            $this->line('✅ <info>Assets are properly configured!</info>');
        }

        return self::SUCCESS;
    }

    /**
     * Format file size for display
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1) . 'MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 1) . 'KB';
        } else {
            return $bytes . 'B';
        }
    }
}