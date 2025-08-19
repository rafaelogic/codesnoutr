# Route Integration

CodeSnoutr is designed to integrate seamlessly with your existing Laravel application's routing structure, middleware, guards, and localization setup.

## Quick Start

### Option 1: Automatic Integration (Default)
By default, CodeSnoutr will automatically load its routes with minimal middleware. This works out of the box but may not fit all applications.

### Option 2: Custom Integration
For better control, disable automatic route loading and include the routes manually:

1. **Disable auto-loading** in your `.env`:
   ```env
   CODESNOUTR_AUTO_LOAD_ROUTES=false
   ```

2. **Include routes manually** in your `routes/web.php`:
   ```php
   // Basic integration with authentication
   Route::middleware(['web', 'auth'])->group(function () {
       require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
   });
   ```

### Option 3: Publish and Customize
Publish the routes to your application and modify them as needed:

```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-routes"
```

This creates `routes/codesnoutr.php` which you can then customize and include in your main routes file.

## Common Integration Patterns

### With Localization
```php
// Laravel Localization package
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'web', 'auth']
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});

// Custom localization
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
    'middleware' => ['web', 'auth', 'set-locale']
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

### With Admin Panels
```php
// Laravel Nova
Route::prefix('nova-vendor/codesnoutr')->group(function () {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});

// Filament
Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});

// Custom admin
Route::prefix('admin')
    ->middleware(['web', 'auth:admin', 'can:access-admin'])
    ->group(function () {
        require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
    });
```

### With Custom Guards and Permissions
```php
Route::middleware(['web', 'auth:admin', 'can:manage-security'])
    ->prefix('security')
    ->group(function () {
        require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
    });
```

## Route Names

CodeSnoutr routes use the `codesnoutr.` prefix. When wrapped in additional route groups, the names are automatically prefixed:

- Base: `codesnoutr.dashboard`
- Localized: `en.codesnoutr.dashboard`
- Admin: `admin.codesnoutr.dashboard`
- Combined: `en.admin.codesnoutr.dashboard`

The package automatically detects these patterns and generates correct URLs.

## Full Documentation

For complete integration examples and troubleshooting, publish the documentation:

```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-docs"
```

This will create `docs/codesnoutr-integration.md` with comprehensive examples for various Laravel setups.
