# CodeSnoutr Route Integration Guide

This guide explains how to integrate CodeSnoutr routes into your existing Laravel application with your custom middleware, guards, and localization setup.

## Basic Integration

### Option 1: Simple Integration (No Middleware)
Add this to your `routes/web.php` or `routes/admin.php`:

```php
// Include CodeSnoutr routes
require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
```

### Option 2: With Authentication Middleware
```php
Route::middleware(['auth'])->group(function () {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

### Option 3: With Custom Middleware and Guards
```php
Route::middleware(['web', 'auth:admin', 'your-custom-middleware'])->group(function () {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

## Localized Routes Integration

### Option 1: Laravel Localization Package (mcamara/laravel-localization)
```php
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
], function() {
    Route::middleware(['auth'])->group(function () {
        require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
    });
});
```

### Option 2: Custom Localization Setup
```php
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
    'middleware' => ['web', 'auth', 'set-locale']
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

### Option 3: Subdomain Localization
```php
Route::group([
    'domain' => '{locale}.yourdomain.com',
    'where' => ['locale' => '[a-zA-Z]{2}'],
    'middleware' => ['web', 'auth']
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

## Admin Panel Integration

### Option 1: Laravel Nova
```php
// In your Nova routes
Route::prefix('nova-vendor/codesnoutr')->group(function () {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

### Option 2: Filament Admin
```php
// In your Filament panel provider
Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

### Option 3: Custom Admin Panel
```php
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['web', 'auth:admin', 'can:access-admin'])
    ->group(function () {
        require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
    });
```

## Multi-tenant Applications

### Option 1: Tenant-specific Routes
```php
// Using spatie/laravel-multitenancy or similar
Route::middleware(['tenant'])->group(function () {
    Route::prefix('tenant/{tenant}')->group(function () {
        require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
    });
});
```

### Option 2: Subdomain Tenancy
```php
Route::group([
    'domain' => '{tenant}.yourdomain.com',
    'middleware' => ['web', 'auth', 'tenant']
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

## API Integration

For API access, you can wrap the routes with API middleware:

```php
Route::prefix('api/v1')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function () {
        require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
    });
```

## Route Naming Considerations

The package's routes use the `codesnoutr.` prefix by default. When you wrap them in additional groups, the route names will be automatically prefixed. For example:

- Base route: `codesnoutr.dashboard`
- With locale: `en.codesnoutr.dashboard`  
- With admin prefix: `admin.codesnoutr.dashboard`
- Combined: `en.admin.codesnoutr.dashboard`

The package automatically detects these patterns and generates the correct URLs.

## Configuration

You can also customize the route integration by publishing the routes and modifying them:

```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="routes"
```

This will copy the routes to your `routes/` directory where you can modify them as needed.

## Troubleshooting

### Route Not Found Errors
If you encounter route not found errors:

1. Make sure you've included the routes file in the correct location
2. Check that your middleware isn't blocking the routes
3. Verify that the route naming matches your application's pattern
4. Use `php artisan route:list` to see all registered routes

### Localization Issues
If localized routes aren't working:

1. Ensure your localization middleware is set up correctly
2. Check that the locale is being set properly in your application
3. Verify that the route names match the expected pattern

### Permission Issues
If you get authorization errors:

1. Make sure your authentication middleware is correctly configured
2. Check that users have the necessary permissions
3. Verify that guards are set up properly for your use case

## Examples

Check the `examples/` directory for complete integration examples for popular Laravel setups:
- Standard Laravel application
- Laravel with Spatie Permission
- Multi-tenant application
- API-only integration
- Custom admin panel integration
