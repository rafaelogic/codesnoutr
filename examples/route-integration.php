<?php

/*
|--------------------------------------------------------------------------
| CodeSnoutr Routes Integration Example
|--------------------------------------------------------------------------
|
| This file shows various ways to integrate CodeSnoutr routes into your
| Laravel application. Choose the approach that best fits your needs.
|
*/

// Example 1: Basic integration with authentication
Route::middleware(['web', 'auth'])->group(function () {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});

// Example 2: Integration with Laravel Localization (mcamara/laravel-localization)
/*
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'web', 'auth']
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
*/

// Example 3: Custom localization with admin prefix
/*
Route::group([
    'prefix' => '{locale}/admin',
    'where' => ['locale' => '[a-zA-Z]{2}'],
    'middleware' => ['web', 'auth', 'set-locale', 'admin'],
    'name' => 'admin.'
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
*/

// Example 4: API integration
/*
Route::prefix('api/v1')
    ->middleware(['api', 'auth:sanctum'])
    ->name('api.')
    ->group(function () {
        require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
    });
*/

// Example 5: Multi-tenant integration (subdomain)
/*
Route::group([
    'domain' => '{tenant}.yourdomain.com',
    'middleware' => ['web', 'auth', 'tenant'],
    'name' => 'tenant.'
], function() {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
*/

// Example 6: Integration with custom guards and permissions
/*
Route::middleware(['web', 'auth:admin', 'can:manage-code-scanner'])
    ->prefix('admin/security')
    ->name('admin.security.')
    ->group(function () {
        require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
    });
*/
