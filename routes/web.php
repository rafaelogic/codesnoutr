<?php

use Illuminate\Support\Facades\Route;
use Rafaelogic\CodeSnoutr\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| CodeSnoutr Routes
|--------------------------------------------------------------------------
|
| These routes can be easily integrated into your existing application's
| route structure. You can wrap them with your own middleware, localization,
| and guards as needed.
|
| Example integration in your main routes file:
|
| // Basic integration
| require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
|
| // With custom middleware and localization
| Route::group([
|     'middleware' => ['web', 'auth', 'your-custom-middleware'],
|     'prefix' => '{locale}/admin',
|     'where' => ['locale' => '[a-zA-Z]{2}']
| ], function() {
|     require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
| });
|
*/

// Define routes with web middleware for CSRF protection and session handling
Route::middleware(['web'])
    ->name('codesnoutr.')
    ->group(function () {
        
        // Dashboard
        Route::get('/codesnoutr', [DashboardController::class, 'index'])->name('dashboard');
        
        // Wizard route (new modern scan experience)
        Route::get('/codesnoutr/wizard', [DashboardController::class, 'wizard'])->name('wizard');
        
        // Scan management
        Route::get('/codesnoutr/scan', [DashboardController::class, 'scan'])->name('scan');
        Route::get('/codesnoutr/scan/{scan}', [DashboardController::class, 'show'])->name('scan.show');
        Route::get('/codesnoutr/scan/{id}/view', [DashboardController::class, 'viewScan'])->name('scan.view');
        
        // Clean scan results view (new dedicated page)
        Route::get('/scan/{id}', [DashboardController::class, 'viewScan'])->name('scan.clean-view');
        
        // Results
        Route::get('/codesnoutr/results', [DashboardController::class, 'results'])->name('results');
        Route::get('/codesnoutr/results/{scan}', [DashboardController::class, 'scanResults'])->name('results.scan');
        Route::get('/codesnoutr/scan-results', [DashboardController::class, 'results'])->name('scan-results');
        Route::get('/codesnoutr/scan-results/{scan}', [DashboardController::class, 'scanResults'])->name('scan-results.show');
        
        // Group file details (simplified for debugging)
        Route::get('/codesnoutr/scan-results/{scan}/group/{title}/{category}/{severity}', [DashboardController::class, 'groupFileDetails'])
            ->name('scan-results.group-details');
            
        // Test route for debugging
        Route::get('/codesnoutr/test-group/{scan}/{title}/{category}/{severity}', function($scan, $title, $category, $severity) {
            return response()->json([
                'message' => 'Route working!',
                'parameters' => [
                    'scan' => $scan,
                    'title' => urldecode($title),
                    'category' => $category,
                    'severity' => $severity,
                ],
                'url' => request()->url(),
            ]);
        })->name('test-group-details');
            
        // Debug route to test route loading
        Route::get('/codesnoutr/debug/routes', [DashboardController::class, 'debugRoutes'])->name('debug.routes');
        
        // Debug route to test CSRF token
        Route::get('/codesnoutr/debug/csrf', [DashboardController::class, 'debugCsrf'])->name('debug.csrf');
        
        // Settings
        Route::get('/codesnoutr/settings', [DashboardController::class, 'settings'])->name('settings');
        
        // Test Livewire route
        Route::get('/codesnoutr/test-livewire', function() {
            return view('codesnoutr::test-livewire');
        })->name('test-livewire');
        
        // Reports
        Route::get('/codesnoutr/reports', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/codesnoutr/export/{scan}/{format?}', [DashboardController::class, 'export'])->name('export');
    });

// API Routes (optional for future use)
Route::prefix('api/codesnoutr')
    ->name('api.codesnoutr.')
    ->middleware(['api'])
    ->group(function () {
        
        // Scan endpoints
        Route::post('/scan', [DashboardController::class, 'apiScan'])->name('scan');
        Route::get('/scan/{scan}', [DashboardController::class, 'apiShow'])->name('scan.show');
        
        // Statistics
        Route::get('/stats', [DashboardController::class, 'apiStats'])->name('stats');
        
        // Health check
        Route::get('/health', [DashboardController::class, 'apiHealth'])->name('health');
    });
