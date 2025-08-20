# CodeSnoutr Route Troubleshooting Guide

This guide helps you resolve common route-related issues with CodeSnoutr.

## Common Route Issues

### 1. Routes Not Loading

**Symptoms:**
- 404 errors when accessing CodeSnoutr URLs
- Routes missing from `php artisan route:list`

**Solutions:**

#### Check Service Provider Registration
```php
// config/app.php
'providers' => [
    // ...
    Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider::class,
],
```

#### Verify Auto-Loading Configuration
```php
// config/codesnoutr.php
'auto_load_routes' => true,  // Should be true
```

#### Clear Caches
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

#### Check Package Installation
```bash
composer show rafaelogic/codesnoutr
```

### 2. Middleware Conflicts

**Symptoms:**
- CSRF token errors
- Authentication issues
- Middleware order problems

**Solutions:**

#### Ensure Web Middleware Group
```php
// If using custom routes
Route::middleware(['web'])
    ->prefix('codesnoutr')
    ->group(function () {
        // Routes
    });
```

#### Check CSRF Exclusions
```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'codesnoutr/api/*',  // If needed for API routes
];
```

#### Verify Session Middleware
Ensure session middleware is active for web routes.

### 3. Route Conflicts

**Symptoms:**
- Wrong controller handling requests
- Unexpected route matches

**Solutions:**

#### Check Route Order
More specific routes should come before general ones:
```php
Route::get('admin/codesnoutr', [AdminController::class, 'index']);
Route::get('codesnoutr/{any}', [CodeSnoutrController::class, 'catch']);
```

#### Use Explicit Route Names
```php
Route::name('admin.codesnoutr.')->group(function () {
    // Admin routes
});

Route::name('codesnoutr.')->group(function () {
    // Regular routes
});
```

### 4. Authentication Issues

**Symptoms:**
- Redirected to login unexpectedly
- Access denied errors

**Solutions:**

#### Check Route Middleware
```php
// For public access
Route::middleware(['web'])->group(function () {
    // Routes
});

// For authenticated access
Route::middleware(['web', 'auth'])->group(function () {
    // Routes
});
```

#### Verify Guard Configuration
```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],
```

### 5. Route Model Binding Issues

**Symptoms:**
- Model not found errors
- Wrong model instances

**Solutions:**

#### Check Model Imports
```php
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
```

#### Verify Route Parameters
```php
Route::get('scan/{scan}', function (Scan $scan) {
    // $scan will be automatically resolved
});
```

#### Custom Route Keys
```php
// In the Scan model
public function getRouteKeyName()
{
    return 'uuid';  // Use UUID instead of ID
}
```

## Debugging Commands

### List All Routes
```bash
php artisan route:list
```

### Filter CodeSnoutr Routes
```bash
php artisan route:list --name=codesnoutr
```

### Show Route Details
```bash
php artisan route:list --path=codesnoutr --columns=uri,name,action,middleware
```

### Test Route Resolution
```bash
php artisan tinker
>>> route('codesnoutr.dashboard')
>>> app('router')->getRoutes()->match(request()->create('/codesnoutr'))
```

## Environment-Specific Issues

### Development Environment

#### Laravel Telescope
If using Telescope, ensure it doesn't conflict:
```php
// config/telescope.php
'path' => 'telescope',  // Not 'codesnoutr'
```

#### Debugbar
May interfere with AJAX requests:
```php
// config/debugbar.php
'enabled' => env('DEBUGBAR_ENABLED', false),
```

### Production Environment

#### Route Caching
Routes may be cached in production:
```bash
php artisan route:cache
```

If issues persist:
```bash
php artisan route:clear
php artisan route:cache
```

#### Opcache
Clear PHP opcache if routes don't update:
```bash
php artisan opcache:clear  # If available
```

Or restart PHP-FPM:
```bash
sudo service php8.1-fpm restart
```

## Specific Error Messages

### "Route [codesnoutr.dashboard] not defined"

**Cause:** Routes not loaded properly

**Solution:**
1. Check service provider registration
2. Verify `auto_load_routes` configuration
3. Clear caches and restart server

### "Class 'Rafaelogic\CodeSnoutr\Http\Controllers\...' not found"

**Cause:** Autoloader issues or missing files

**Solution:**
1. Run `composer dump-autoload`
2. Verify package installation
3. Check for file permissions

### "CSRF token mismatch"

**Cause:** CSRF protection active on API routes

**Solution:**
1. Exclude API routes from CSRF verification
2. Include CSRF token in requests
3. Check middleware configuration

### "Method ... does not exist"

**Cause:** Route pointing to wrong controller method

**Solution:**
1. Verify controller method names
2. Check route definitions
3. Clear route cache

## Advanced Debugging

### Custom Route Debugging

Add debug middleware:
```php
Route::middleware([function ($request, $next) {
    logger('CodeSnoutr route accessed: ' . $request->path());
    return $next($request);
}])->group(function () {
    // Routes
});
```

### Route Registration Debugging

Check if routes are being registered:
```php
// In a service provider
public function boot()
{
    logger('CodeSnoutr routes loading...');
    $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    logger('CodeSnoutr routes loaded: ' . count(Route::getRoutes()));
}
```

### Request Lifecycle Debugging

```php
// In AppServiceProvider
public function boot()
{
    if (app()->environment('local')) {
        Event::listen('router.matched', function ($event) {
            if (str_contains($event->route->getName(), 'codesnoutr')) {
                logger('CodeSnoutr route matched: ' . $event->route->getName());
            }
        });
    }
}
```

## Configuration Verification

### Verify Package Configuration

```bash
php artisan config:show codesnoutr
```

### Check Route Configuration

```php
// routes/web.php or custom route file
Route::prefix('codesnoutr')
    ->name('codesnoutr.')
    ->middleware(['web'])  // Essential for session/CSRF
    ->group(function () {
        // Verify this group exists
    });
```

## Performance Considerations

### Route Caching in Production

Always cache routes in production:
```bash
php artisan route:cache
```

### Optimize Route Loading

```php
// Only load routes when needed
if (request()->is('codesnoutr*')) {
    $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
}
```

## Integration Testing

### Test Route Registration

```php
// tests/Feature/RouteTest.php
public function test_codesnoutr_routes_are_registered()
{
    $routes = collect(Route::getRoutes())
        ->filter(fn($route) => str_starts_with($route->getName(), 'codesnoutr.'));
    
    $this->assertGreaterThan(0, $routes->count());
}
```

### Test Route Access

```php
public function test_dashboard_is_accessible()
{
    $response = $this->get(route('codesnoutr.dashboard'));
    $response->assertStatus(200);
}
```

## Getting Help

### Debug Information to Provide

When seeking help, provide:

1. Laravel version: `php artisan --version`
2. CodeSnoutr version: `composer show rafaelogic/codesnoutr`
3. Route list: `php artisan route:list --name=codesnoutr`
4. Configuration: `php artisan config:show codesnoutr`
5. Error messages and stack traces
6. Middleware configuration
7. Environment details

### Common Solutions Summary

1. **Clear all caches** first
2. **Check service provider registration**
3. **Verify middleware configuration**
4. **Ensure web middleware group is used**
5. **Check for route conflicts**
6. **Verify package installation**

For additional support, check the main troubleshooting guide or create an issue on GitHub.
