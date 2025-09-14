<?php

namespace Rafaelogic\CodeSnoutr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\ScanManager;
use Carbon\Carbon;

class DashboardController
{
    protected ScanManager $scanManager;

    public function __construct(ScanManager $scanManager)
    {
        $this->scanManager = $scanManager;
    }

    /**
     * Display the dashboard
     */
    public function index(): View
    {
        $stats = $this->getDashboardStats();
        $recentScans = $this->getRecentScans();

        return view('codesnoutr::pages.dashboard', compact('stats', 'recentScans'));
    }

    /**
     * Display the scan page
     */
    public function scan(): View
    {
        return view('codesnoutr::pages.scan');
    }

    /**
     * Display the wizard (modern scan experience)
     */
    public function wizard(): View
    {
        return view('codesnoutr::pages.wizard');
    }

    /**
     * Show a specific scan
     */
    public function show(Scan $scan): View
    {
        $scan->load(['issues' => function ($query) {
            $query->orderBy('severity', 'desc')
                  ->orderBy('created_at', 'desc');
        }]);

        return view('codesnoutr::pages.scan-detail', compact('scan'));
    }

    /**
     * Display scan results in a clean dedicated view
     */
    public function viewScan($id): View
    {
        $scan = Scan::with(['issues' => function ($query) {
            $query->orderBy('severity', 'desc')
                  ->orderBy('file_path')
                  ->orderBy('line_number');
        }])->findOrFail($id);

        return view('codesnoutr::pages.view-scan', compact('scan'));
    }

    /**
     * Display the results page
     */
    public function results(Request $request): View
    {
        $query = Scan::with(['issues'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $scans = $query->paginate(15);

        return view('codesnoutr::pages.results', compact('scans'));
    }

    /**
     * Show results for a specific scan
     */
    public function scanResults($scanId): View|RedirectResponse
    {
        // Handle string scan IDs that might be invalid
        if (!is_numeric($scanId) || $scanId === 'undefined' || $scanId === 'null') {
            // Redirect to general results page with an error message
            return redirect()->route('codesnoutr.results')
                ->with('error', 'Invalid scan ID provided. Showing all scans instead.');
        }

        $scan = Scan::findOrFail($scanId);
        
        return view('codesnoutr::pages.scan-results', compact('scan'));
    }

    /**
     * Display the settings page
     */
    public function settings(): View
    {
        return view('codesnoutr::pages.settings');
    }

    /**
     * Display the reports page
     */
    public function reports(): View
    {
        $scans = Scan::with('issues')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total_scans' => Scan::count(),
            'total_issues' => Issue::count(),
            'resolved_issues' => Issue::where('fixed', true)->count(),
            'critical_issues' => Issue::where('severity', 'critical')->count(),
        ];

        return view('codesnoutr::pages.reports', compact('scans', 'stats'));
    }

    /**
     * Export scan results
     */
    public function export(Scan $scan, string $format = 'json'): Response
    {
        $scan->load('issues');

        switch ($format) {
            case 'csv':
                return $this->exportCsv($scan);
            case 'pdf':
                return $this->exportPdf($scan);
            case 'json':
            default:
                return $this->exportJson($scan);
        }
    }

    /**
     * API: Start a new scan
     */
    public function apiScan(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:file,directory,codebase',
            'target' => 'required_unless:type,codebase|string',
            'categories' => 'array',
            'categories.*' => 'in:security,performance,quality,laravel',
            'options' => 'array',
        ]);

        try {
            $result = $this->scanManager->scan(
                $request->type,
                $request->target,
                $request->categories ?? ['security', 'performance', 'quality', 'laravel'],
                $request->options ?? []
            );

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Scan started successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start scan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get scan details
     */
    public function apiShow(Scan $scan): JsonResponse
    {
        $scan->load('issues');

        return response()->json([
            'success' => true,
            'data' => $scan
        ]);
    }

    /**
     * API: Get dashboard statistics
     */
    public function apiStats(): JsonResponse
    {
        $stats = $this->getDashboardStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * API: Health check
     */
    public function apiHealth(): JsonResponse
    {
        try {
            // Basic health checks
            $health = [
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'version' => '1.0.0',
                'checks' => [
                    'database' => $this->checkDatabase(),
                    'storage' => $this->checkStorage(),
                    'configuration' => $this->checkConfiguration(),
                ]
            ];

            $allHealthy = collect($health['checks'])->every(fn($check) => $check['status'] === 'healthy');
            
            if (!$allHealthy) {
                $health['status'] = 'degraded';
            }

            return response()->json($health, $allHealthy ? 200 : 503);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics
     */
    protected function getDashboardStats(): array
    {
        $lastWeek = Carbon::now()->subWeek();
        
        $totalScans = Scan::count();
        $totalIssues = Issue::count();
        $resolvedIssues = Issue::where('fixed', true)->count();
        $criticalIssues = Issue::where('severity', 'critical')->where('fixed', false)->count();

        // Calculate changes from last week
        $scansLastWeek = Scan::where('created_at', '>=', $lastWeek)->count();
        $scansBeforeLastWeek = $totalScans - $scansLastWeek;
        $scansChange = $scansBeforeLastWeek > 0 ? 
            round((($scansLastWeek - $scansBeforeLastWeek) / $scansBeforeLastWeek) * 100, 1) : 0;

        $issuesLastWeek = Issue::where('created_at', '>=', $lastWeek)->count();
        $issuesBeforeLastWeek = $totalIssues - $issuesLastWeek;
        $issuesChange = $issuesBeforeLastWeek > 0 ? 
            round((($issuesLastWeek - $issuesBeforeLastWeek) / $issuesBeforeLastWeek) * 100, 1) : 0;

        $resolutionRate = $totalIssues > 0 ? round(($resolvedIssues / $totalIssues) * 100, 1) : 0;

        // Issues by severity
        $issuesBySeverity = Issue::selectRaw('severity, count(*) as count')
            ->where('fixed', false)
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        // Fill missing severities with 0
        $severities = ['critical', 'high', 'medium', 'low', 'info'];
        foreach ($severities as $severity) {
            if (!isset($issuesBySeverity[$severity])) {
                $issuesBySeverity[$severity] = 0;
            }
        }

        return [
            'total_scans' => $totalScans,
            'total_issues' => $totalIssues,
            'resolved_issues' => $resolvedIssues,
            'critical_issues' => $criticalIssues,
            'scans_change' => $scansChange,
            'issues_change' => $issuesChange,
            'resolution_rate' => $resolutionRate,
            'issues_by_severity' => $issuesBySeverity,
        ];
    }

    /**
     * Get recent scans
     */
    protected function getRecentScans(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Scan::with('issues')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Export scan as JSON
     */
    protected function exportJson(Scan $scan): Response
    {
        $data = [
            'scan' => [
                'id' => $scan->id,
                'type' => $scan->type,
                'target' => $scan->target,
                'status' => $scan->status,
                'started_at' => $scan->started_at,
                'completed_at' => $scan->completed_at,
                'files_scanned' => $scan->files_scanned,
                'issues_found' => $scan->issues_found,
                'scan_duration' => $scan->scan_duration,
            ],
            'issues' => $scan->issues->map(function ($issue) {
                return [
                    'id' => $issue->id,
                    'file_path' => $issue->file_path,
                    'line_number' => $issue->line_number,
                    'column_number' => $issue->column_number,
                    'severity' => $issue->severity,
                    'category' => $issue->category,
                    'title' => $issue->title,
                    'description' => $issue->description,
                    'code_snippet' => $issue->code_snippet,
                    'fix_suggestion' => $issue->fix_suggestion,
                    'status' => $issue->status,
                    'created_at' => $issue->created_at,
                ];
            }),
            'exported_at' => now()->toISOString(),
            'exported_by' => 'CodeSnoutr v1.0',
        ];

        $filename = "codesnoutr-scan-{$scan->id}-" . now()->format('Y-m-d-H-i-s') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export scan as CSV
     */
    protected function exportCsv(Scan $scan): Response
    {
        $csv = "ID,File,Line,Column,Severity,Category,Title,Description,Status,Created At\n";
        
        foreach ($scan->issues as $issue) {
            $csv .= sprintf(
                "%d,\"%s\",%d,%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $issue->id,
                str_replace('"', '""', $issue->file_path),
                $issue->line_number,
                $issue->column_number,
                $issue->severity,
                $issue->category,
                str_replace('"', '""', $issue->title),
                str_replace('"', '""', $issue->description),
                $issue->status,
                $issue->created_at->format('Y-m-d H:i:s')
            );
        }

        $filename = "codesnoutr-scan-{$scan->id}-" . now()->format('Y-m-d-H-i-s') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export scan as PDF (placeholder for future implementation)
     */
    protected function exportPdf(Scan $scan): Response
    {
        // This would require a PDF library like TCPDF or DOMPDF
        // For now, return JSON with a message
        return response()->json([
            'message' => 'PDF export is not yet implemented. Please use JSON or CSV format.',
            'available_formats' => ['json', 'csv']
        ], 501);
    }

    /**
     * Debug CSRF and session information
     */
    public function debugCsrf(): JsonResponse
    {
        return response()->json([
            'csrf_token' => csrf_token(),
            'session_id' => session()->getId(),
            'session_started' => session()->isStarted(),
            'middleware_loaded' => true,
            'app_key' => config('app.key') ? 'SET' : 'NOT_SET',
            'session_driver' => config('session.driver'),
            'session_lifetime' => config('session.lifetime'),
            'session_path' => config('session.path'),
            'session_domain' => config('session.domain'),
            'session_secure' => config('session.secure'),
            'session_http_only' => config('session.http_only'),
            'session_same_site' => config('session.same_site'),
            'env' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'route_middleware' => request()->route() ? request()->route()->gatherMiddleware() : [],
        ]);
    }

    /**
     * Debug route information
     */
    public function debugRoutes(): JsonResponse
    {
        $routes = collect(Route::getRoutes())->filter(function($route) {
            $name = $route->getName();
            return $name && str_contains($name, 'codesnoutr');
        })->map(function($route) {
            return [
                'name' => $route->getName(),
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'action' => $route->getActionName(),
            ];
        });
        
        return response()->json([
            'routes_loaded' => true,
            'codesnoutr_routes' => $routes->values(),
            'target_route_exists' => Route::has('codesnoutr.scan-results.group-details'),
            'locale' => app()->getLocale(),
            'request_url' => request()->url(),
            'app_url' => config('app.url'),
        ]);
    }

    /**
     * Display group file details
     */
    public function groupFileDetails(string $scanId, string $title, string $category, string $severity, Request $request): View|RedirectResponse
    {
        try {
            $scan = Scan::findOrFail($scanId);
            
            // Decode URL-encoded parameters
            $decodedTitle = urldecode($title);
            $decodedCategory = urldecode($category);
            $decodedSeverity = urldecode($severity);
            
            // Get additional parameters from query string
            $description = $request->query('description');
            $rule = $request->query('rule');
            $suggestion = $request->query('suggestion');
            
            return view('codesnoutr::pages.group-file-details', [
                'scanId' => $scanId,
                'scan' => $scan,
                'title' => $decodedTitle,
                'category' => $decodedCategory,
                'severity' => $decodedSeverity,
                'description' => $description ? urldecode($description) : null,
                'rule' => $rule ? urldecode($rule) : null,
                'suggestion' => $suggestion ? urldecode($suggestion) : null,
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('CodeSnoutr Group Details Error', [
                'scan_id' => $scanId,
                'title' => $title,
                'category' => $category,
                'severity' => $severity,
                'error' => $e->getMessage(),
                'url' => $request->url(),
            ]);
            
            // Redirect back with error message
            return redirect()->route('codesnoutr.results')
                ->with('error', 'Unable to load group details. Scan ID: ' . $scanId . ' - ' . $e->getMessage());
        }
    }

    /**
     * Check database connectivity
     */
    protected function checkDatabase(): array
    {
        try {
            Scan::count();
            return ['status' => 'healthy', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check storage accessibility
     */
    protected function checkStorage(): array
    {
        try {
            $testPath = storage_path('app/codesnoutr-health-check');
            file_put_contents($testPath, 'test');
            unlink($testPath);
            return ['status' => 'healthy', 'message' => 'Storage is accessible'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => 'Storage not accessible: ' . $e->getMessage()];
        }
    }

    /**
     * Check configuration
     */
    protected function checkConfiguration(): array
    {
        try {
            $config = config('codesnoutr');
            if (empty($config)) {
                return ['status' => 'unhealthy', 'message' => 'Configuration not loaded'];
            }
            return ['status' => 'healthy', 'message' => 'Configuration loaded successfully'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => 'Configuration error: ' . $e->getMessage()];
        }
    }
}
