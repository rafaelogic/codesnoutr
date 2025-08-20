#!/usr/bin/env php
<?php

/**
 * CodeSnoutr Publishing Verification Script
 * 
 * This script verifies that all publishable assets exist and are properly configured.
 * Run this before releasing a new version to ensure publishing will work correctly.
 */

$packageRoot = dirname(__FILE__);
$errors = [];
$warnings = [];
$success = [];

echo "CodeSnoutr Publishing Verification\n";
echo "=================================\n\n";

// Check Service Provider
echo "🔍 Checking Service Provider...\n";
$serviceProvider = $packageRoot . '/src/CodeSnoutrServiceProvider.php';
if (file_exists($serviceProvider)) {
    $content = file_get_contents($serviceProvider);
    
    // Check for publish configurations
    $publishConfigs = [
        'codesnoutr-config',
        'codesnoutr-migrations', 
        'codesnoutr-assets',
        'codesnoutr-routes',
        'codesnoutr-docs'
    ];
    
    foreach ($publishConfigs as $tag) {
        if (strpos($content, "'{$tag}'") !== false) {
            $success[] = "✅ Publish tag '{$tag}' found in service provider";
        } else {
            $errors[] = "❌ Publish tag '{$tag}' missing from service provider";
        }
    }
} else {
    $errors[] = "❌ Service provider not found";
}

// Check Configuration File
echo "\n🔍 Checking Configuration...\n";
$configFile = $packageRoot . '/config/codesnoutr.php';
if (file_exists($configFile)) {
    $success[] = "✅ Configuration file exists";
    
    // Check if it's valid PHP (without including it)
    $configContent = file_get_contents($configFile);
    if (strpos($configContent, 'return [') !== false) {
        $success[] = "✅ Configuration file has valid structure";
    } else {
        $errors[] = "❌ Configuration file doesn't return array";
    }
    
    // Check for key configuration options
    $expectedConfigs = [
        'auto_load_routes' => "'auto_load_routes'",
        'scan configuration' => "'scan'",
        'ai configuration' => "'ai'",
        'ui configuration' => "'ui'",
        'queue configuration' => "'queue'"
    ];
    foreach ($expectedConfigs as $description => $configKey) {
        if (strpos($configContent, $configKey) !== false) {
            $success[] = "✅ Configuration option found: {$description}";
        } else {
            $warnings[] = "⚠️  Configuration option might be missing: {$description}";
        }
    }
} else {
    $errors[] = "❌ Configuration file missing: config/codesnoutr.php";
}

// Check Migration Files
echo "\n🔍 Checking Migrations...\n";
$migrationsDir = $packageRoot . '/database/migrations';
$expectedMigrations = [
    '2024_01_01_000001_create_codesnoutr_scans_table.php',
    '2024_01_01_000002_create_codesnoutr_issues_table.php',
    '2024_01_01_000003_create_codesnoutr_settings_table.php'
];

if (is_dir($migrationsDir)) {
    foreach ($expectedMigrations as $migration) {
        $migrationPath = $migrationsDir . '/' . $migration;
        if (file_exists($migrationPath)) {
            $success[] = "✅ Migration exists: {$migration}";
        } else {
            $errors[] = "❌ Migration missing: {$migration}";
        }
    }
} else {
    $errors[] = "❌ Migrations directory missing";
}

// Check Routes
echo "\n🔍 Checking Routes...\n";
$routesFile = $packageRoot . '/routes/web.php';
if (file_exists($routesFile)) {
    $success[] = "✅ Routes file exists";
    
    $routeContent = file_get_contents($routesFile);
    $expectedRoutes = [
        'codesnoutr.dashboard',
        'codesnoutr.scan',
        'codesnoutr.results',
        'codesnoutr.settings'
    ];
    
    foreach ($expectedRoutes as $route) {
        if (strpos($routeContent, "'{$route}'") !== false || strpos($routeContent, "\"{$route}\"") !== false) {
            $success[] = "✅ Route name found: {$route}";
        } else {
            $warnings[] = "⚠️  Route name might be missing: {$route}";
        }
    }
} else {
    $errors[] = "❌ Routes file missing: routes/web.php";
}

// Check Assets
echo "\n🔍 Checking Assets...\n";

// CSS
$cssFile = $packageRoot . '/resources/css/codesnoutr.css';
if (file_exists($cssFile)) {
    $success[] = "✅ CSS file exists";
    
    $cssContent = file_get_contents($cssFile);
    $expectedClasses = ['.btn', '.input', '.badge', '.alert', '.dark'];
    
    foreach ($expectedClasses as $class) {
        if (strpos($cssContent, $class) !== false) {
            $success[] = "✅ CSS class found: {$class}";
        } else {
            $warnings[] = "⚠️  CSS class missing: {$class}";
        }
    }
} else {
    $errors[] = "❌ CSS file missing: resources/css/codesnoutr.css";
}

// JavaScript
$jsFile = $packageRoot . '/resources/js/codesnoutr.js';
if (file_exists($jsFile)) {
    $success[] = "✅ JavaScript file exists";
    
    $jsContent = file_get_contents($jsFile);
    $expectedFunctions = ['initDarkMode', 'toggleDarkMode', 'copyToClipboard'];
    
    foreach ($expectedFunctions as $func) {
        if (strpos($jsContent, $func) !== false) {
            $success[] = "✅ JavaScript function found: {$func}";
        } else {
            $warnings[] = "⚠️  JavaScript function missing: {$func}";
        }
    }
} else {
    $errors[] = "❌ JavaScript file missing: resources/js/codesnoutr.js";
}

// Check Views
echo "\n🔍 Checking Views...\n";
$viewsDir = $packageRoot . '/resources/views';

// Check atomic design structure
$atomicStructure = [
    'components/atoms',
    'components/molecules', 
    'components/organisms',
    'components/templates',
    'components/icons/outline'
];

foreach ($atomicStructure as $dir) {
    $fullPath = $viewsDir . '/' . $dir;
    if (is_dir($fullPath)) {
        $fileCount = count(glob($fullPath . '/*.blade.php'));
        $success[] = "✅ Atomic design directory exists: {$dir} ({$fileCount} files)";
    } else {
        $errors[] = "❌ Atomic design directory missing: {$dir}";
    }
}

// Check key components
$keyComponents = [
    'components/atoms/button.blade.php',
    'components/atoms/input.blade.php',
    'components/molecules/alert.blade.php',
    'components/organisms/navigation.blade.php',
    'components/templates/app-layout.blade.php'
];

foreach ($keyComponents as $component) {
    $componentPath = $viewsDir . '/' . $component;
    if (file_exists($componentPath)) {
        $success[] = "✅ Key component exists: {$component}";
    } else {
        $errors[] = "❌ Key component missing: {$component}";
    }
}

// Check Livewire views
$livewireDir = $viewsDir . '/livewire';
if (is_dir($livewireDir)) {
    $livewireFiles = glob($livewireDir . '/*.blade.php');
    $success[] = "✅ Livewire views directory exists (" . count($livewireFiles) . " files)";
} else {
    $warnings[] = "⚠️  Livewire views directory missing";
}

// Check Documentation Files
echo "\n🔍 Checking Documentation...\n";
$docFiles = [
    'ROUTE_INTEGRATION.md',
    'ROUTE_TROUBLESHOOTING.md', 
    'CSRF_TROUBLESHOOTING.md',
    'PUBLISHING_GUIDE.md'
];

foreach ($docFiles as $docFile) {
    $docPath = $packageRoot . '/' . $docFile;
    if (file_exists($docPath)) {
        $success[] = "✅ Documentation exists: {$docFile}";
    } else {
        $errors[] = "❌ Documentation missing: {$docFile}";
    }
}

// Check Examples
echo "\n🔍 Checking Examples...\n";
$examplesDir = $packageRoot . '/examples';
if (is_dir($examplesDir)) {
    $exampleFiles = glob($examplesDir . '/*.blade.php');
    $success[] = "✅ Examples directory exists (" . count($exampleFiles) . " files)";
    
    $keyExamples = [
        'atomic-components-demo.blade.php',
        'settings-demo.blade.php'
    ];
    
    foreach ($keyExamples as $example) {
        $examplePath = $examplesDir . '/' . $example;
        if (file_exists($examplePath)) {
            $success[] = "✅ Key example exists: {$example}";
        } else {
            $warnings[] = "⚠️  Key example missing: {$example}";
        }
    }
} else {
    $warnings[] = "⚠️  Examples directory missing";
}

// File Permissions Check
echo "\n🔍 Checking File Permissions...\n";
$directoriesToCheck = [
    '/resources',
    '/config', 
    '/database',
    '/routes'
];

foreach ($directoriesToCheck as $dir) {
    $fullPath = $packageRoot . $dir;
    if (is_readable($fullPath)) {
        $success[] = "✅ Directory readable: {$dir}";
    } else {
        $warnings[] = "⚠️  Directory not readable: {$dir}";
    }
}

// Summary
echo "\n📊 Summary\n";
echo "=========\n";
echo "✅ Successes: " . count($success) . "\n";
echo "⚠️  Warnings: " . count($warnings) . "\n";
echo "❌ Errors: " . count($errors) . "\n\n";

if (!empty($errors)) {
    echo "🚨 ERRORS (must fix before publishing):\n";
    foreach ($errors as $error) {
        echo "  {$error}\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  WARNINGS (should consider fixing):\n";
    foreach ($warnings as $warning) {
        echo "  {$warning}\n";
    }
    echo "\n";
}

// File count summary
$totalBladeFiles = count(glob($packageRoot . '/resources/views/**/*.blade.php', GLOB_BRACE));
$totalFiles = count(glob($packageRoot . '/resources/**/*', GLOB_BRACE));

echo "📁 File Statistics:\n";
echo "  Total Blade files: {$totalBladeFiles}\n";
echo "  Total resource files: {$totalFiles}\n";

// Publishing commands
echo "\n📋 Publishing Commands:\n";
echo "====================\n";
echo "# Publish configuration\n";
echo "php artisan vendor:publish --provider=\"Rafaelogic\\CodeSnoutr\\CodeSnoutrServiceProvider\" --tag=\"codesnoutr-config\"\n\n";
echo "# Publish migrations\n";
echo "php artisan vendor:publish --provider=\"Rafaelogic\\CodeSnoutr\\CodeSnoutrServiceProvider\" --tag=\"codesnoutr-migrations\"\n\n";
echo "# Publish assets (views, CSS, JS)\n";
echo "php artisan vendor:publish --provider=\"Rafaelogic\\CodeSnoutr\\CodeSnoutrServiceProvider\" --tag=\"codesnoutr-assets\"\n\n";
echo "# Publish routes\n";
echo "php artisan vendor:publish --provider=\"Rafaelogic\\CodeSnoutr\\CodeSnoutrServiceProvider\" --tag=\"codesnoutr-routes\"\n\n";
echo "# Publish documentation\n";
echo "php artisan vendor:publish --provider=\"Rafaelogic\\CodeSnoutr\\CodeSnoutrServiceProvider\" --tag=\"codesnoutr-docs\"\n\n";
echo "# Publish everything\n";
echo "php artisan vendor:publish --provider=\"Rafaelogic\\CodeSnoutr\\CodeSnoutrServiceProvider\"\n\n";

$exitCode = !empty($errors) ? 1 : 0;

if ($exitCode === 0) {
    echo "🎉 All checks passed! Ready for publishing.\n";
} else {
    echo "🚨 Fix errors before publishing.\n";
}

exit($exitCode);
