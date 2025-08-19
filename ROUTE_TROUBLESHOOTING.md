# CodeSnoutr Route Troubleshooting Guide

## Common Issues and Solutions

### 1. 404 Error on Group Details Page

If you're getting a 404 error when clicking "View Details" on grouped scan results, try these solutions:

#### Solution A: Check Route Loading

1. Visit `/codesnoutr/debug/routes` to see if routes are loaded correctly
2. Look for `codesnoutr.scan-results.group-details` in the response

#### Solution B: Publish Routes (Recommended)

```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-routes"
```

Then add the routes to your main routes file with proper middleware:

```php
// In routes/web.php or routes/codesnoutr.php
Route::group([
    'middleware' => ['web', 'auth'], // Add your middleware
    'prefix' => '{locale}',          // Add locale prefix if needed
    'where' => ['locale' => '[a-zA-Z]{2}']
], function() {
    require base_path('routes/codesnoutr.php');
});
```

#### Solution C: Disable Auto-loading and Manual Integration

In your `config/codesnoutr.php`:

```php
'auto_load_routes' => false,
```

Then manually load routes with your preferred structure:

```php
// In your RouteServiceProvider or web.php
Route::group([
    'middleware' => ['web', 'auth'],
    'prefix' => 'admin', // or your preferred prefix
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

#### Solution D: Check URL Construction

If routes are not working, the package will fall back to manual URL construction. 
Make sure your `config/app.php` has the correct `url` setting:

```php
'url' => env('APP_URL', 'http://localhost'),
```

### 2. Localization Issues

If you're using localization, ensure routes are properly prefixed:

```php
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
    'middleware' => ['web', 'setlocale']
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

### 3. Testing Route Registration

Use the debug route to test:

1. Visit: `/codesnoutr/debug/routes`
2. Visit: `/codesnoutr/test-group/12/Test%20Title/quality/info`

### 4. Manual URL Testing

Try accessing the route directly:
`/codesnoutr/scan-results/12/group/Potential%20Unused%20Variable/quality/info`

Where:
- `12` = Scan ID
- `Potential%20Unused%20Variable` = URL-encoded issue title
- `quality` = Category
- `info` = Severity

## Route Integration Examples

### Basic Integration
```php
require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
```

### With Middleware
```php
Route::group(['middleware' => ['web', 'auth']], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

### With Prefix and Localization
```php
Route::group([
    'middleware' => ['web', 'auth'],
    'prefix' => '{locale}/admin',
    'where' => ['locale' => '[a-zA-Z]{2}']
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```
