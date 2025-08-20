# CodeSnoutr Route Integration Guide

This guide explains how to integrate CodeSnoutr routes into your Laravel application.

## Automatic Route Loading (Recommended)

By default, CodeSnoutr automatically loads its routes. This is controlled by the `auto_load_routes` configuration:

```php
// config/codesnoutr.php
'auto_load_routes' => true,  // Default
```

**Automatic routes are loaded at the following prefix:**
- `/codesnoutr/*` - All CodeSnoutr routes

## Manual Route Integration

If you prefer manual control over routes, you can:

1. **Disable automatic loading:**
```php
// config/codesnoutr.php
'auto_load_routes' => false,
```

2. **Publish the routes:**
```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-routes"
```

3. **Include routes manually:**
```php
// routes/web.php or dedicated route file
require_once base_path('routes/codesnoutr.php');
```

## Available Routes

CodeSnoutr provides the following routes:

### Dashboard Routes
- `GET /codesnoutr` - Main dashboard
- `GET /codesnoutr/dashboard` - Dashboard (alias)

### Scanning Routes
- `GET /codesnoutr/scan` - Scan form
- `POST /codesnoutr/scan` - Start new scan
- `GET /codesnoutr/scan/wizard` - Scan wizard
- `GET /codesnoutr/scan/{scan}` - View specific scan

### Results Routes
- `GET /codesnoutr/results` - All scan results
- `GET /codesnoutr/results/{scan}` - Specific scan results
- `GET /codesnoutr/results/issue/{issue}` - Issue details

### Settings Routes
- `GET /codesnoutr/settings` - Settings page
- `POST /codesnoutr/settings` - Update settings

### API Routes
- `GET /codesnoutr/api/scan/{scan}/status` - Scan status
- `POST /codesnoutr/api/scan/{scan}/stop` - Stop scan
- `DELETE /codesnoutr/api/scan/{scan}` - Delete scan

## Route Customization

### Custom Route Prefix

To change the route prefix, publish and modify the routes:

```php
// routes/codesnoutr.php (after publishing)
Route::prefix('code-analysis')  // Changed from 'codesnoutr'
    ->name('codesnoutr.')
    ->middleware(['web'])
    ->group(function () {
        // ... routes
    });
```

### Custom Middleware

Add authentication or other middleware:

```php
// routes/codesnoutr.php
Route::prefix('codesnoutr')
    ->name('codesnoutr.')
    ->middleware(['web', 'auth', 'verified'])  // Added auth middleware
    ->group(function () {
        // ... routes
    });
```

### Route Model Binding

CodeSnoutr uses route model binding for scans and issues:

```php
// Automatic binding in controllers
public function show(Scan $scan)
{
    // $scan is automatically resolved
}
```

## Integration Examples

### Laravel Breeze/Jetstream Integration

Add CodeSnoutr links to your navigation:

```blade
{{-- resources/views/navigation.blade.php --}}
<x-nav-link :href="route('codesnoutr.dashboard')" :active="request()->routeIs('codesnoutr.*')">
    {{ __('Code Analysis') }}
</x-nav-link>
```

### Fortify Integration

Protect routes with authentication:

```php
// routes/codesnoutr.php
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // ... CodeSnoutr routes
});
```

### Spatie Permission Integration

Add role-based access:

```php
// routes/codesnoutr.php
Route::middleware(['permission:view-code-analysis'])->group(function () {
    // Read-only routes
});

Route::middleware(['permission:manage-code-analysis'])->group(function () {
    // Admin routes
});
```

## Route Naming Convention

All CodeSnoutr routes use the `codesnoutr.` prefix:

- `codesnoutr.dashboard`
- `codesnoutr.scan`
- `codesnoutr.scan.store`
- `codesnoutr.results`
- `codesnoutr.results.scan`
- `codesnoutr.settings`

## URL Generation

Generate URLs to CodeSnoutr routes:

```php
// In controllers
return redirect()->route('codesnoutr.dashboard');

// In views
<a href="{{ route('codesnoutr.scan') }}">Start Scan</a>

// With parameters
<a href="{{ route('codesnoutr.results.scan', $scan) }}">View Results</a>
```

## Route Caching

CodeSnoutr routes are compatible with Laravel's route caching:

```bash
php artisan route:cache
```

If you experience issues, clear the cache:

```bash
php artisan route:clear
```

## Subdomain Integration

To use CodeSnoutr on a subdomain:

```php
// routes/codesnoutr.php
Route::domain('analysis.' . config('app.domain'))
    ->name('codesnoutr.')
    ->middleware(['web'])
    ->group(function () {
        // ... routes without prefix
    });
```

## API-Only Integration

For API-only usage:

```php
// routes/api.php
Route::prefix('codesnoutr')
    ->name('api.codesnoutr.')
    ->middleware(['api'])
    ->group(function () {
        // API routes only
        Route::get('scan/{scan}/status', [ScanController::class, 'status']);
        Route::post('scan', [ScanController::class, 'store']);
        // ... other API routes
    });
```

## Route Debugging

Debug route registration:

```bash
# List all routes
php artisan route:list

# Filter CodeSnoutr routes
php artisan route:list --name=codesnoutr

# Show specific route
php artisan route:list --path=codesnoutr
```

## Common Issues and Solutions

### Routes Not Found
1. Check if `auto_load_routes` is enabled
2. Ensure service provider is registered
3. Clear route cache: `php artisan route:clear`

### Middleware Conflicts
1. Check middleware compatibility
2. Ensure session middleware is included
3. Verify CSRF token handling

### Route Conflicts
1. Check for naming conflicts
2. Ensure unique prefixes
3. Review route order (more specific first)

### Permission Issues
1. Verify middleware order
2. Check route parameter binding
3. Ensure proper authentication flow

For more specific issues, see the troubleshooting guide.
