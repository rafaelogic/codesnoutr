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
        Route::get('/codesnoutr/results/{scan}/by-issues', [DashboardController::class, 'scanResultsByIssues'])->name('results.scan.by-issues');
        
        // Debug route
        Route::get('/codesnoutr/results-debug', function() {
            $query = \Rafaelogic\CodeSnoutr\Models\Scan::with(['issues'])->orderBy('created_at', 'desc');
            $scans = $query->paginate(15);
            return view('codesnoutr::pages.results-debug', compact('scans'));
        })->name('results.debug');
        
        // Create sample data route (for testing)
        Route::get('/codesnoutr/create-sample-data', function() {
            try {
                $seeder = new \Rafaelogic\CodeSnoutr\Database\Seeders\ScanSeeder();
                $seeder->run();
                return redirect()->route('codesnoutr.results')->with('success', 'Sample data created successfully!');
            } catch (\Exception $e) {
                return redirect()->route('codesnoutr.results')->with('error', 'Error creating sample data: ' . $e->getMessage());
            }
        })->name('create-sample-data');
        
        // Test data route
        Route::get('/codesnoutr/test-data', function() {
            $totalScans = \Rafaelogic\CodeSnoutr\Models\Scan::count();
            $scans = \Rafaelogic\CodeSnoutr\Models\Scan::all();
            $paginatedScans = \Rafaelogic\CodeSnoutr\Models\Scan::paginate(15);
            
            return response()->json([
                'total_scans' => $totalScans,
                'scans_collection_count' => $scans->count(),
                'paginated_count' => $paginatedScans->count(),
                'paginated_total' => $paginatedScans->total(),
                'current_page' => $paginatedScans->currentPage(),
                'per_page' => $paginatedScans->perPage(),
                'first_scan' => $scans->first(),
                'pagination_data' => [
                    'from' => $paginatedScans->firstItem(),
                    'to' => $paginatedScans->lastItem(),
                    'has_pages' => $paginatedScans->hasPages(),
                ]
            ]);
        })->name('test-data');
        Route::get('/codesnoutr/scan-results', [DashboardController::class, 'results'])->name('scan-results');
        Route::get('/codesnoutr/scan-results/{scan}', [DashboardController::class, 'scanResults'])->name('scan-results.show');
        
        // Group file details (simplified for debugging)
        Route::get('/codesnoutr/scan-results/{scan}/group/{title}/{category}/{severity}', [DashboardController::class, 'groupFileDetails'])
            ->name('scan-results.group-details');
            
        // Settings
        Route::get('/codesnoutr/settings', [DashboardController::class, 'settings'])->name('settings');
        
        // Reports
        Route::get('/codesnoutr/reports', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/codesnoutr/export/{scan}/{format?}', [DashboardController::class, 'export'])->name('export');
        
        // Dark Mode Test Page
        Route::get('/dark-mode-test', function () {
    return view('codesnoutr::pages.dark-mode-test');
})->name('codesnoutr.dark-mode-test');

Route::get('/input-dark-mode-test', function () {
    return view('codesnoutr::pages.input-dark-mode-test');
})->name('codesnoutr.input-dark-mode-test');
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
