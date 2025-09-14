# CodeSnoutr Asset Publishing Guide

## Problem: "No publishable resources for tag [codesnoutr-assets]"

This error occurs when Laravel cannot find the CodeSnoutr package assets to publish. Here's how to fix it:

## Solution Steps

### 1. Verify Package Installation

First, make sure the package is properly installed in your Laravel application:

```bash
# Check if package is in composer.json
cat composer.json | grep codesnoutr

# Check if vendor files exist
ls -la vendor/rafaelogic/codesnoutr/

# If not installed, install it:
composer require rafaelogic/codesnoutr
```

### 2. Clear Laravel Caches

Laravel might have cached the old service provider information:

```bash
# Clear all Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Clear composer autoload cache
composer dump-autoload

# Recreate config cache
php artisan config:cache
```

### 3. Try Publishing with Provider

Use the full provider name instead of just the tag:

```bash
# Publish with provider name
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider"

# Or publish specific tags
php artisan vendor:publish --tag=codesnoutr-assets --force
php artisan vendor:publish --tag=codesnoutr-config --force
php artisan vendor:publish --tag=codesnoutr-migrations --force
```

### 4. Use the Install Command

The package includes an install command that handles everything:

```bash
# Install everything at once
php artisan codesnoutr:install

# Install with force (overwrite existing files)
php artisan codesnoutr:install --force
```

### 5. Manual Asset Check

If the above doesn't work, check if the asset files exist:

```bash
# Run the test script (from package directory)
php test-publish.php

# Check specific files
ls -la vendor/rafaelogic/codesnoutr/resources/css/
ls -la vendor/rafaelogic/codesnoutr/resources/js/
ls -la vendor/rafaelogic/codesnoutr/resources/images/
```

### 6. Available Publishing Tags

The package supports these publishing tags:

- `codesnoutr-assets` - CSS, JS, and image files
- `codesnoutr-views` - Blade templates for customization
- `codesnoutr-config` - Configuration file
- `codesnoutr-migrations` - Database migrations
- `codesnoutr-routes` - Route files (optional)
- `codesnoutr-docs` - Documentation files
- `codesnoutr-resources` - All resources at once

## Expected Results

After successful publishing, you should see:

```
public/vendor/codesnoutr/
├── css/
│   └── codesnoutr.css
├── js/
│   └── codesnoutr.js
└── images/
    └── codesnoutr-icon.svg

resources/views/vendor/codesnoutr/
├── components/
├── livewire/
└── pages/

config/
└── codesnoutr.php

database/migrations/
├── 2024_01_01_000001_create_codesnoutr_scans_table.php
├── 2024_01_01_000002_create_codesnoutr_issues_table.php
└── 2024_01_01_000003_create_codesnoutr_settings_table.php
```

## Troubleshooting

### If you still get "No publishable resources" error:

1. **Check service provider registration:**
   ```php
   // In config/app.php (if not auto-discovered)
   'providers' => [
       // ...
       Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider::class,
   ],
   ```

2. **Verify package is in the right location:**
   ```bash
   composer show rafaelogic/codesnoutr
   ```

3. **Check Laravel version compatibility:**
   - The package requires Laravel 10.0+ and PHP 8.1+

4. **Try manual file copying (last resort):**
   ```bash
   # Create directories
   mkdir -p public/vendor/codesnoutr/{css,js,images}
   mkdir -p resources/views/vendor/codesnoutr
   
   # Copy files manually
   cp vendor/rafaelogic/codesnoutr/resources/css/* public/vendor/codesnoutr/css/
   cp vendor/rafaelogic/codesnoutr/resources/js/* public/vendor/codesnoutr/js/
   cp vendor/rafaelogic/codesnoutr/resources/images/* public/vendor/codesnoutr/images/
   cp -r vendor/rafaelogic/codesnoutr/resources/views/* resources/views/vendor/codesnoutr/
   cp vendor/rafaelogic/codesnoutr/config/codesnoutr.php config/
   ```

## Next Steps

After publishing assets:

1. **Run migrations:** `php artisan migrate`
2. **Access dashboard:** Visit `/codesnoutr` in your browser
3. **Configure settings:** Visit `/codesnoutr/settings`
4. **Run first scan:** `php artisan codesnoutr:scan`

## Need Help?

If you're still having issues:

1. Check the Laravel log: `tail -f storage/logs/laravel.log`
2. Enable debug mode: Set `APP_DEBUG=true` in `.env`
3. Check server requirements: PHP 8.1+, Laravel 10.0+
4. Verify file permissions on storage and bootstrap directories