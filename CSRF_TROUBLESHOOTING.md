# CodeSnoutr CSRF Troubleshooting Guide

This guide helps resolve CSRF (Cross-Site Request Forgery) token issues with CodeSnoutr.

## Understanding CSRF in CodeSnoutr

CodeSnoutr uses Laravel's built-in CSRF protection for all forms and state-changing requests. This ensures security but can sometimes cause issues during integration.

## Common CSRF Errors

### 1. "CSRF token mismatch" Error

**Symptoms:**
- 419 error pages on form submissions
- AJAX requests failing with 419 status
- Forms not submitting properly

**Common Causes:**
- Missing or invalid CSRF tokens
- Session configuration issues
- Middleware conflicts
- Cached forms with expired tokens

## Solutions

### Basic CSRF Token Inclusion

#### In Blade Forms
```blade
{{-- Method 1: CSRF field helper --}}
<form method="POST" action="{{ route('codesnoutr.scan.store') }}">
    @csrf
    <!-- form fields -->
</form>

{{-- Method 2: Manual token --}}
<form method="POST" action="{{ route('codesnoutr.scan.store') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <!-- form fields -->
</form>

{{-- Method 3: Meta tag for AJAX --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
```

#### In AJAX Requests
```javascript
// Method 1: Include token in headers
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Method 2: Include token in data
$.post('/codesnoutr/scan', {
    _token: $('meta[name="csrf-token"]').attr('content'),
    // other data
});

// Method 3: Using Axios
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
```

#### In Livewire Components
```php
// Livewire automatically handles CSRF tokens
class ScanForm extends Component
{
    public function startScan()
    {
        // No manual CSRF handling needed
    }
}
```

### Session Configuration Issues

#### Check Session Configuration
```php
// config/session.php
'driver' => env('SESSION_DRIVER', 'file'),
'lifetime' => 120,  // Session lifetime in minutes
'path' => '/',
'domain' => env('SESSION_DOMAIN', null),
'secure' => env('SESSION_SECURE_COOKIE', false),
'http_only' => true,
'same_site' => 'lax',
```

#### Common Session Problems

**Problem:** Session driver issues
```php
// Solution: Use file or database driver
'driver' => 'file',  // or 'database'
```

**Problem:** Session domain mismatch
```php
// Solution: Set correct domain
'domain' => '.yourdomain.com',  // Include subdomain
```

**Problem:** HTTPS cookie issues
```php
// Solution: Match your environment
'secure' => env('SESSION_SECURE_COOKIE', true),  // true for HTTPS
```

### Middleware Configuration

#### Verify CSRF Middleware
```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ...
        \App\Http\Middleware\VerifyCsrfToken::class,
        // ...
    ],
];
```

#### Exclude Specific Routes (if needed)
```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'codesnoutr/api/*',      // API routes
    'codesnoutr/webhook/*',  // Webhook routes
];
```

**⚠️ Warning:** Only exclude routes that don't need CSRF protection (APIs, webhooks).

### Environment-Specific Issues

#### Development Environment

**Problem:** Mixed HTTP/HTTPS
```bash
# Solution: Ensure consistent protocol
APP_URL=https://yourdomain.local  # Match your actual setup
```

**Problem:** Localhost issues
```php
// config/session.php
'domain' => null,  // Don't set domain for localhost
```

#### Production Environment

**Problem:** Load balancer issues
```php
// Add to AppServiceProvider boot()
public function boot()
{
    if (request()->header('X-Forwarded-Proto') === 'https') {
        \URL::forceScheme('https');
    }
}
```

**Problem:** CDN/Proxy issues
```php
// config/trustedproxy.php
protected $proxies = '*';  // Or specific proxy IPs
```

### CodeSnoutr-Specific Solutions

#### Livewire CSRF Handling

Livewire components in CodeSnoutr handle CSRF automatically, but ensure:

```blade
{{-- Include Livewire scripts --}}
@livewireScripts

{{-- Ensure proper layout --}}
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @livewireStyles
</head>
<body>
    @livewire('codesnoutr-scan-form')
    @livewireScripts
</body>
</html>
```

#### AJAX Forms in CodeSnoutr

```javascript
// CodeSnoutr JavaScript handles CSRF automatically
// But ensure meta tag is present
document.querySelector('meta[name="csrf-token"]');
```

#### File Upload CSRF

```blade
{{-- File upload forms need enctype --}}
<form method="POST" action="{{ route('codesnoutr.upload') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file">
    <button type="submit">Upload</button>
</form>
```

## Advanced CSRF Configuration

### Custom CSRF Token Names

```php
// app/Http/Middleware/VerifyCsrfToken.php
protected function tokensMatch($request)
{
    $token = $this->getTokenFromRequest($request);
    
    return is_string($request->session()->token()) &&
           is_string($token) &&
           hash_equals($request->session()->token(), $token);
}
```

### CSRF for SPA Integration

For Single Page Applications:

```php
// routes/web.php
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});
```

```javascript
// SPA setup
await axios.get('/sanctum/csrf-cookie');
// Now make authenticated requests
```

### API Token Alternative

For API-based integration:

```php
// Use Sanctum or Passport instead of CSRF
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/codesnoutr/api/scan', [ScanController::class, 'store']);
});
```

## Debugging CSRF Issues

### Check Token Generation

```blade
{{-- Debug CSRF token --}}
<script>
    console.log('CSRF Token:', '{{ csrf_token() }}');
    console.log('Meta CSRF:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
</script>
```

### Log CSRF Verification

```php
// In VerifyCsrfToken middleware
protected function tokensMatch($request)
{
    $sessionToken = $request->session()->token();
    $requestToken = $this->getTokenFromRequest($request);
    
    logger('CSRF Debug', [
        'session_token' => $sessionToken,
        'request_token' => $requestToken,
        'match' => hash_equals($sessionToken, $requestToken)
    ]);
    
    return parent::tokensMatch($request);
}
```

### Check Session Data

```php
// In controller or middleware
logger('Session Data', [
    'session_id' => session()->getId(),
    'csrf_token' => session()->token(),
    'session_data' => session()->all()
]);
```

## Testing CSRF Protection

### Test CSRF Token Generation

```php
// tests/Feature/CsrfTest.php
public function test_csrf_token_is_generated()
{
    $response = $this->get('/codesnoutr');
    
    $response->assertStatus(200);
    $this->assertNotEmpty(csrf_token());
}
```

### Test Form Submission

```php
public function test_form_submission_with_csrf()
{
    $response = $this->post('/codesnoutr/scan', [
        '_token' => csrf_token(),
        'scan_type' => 'full'
    ]);
    
    $response->assertSuccessful();
}
```

### Test AJAX Requests

```php
public function test_ajax_request_with_csrf()
{
    $response = $this->post('/codesnoutr/api/scan', [
        'scan_type' => 'full'
    ], [
        'X-CSRF-TOKEN' => csrf_token()
    ]);
    
    $response->assertSuccessful();
}
```

## Security Best Practices

### 1. Always Use CSRF Protection

```php
// Don't disable CSRF globally
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    // Only exclude specific routes that need it
];
```

### 2. Secure Session Configuration

```php
// config/session.php
'secure' => true,        // HTTPS only in production
'http_only' => true,     // Prevent XSS
'same_site' => 'strict', // Prevent CSRF
```

### 3. Token Rotation

Laravel rotates CSRF tokens automatically, but ensure:

```php
// Don't cache forms with tokens
// Regenerate tokens after authentication
```

### 4. Validate Token Timing

```php
// Consider token lifetime
'lifetime' => 120, // 2 hours max
```

## Quick Fixes Checklist

When experiencing CSRF issues:

- [ ] Check if `@csrf` is included in forms
- [ ] Verify session configuration
- [ ] Ensure web middleware is applied
- [ ] Check for session storage issues
- [ ] Verify domain/protocol consistency
- [ ] Clear browser cookies/cache
- [ ] Check for middleware conflicts
- [ ] Verify AJAX headers include token
- [ ] Check session lifetime settings
- [ ] Ensure proper error handling

## Error Messages and Solutions

### "419 | Page Expired"

**Solution:**
1. Add `@csrf` to forms
2. Check session configuration
3. Clear browser cache
4. Verify session storage is writable

### "CSRF token mismatch" in logs

**Solution:**
1. Check token generation
2. Verify token transmission
3. Check session persistence
4. Verify domain configuration

### AJAX requests failing

**Solution:**
1. Include CSRF token in headers
2. Check AJAX setup
3. Verify meta tag exists
4. Check for JavaScript errors

## Integration with Other Packages

### Laravel Telescope

```php
// Don't let Telescope interfere
// config/telescope.php
'ignore_paths' => [
    'codesnoutr/*'  // If causing issues
],
```

### Laravel Debugbar

```php
// May interfere with AJAX
'enabled' => env('DEBUGBAR_ENABLED', false),
```

For additional CSRF support, consult the main troubleshooting guide or Laravel documentation.
