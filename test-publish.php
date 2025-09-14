<?php

/**
 * Simple test script to check what can be published from CodeSnoutr package
 * Run this from your Laravel application root to test publishing
 */

echo "CodeSnoutr Asset Publishing Test\n";
echo "================================\n\n";

// Check if we're in a Laravel environment
if (!function_exists('app') && !class_exists('Illuminate\Foundation\Application')) {
    echo "❌ This must be run from a Laravel application directory\n";
    echo "Please navigate to your Laravel app root and run:\n";
    echo "php artisan vendor:publish --provider=\"Rafaelogic\\CodeSnoutr\\CodeSnoutrServiceProvider\"\n";
    exit(1);
}

// List available publishing tags
echo "Available publishing commands:\n";
echo "php artisan vendor:publish --tag=codesnoutr-assets\n";
echo "php artisan vendor:publish --tag=codesnoutr-views\n";
echo "php artisan vendor:publish --tag=codesnoutr-config\n";
echo "php artisan vendor:publish --tag=codesnoutr-migrations\n";
echo "php artisan vendor:publish --tag=codesnoutr-resources (all files)\n";
echo "\n";

// Check if package is installed
$composerFile = 'composer.json';
if (file_exists($composerFile)) {
    $composer = json_decode(file_get_contents($composerFile), true);
    
    $isInstalled = false;
    
    // Check require section
    if (isset($composer['require']['rafaelogic/codesnoutr'])) {
        $isInstalled = true;
        echo "✅ Package found in composer.json require section\n";
    }
    
    // Check require-dev section  
    if (isset($composer['require-dev']['rafaelogic/codesnoutr'])) {
        $isInstalled = true;
        echo "✅ Package found in composer.json require-dev section\n";
    }
    
    if (!$isInstalled) {
        echo "❌ Package not found in composer.json\n";
        echo "Install with: composer require rafaelogic/codesnoutr\n";
    }
}

// Check if vendor directory exists
if (file_exists('vendor/rafaelogic/codesnoutr')) {
    echo "✅ Package files found in vendor directory\n";
} else {
    echo "❌ Package files not found in vendor directory\n";
    echo "Run: composer install or composer update\n";
}

// Check specific asset files
$assetFiles = [
    'vendor/rafaelogic/codesnoutr/resources/css/codesnoutr.css',
    'vendor/rafaelogic/codesnoutr/resources/js/codesnoutr.js',
    'vendor/rafaelogic/codesnoutr/resources/images/codesnoutr-icon.svg',
];

echo "\nChecking asset files:\n";
foreach ($assetFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file\n";
    } else {
        echo "❌ $file (missing)\n";
    }
}

echo "\nIf all files exist, try:\n";
echo "php artisan vendor:publish --tag=codesnoutr-assets --force\n";
echo "\nOr use the install command:\n";
echo "php artisan codesnoutr:install\n";