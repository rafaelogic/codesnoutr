# CodeSnoutr Troubleshooting Guide

## "View Details" Not Working

If the "View Details" button is not working, follow these steps:

### 1. Check if Livewire Component is Registered

Ensure the `GroupFileDetails` component is registered in your service provider:

```php
// In CodeSnoutrServiceProvider.php
\Livewire\Livewire::component('codesnoutr-group-file-details', \Rafaelogic\CodeSnoutr\Livewire\GroupFileDetails::class);
```

### 2. Verify Route Registration

Make sure the routes are properly registered with your application's localization setup:

```php
// Example for localized routes in your main routes file
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'web', 'auth']
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

### 3. Clear Application Cache

```bash
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

### 4. Check Route List

Verify that the route exists:

```bash
php artisan route:list | grep group-details
```

You should see something like:
- `GET|HEAD en/codesnoutr/scan-results/{scan}/group/{title}/{category}/{severity}`
- Or `GET|HEAD codesnoutr/scan-results/{scan}/group/{title}/{category}/{severity}`

### 5. Debug Route URL Generation

If the "View Details" button shows "#" as the URL, add this debug code temporarily:

```php
// In your blade template, add this before the button:
@php
    dd([
        'locale' => app()->getLocale(),
        'route_exists_localized' => Route::has(app()->getLocale() . '.codesnoutr.scan-results.group-details'),
        'route_exists_base' => Route::has('codesnoutr.scan-results.group-details'),
        'available_routes' => collect(Route::getRoutes())->filter(function($route) {
            return str_contains($route->getName() ?? '', 'group-details');
        })->map(function($route) {
            return $route->getName();
        })->values()->toArray()
    ]);
@endphp
```

### 6. Alternative Solutions

If routes are still not working, you can use these alternatives:

#### Option A: Use Livewire Navigation
```php
// In the scan results view, replace the link with:
<button wire:click="$emit('showGroupDetails', '{{ $group['title'] }}', '{{ $group['category'] }}', '{{ $group['severity'] }}')"
        class="...">
    View Details
</button>

// In your ScanResults component, add:
protected $listeners = ['showGroupDetails'];

public function showGroupDetails($title, $category, $severity)
{
    return redirect()->to(
        request()->url() . '/group/' . 
        urlencode($title) . '/' . 
        $category . '/' . 
        $severity
    );
}
```

#### Option B: Manual URL Construction
```php
// Replace the complex route detection with:
@php
    $detailsUrl = request()->url() . '/group/' . 
                  urlencode($group['title']) . '/' . 
                  $group['category'] . '/' . 
                  $group['severity'];
@endphp
```

#### Option C: Use JavaScript Navigation
```html
<button onclick="window.location.href='{{ request()->url() }}/group/{{ urlencode($group['title']) }}/{{ $group['category'] }}/{{ $group['severity'] }}'"
        class="...">
    View Details
</button>
```

### 7. Check for JavaScript Errors

Open browser developer tools and check for any JavaScript errors that might prevent navigation.

### 8. Verify Middleware and Permissions

Ensure your user has the necessary permissions to access the detail pages and that middleware is not blocking access.

### 9. Test with Simple Route

Create a test route to verify basic functionality:

```php
// Add to your routes file temporarily
Route::get('/test-group-details', function() {
    return view('codesnoutr::livewire.group-file-details', [
        'scanId' => 1,
        'groupTitle' => 'Test Issue',
        'groupCategory' => 'security',
        'groupSeverity' => 'high'
    ]);
})->name('test.group-details');
```

### 10. Contact Support

If none of the above solutions work, please provide:
- Laravel version
- Your route configuration
- Any error messages from logs
- Output of `php artisan route:list | grep codesnoutr`

## UI Enhancement Features

The enhanced UI includes:

### New Features
- **Enhanced file type detection** with specific icons for different file types
- **Progress indicators** showing issue resolution status
- **Interactive hover effects** with smooth animations
- **Better color coding** for different severity levels
- **Improved file previews** with more metadata
- **Enhanced navigation** with breadcrumbs and action buttons
- **Better responsive design** for mobile and tablet views

### Customization

You can customize the styling by publishing the views:

```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-assets"
```

Then modify the CSS classes in:
- `resources/views/vendor/codesnoutr/livewire/scan-results.blade.php`
- `resources/views/vendor/codesnoutr/livewire/group-file-details.blade.php`
