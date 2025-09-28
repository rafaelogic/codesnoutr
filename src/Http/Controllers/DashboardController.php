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
    public function results(Request $request): View|JsonResponse
    {
        // Debug: Check if we can get any scans at all
        $totalScans = Scan::count();
        \Log::info('Results page - Total scans in database: ' . $totalScans);
        
        $query = Scan::with(['issues'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
            \Log::info('Results page - Filtering by status: ' . $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
            \Log::info('Results page - Filtering by type: ' . $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
            \Log::info('Results page - Filtering by date_from: ' . $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
            \Log::info('Results page - Filtering by date_to: ' . $request->date_to);
        }

        // Debug: Get the SQL query being executed
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        \Log::info('Results page - SQL: ' . $sql, $bindings);

        $scans = $query->paginate(15);
        
        \Log::info('Results page - Paginated scans count: ' . $scans->count());
        \Log::info('Results page - Paginated scans total: ' . $scans->total());

        // Debug: Add debug info to help troubleshoot
        if ($request->has('debug')) {
            return response()->json([
                'total_scans_in_db' => $totalScans,
                'paginated_count' => $scans->count(),
                'paginated_total' => $scans->total(),
                'current_page' => $scans->currentPage(),
                'per_page' => $scans->perPage(),
                'last_page' => $scans->lastPage(),
                'sql' => $sql,
                'bindings' => $bindings,
                'filters' => [
                    'status' => $request->get('status'),
                    'type' => $request->get('type'),
                    'date_from' => $request->get('date_from'),
                    'date_to' => $request->get('date_to'),
                ],
                'scans_data' => $scans->items(),
            ]);
        }

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
    public function export(Scan $scan, string $format = 'json'): Response|JsonResponse
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
    protected function exportJson(Scan $scan): JsonResponse
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
     * Export scan as PDF
     */
    protected function exportPdf(Scan $scan): Response|JsonResponse
    {
        // Check if PDF library is available
        if (class_exists('Spatie\\LaravelPdf\\Facades\\Pdf')) {
            return $this->generatePdfWithSpatie($scan);
        } elseif (class_exists('TCPDF')) {
            return $this->generatePdfWithTcpdf($scan);
        } elseif (class_exists('Dompdf\\Dompdf')) {
            return $this->generatePdfWithDompdf($scan);
        } else {
            // No PDF library available - return informative response
            return response()->json([
                'error' => 'PDF export requires a PDF library to be installed.',
                'message' => 'To enable PDF export, install one of the following packages:',
                'recommendations' => [
                    'spatie/laravel-pdf' => 'composer require spatie/laravel-pdf',
                    'tecnickcom/tcpdf' => 'composer require tecnickcom/tcpdf',
                    'dompdf/dompdf' => 'composer require dompdf/dompdf'
                ],
                'available_formats' => ['json', 'csv'],
                'alternative_download' => route('codesnoutr.export', [$scan->id, 'json'])
            ], 501);
        }
    }

    /**
     * Generate PDF using Spatie Laravel PDF
     */
    protected function generatePdfWithSpatie(Scan $scan): Response
    {
        $data = [
            'scan' => $scan,
            'issues' => $scan->issues,
            'exported_at' => now()->format('Y-m-d H:i:s'),
            'stats' => [
                'total_issues' => $scan->issues->count(),
                'critical_issues' => $scan->issues->where('severity', 'critical')->count(),
                'high_issues' => $scan->issues->where('severity', 'high')->count(),
                'medium_issues' => $scan->issues->where('severity', 'medium')->count(),
                'low_issues' => $scan->issues->where('severity', 'low')->count(),
            ]
        ];

        $pdfClass = 'Spatie\\LaravelPdf\\Facades\\Pdf';
        $pdf = $pdfClass::view('codesnoutr::exports.pdf-report', $data)
            ->format('A4')
            ->name("codesnoutr-scan-{$scan->id}-" . now()->format('Y-m-d-H-i-s') . '.pdf');

        return $pdf->download();
    }

    /**
     * Generate PDF using TCPDF
     */
    protected function generatePdfWithTcpdf(Scan $scan): Response
    {
        $tcpdfClass = 'TCPDF';
        $pdf = new $tcpdfClass('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('CodeSnoutr v1.0');
        $pdf->SetAuthor('CodeSnoutr');
        $pdf->SetTitle("Scan Report #{$scan->id}");
        $pdf->SetSubject('Code Quality Report');

        // Set default header data
        $pdf->SetHeaderData('', 0, "CodeSnoutr Scan Report", "Scan #{$scan->id} - " . now()->format('Y-m-d H:i:s'));

        // Set header and footer fonts
        $pdf->setHeaderFont(['helvetica', '', 12]);
        $pdf->setFooterFont(['helvetica', '', 10]);

        // Set margins
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, 25);

        // Add a page
        $pdf->AddPage();

        // Build HTML content
        $html = $this->buildPdfContent($scan);
        
        // Write HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = "codesnoutr-scan-{$scan->id}-" . now()->format('Y-m-d-H-i-s') . '.pdf';

        return response($pdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Generate PDF using Dompdf
     */
    protected function generatePdfWithDompdf(Scan $scan): Response
    {
        $dompdfClass = 'Dompdf\\Dompdf';
        $dompdf = new $dompdfClass();
        $dompdf->loadHtml($this->buildPdfContent($scan));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = "codesnoutr-scan-{$scan->id}-" . now()->format('Y-m-d-H-i-s') . '.pdf';

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Build HTML content for PDF
     */
    protected function buildPdfContent(Scan $scan): string
    {
        $stats = [
            'total_issues' => $scan->issues->count(),
            'critical_issues' => $scan->issues->where('severity', 'critical')->count(),
            'high_issues' => $scan->issues->where('severity', 'high')->count(),
            'medium_issues' => $scan->issues->where('severity', 'medium')->count(),
            'low_issues' => $scan->issues->where('severity', 'low')->count(),
        ];

        $html = '<html><head><title>CodeSnoutr Scan Report</title></head><body>';
        $html .= '<h1>CodeSnoutr Scan Report</h1>';
        $html .= '<h2>Scan Details</h2>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
        $html .= '<tr><td><strong>Scan ID:</strong></td><td>' . htmlspecialchars($scan->id) . '</td></tr>';
        $html .= '<tr><td><strong>Type:</strong></td><td>' . htmlspecialchars($scan->type) . '</td></tr>';
        $html .= '<tr><td><strong>Target:</strong></td><td>' . htmlspecialchars($scan->target) . '</td></tr>';
        $html .= '<tr><td><strong>Status:</strong></td><td>' . htmlspecialchars($scan->status) . '</td></tr>';
        $html .= '<tr><td><strong>Files Scanned:</strong></td><td>' . htmlspecialchars($scan->files_scanned) . '</td></tr>';
        $html .= '<tr><td><strong>Issues Found:</strong></td><td>' . htmlspecialchars($scan->issues_found) . '</td></tr>';
        $html .= '<tr><td><strong>Started At:</strong></td><td>' . htmlspecialchars($scan->started_at) . '</td></tr>';
        $html .= '<tr><td><strong>Completed At:</strong></td><td>' . htmlspecialchars($scan->completed_at) . '</td></tr>';
        $html .= '</table>';

        $html .= '<h2>Issue Summary</h2>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
        $html .= '<tr><td><strong>Critical:</strong></td><td>' . $stats['critical_issues'] . '</td></tr>';
        $html .= '<tr><td><strong>High:</strong></td><td>' . $stats['high_issues'] . '</td></tr>';
        $html .= '<tr><td><strong>Medium:</strong></td><td>' . $stats['medium_issues'] . '</td></tr>';
        $html .= '<tr><td><strong>Low:</strong></td><td>' . $stats['low_issues'] . '</td></tr>';
        $html .= '</table>';

        if ($scan->issues->count() > 0) {
            $html .= '<h2>Issues Details</h2>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%; font-size: 12px;">';
            $html .= '<tr style="background-color: #f0f0f0;"><th>File</th><th>Line</th><th>Severity</th><th>Category</th><th>Title</th><th>Description</th></tr>';

            foreach ($scan->issues as $issue) {
                $severityColor = match($issue->severity) {
                    'critical' => '#dc2626',
                    'high' => '#ea580c',
                    'medium' => '#ca8a04',
                    'low' => '#65a30d',
                    default => '#6b7280'
                };

                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($issue->file_path) . '</td>';
                $html .= '<td>' . htmlspecialchars($issue->line_number) . '</td>';
                $html .= '<td style="color: ' . $severityColor . '; font-weight: bold;">' . htmlspecialchars(strtoupper($issue->severity)) . '</td>';
                $html .= '<td>' . htmlspecialchars($issue->category) . '</td>';
                $html .= '<td>' . htmlspecialchars($issue->title) . '</td>';
                $html .= '<td>' . htmlspecialchars(substr($issue->description, 0, 100) . (strlen($issue->description) > 100 ? '...' : '')) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
        }

        $html .= '<hr><p style="font-size: 10px; color: #666;">Generated by CodeSnoutr v1.0 on ' . now()->format('Y-m-d H:i:s') . '</p>';
        $html .= '</body></html>';

        return $html;
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
