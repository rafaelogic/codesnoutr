# CodeSnoutr 419 CSRF Error Troubleshooting Guide

## The 419 Error

A 419 error in Laravel indicates a CSRF (Cross-Site Request Forgery) token mismatch. This typically happens when:

1. CSRF token is missing or invalid
2. Session configuration issues
3. Middleware not properly loaded
4. Application key not set

## Quick Diagnostics

### 1. Check Debug Information

Visit these debug URLs to check system status:
- `/codesnoutr/debug/csrf` - Check CSRF and session status
- `/codesnoutr/debug/routes` - Check route loading

### 2. Verify Application Configuration

Check your `.env` file:
```env
# Application key must be set
APP_KEY=base64:your-32-character-key

# Session configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
```

### 3. Check Session Directory Permissions

```bash
# Make sure storage/framework/sessions is writable
chmod -R 775 storage/framework/sessions
chown -R www-data:www-data storage/framework/sessions
```

## Common Solutions

### Solution 1: Regenerate Application Key

```bash
php artisan key:generate
```

### Solution 2: Clear Cache and Sessions

```bash
php artisan cache:clear
php artisan session:flush
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Solution 3: Check Session Configuration

In `config/session.php`:
```php
return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION', null),
    'table' => 'sessions',
    'store' => env('SESSION_STORE', null),
    'lottery' => [2, 100],
    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN', null),
    'secure' => env('SESSION_SECURE_COOKIE'),
    'http_only' => true,
    'same_site' => 'lax',
];
```

### Solution 4: Middleware Integration Issues

If using localization or custom middleware, wrap CodeSnoutr routes properly:

```php
// In your main routes file or RouteServiceProvider
Route::group([
    'middleware' => ['web'], // Essential for CSRF protection
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

### Solution 5: Disable Auto-loading and Manual Integration

In `config/codesnoutr.php`:
```php
'auto_load_routes' => false,
```

Then manually load in your RouteServiceProvider:
```php
public function map()
{
    $this->mapWebRoutes();
    $this->mapApiRoutes();
    $this->mapCodeSnoutrRoutes(); // Add this
}

protected function mapCodeSnoutrRoutes()
{
    Route::middleware('web')
        ->group(base_path('vendor/rafaelogic/codesnoutr/routes/web.php'));
}
```

### Solution 6: Database Session Driver

If using database sessions, make sure the table exists:

```bash
php artisan session:table
php artisan migrate
```

### Solution 7: Redis Session Driver

If using Redis, ensure Redis is running and configured:
```env
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Livewire-Specific Issues

### Check Livewire Configuration

In `config/livewire.php`:
```php
return [
    'class_namespace' => 'App\\Http\\Livewire',
    'view_path' => resource_path('views/livewire'),
    'layout' => 'layouts.app',
    'lazy_loading_placeholder' => null,
    'temporary_file_upload' => [
        'disk' => null,
        'rules' => null,
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
    ],
    'render_on_redirect' => false,
    'legacy_model_binding' => false,
    'inject_assets' => true,
    'navigate' => [
        'show_progress_bar' => true,
    ],
];
```

### Livewire Asset Publishing

```bash
php artisan livewire:publish --config
php artisan livewire:publish --assets
```

## Development vs Production

### Development Environment
- Check if `APP_DEBUG=true` for detailed error messages
- Verify `.env` configuration

### Production Environment
- Ensure `APP_DEBUG=false`
- Check server configuration
- Verify SSL/TLS settings if using HTTPS

## Testing the Fix

After applying solutions:

1. Clear browser cache and cookies
2. Visit `/codesnoutr/debug/csrf` to verify configuration
3. Try accessing CodeSnoutr pages
4. Check browser developer tools for any JavaScript errors

## Advanced Debugging

### Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

### Check Web Server Logs
```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/error.log
```

### Browser Network Tab
- Check if CSRF token is being sent in requests
- Look for failed XHR/Fetch requests
- Verify response headers

## Last Resort Solutions

### 1. Temporary CSRF Disable (NOT for production)
In `app/Http/Middleware/VerifyCsrfToken.php`:
```php
protected $except = [
    'codesnoutr/*', // ONLY for debugging - remove this!
];
```

### 2. Fresh Laravel Installation Test
Create a fresh Laravel project and test CodeSnoutr installation to isolate the issue.

### 3. Contact Support
If all else fails, provide:
- Laravel version
- PHP version
- CodeSnoutr version
- Complete error logs
- Configuration files
