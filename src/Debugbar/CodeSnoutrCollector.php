<?php

namespace Rafaelogic\CodeSnoutr\Debugbar;

use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

if (!class_exists('Rafaelogic\CodeSnoutr\Debugbar\CodeSnoutrCollector')) {
    if (class_exists('DebugBar\DataCollector\DataCollector')) {
        class CodeSnoutrCollector extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable
        {
            /**
             * Store complex data for panel rendering
             */
            protected $panelData = [];

            /**
             * Called by the DebugBar when data needs to be collected
             */
            public function collect()
            {
                try {
                    $settings = $this->getSettings();
                    
                    if (!$settings['debugbar_enabled']) {
                        return [
                            'enabled' => false,
                            'message' => 'CodeSnoutr debugbar integration is disabled'
                        ];
                    }

                    // Get raw data
                    $stats = $this->getStats($settings);
                    $systemInfo = $this->getSystemInfo();
                    $recentActivity = $this->getRecentActivity($settings);
                    $queueStatus = $this->getQueueStatus();
                    $performanceMetrics = $this->getPerformanceMetrics();
                    $alerts = $this->getAlerts();

                    // Flatten complex data for debugbar display
                    $data = [
                        'enabled' => true,
                        // Flatten stats
                        'total_scans' => $stats['total_scans'] ?? 0,
                        'scans_today' => $stats['scans_today'] ?? 0,
                        'total_issues' => $stats['total_issues'] ?? 0,
                        'unresolved_issues' => $stats['unresolved_issues'] ?? 0,
                        'critical_issues' => $stats['critical_issues'] ?? 0,
                        'security_issues' => $stats['security_issues'] ?? 0,
                        'performance_issues' => $stats['performance_issues'] ?? 0,
                        'quality_issues' => $stats['quality_issues'] ?? 0,
                        'resolved_today' => $stats['resolved_today'] ?? 0,
                        'health_score' => $stats['health_score'] ?? 0,
                        'last_scan' => $stats['last_scan'] ?? 'Never',
                        
                        // Flatten system info
                        'version' => $systemInfo['version'] ?? 'Unknown',
                        'memory_usage' => $systemInfo['memory_usage'] ?? 'Unknown',
                        'memory_usage_percent' => $systemInfo['memory_usage_percent'] ?? 'Unknown',
                        'cache_enabled' => $systemInfo['cache_enabled'] ? 'Yes' : 'No',
                        'ai_enabled' => $systemInfo['ai_enabled'] ? 'Yes' : 'No',
                        'queue_enabled' => $systemInfo['queue_enabled'] ? 'Yes' : 'No',
                        'environment' => $systemInfo['environment'] ?? 'Unknown',
                        'debugbar_version' => $systemInfo['debugbar_version'] ?? 'Unknown',
                        
                        // Flatten queue status
                        'queue_connection' => $queueStatus['connection'] ?? 'Unknown',
                        'pending_jobs' => $queueStatus['pending_jobs'] ?? 0,
                        'failed_jobs' => $queueStatus['failed_jobs'] ?? 0,
                        
                        // Flatten performance metrics
                        'avg_scan_time' => $performanceMetrics['avg_scan_time'] ?? 'Unknown',
                        'slowest_scan_time' => $performanceMetrics['slowest_scan_time'] ?? 'Unknown',
                        'fastest_scan_time' => $performanceMetrics['fastest_scan_time'] ?? 'Unknown',
                        'most_problematic_file' => is_array($performanceMetrics['most_problematic_file'] ?? null) ? 
                            ($performanceMetrics['most_problematic_file']['file'] . ' (' . $performanceMetrics['most_problematic_file']['issues'] . ' issues)') : 
                            'None',
                        
                        // Format recent activity as strings
                        'recent_activity_count' => count($recentActivity),
                        'latest_activity' => !empty($recentActivity) ? $this->formatActivityForDisplay($recentActivity[0]) : 'None',
                        
                        // Format alerts
                        'alert_count' => count($alerts),
                        'critical_alerts' => count(array_filter($alerts, function($alert) { return ($alert['type'] ?? '') === 'critical'; })),
                        'latest_alert' => !empty($alerts) ? $alerts[0]['message'] ?? 'None' : 'None',
                    ];

                    // Store complex data for panel rendering
                    $this->panelData = [
                        'stats' => $stats,
                        'system_info' => $systemInfo,
                        'recent_activity' => $recentActivity,
                        'queue_status' => $queueStatus,
                        'performance_metrics' => $performanceMetrics,
                        'alerts' => $alerts,
                    ];

                    return $data;
                } catch (\Exception $e) {
                    return [
                        'enabled' => false,
                        'error' => $e->getMessage(),
                        'trace' => config('app.debug') ? $e->getTraceAsString() : null
                    ];
                }
            }

            /**
             * Format activity item for simple display
             */
            protected function formatActivityForDisplay($activity)
            {
                if (!is_array($activity)) {
                    return 'Unknown activity';
                }

                if ($activity['type'] === 'scan') {
                    return "Scan #{$activity['id']} - {$activity['issues_count']} issues";
                } elseif ($activity['type'] === 'issue') {
                    $severity = strtoupper($activity['severity']);
                    return "{$severity} {$activity['category']} in " . basename($activity['file_path']);
                }

                return 'Unknown activity';
            }

            /**
             * Returns the unique name of the collector
             */
            public function getName()
            {
                return 'codesnoutr';
            }

            /**
             * Returns a hash where keys are control names and their values
             * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
             */
            public function getWidgets()
            {
                $name = $this->getName();
                return [
                    "$name" => [
                        "icon" => "shield",
                        "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                        "map" => "$name",
                        "default" => "{}",
                        "tooltip" => "CodeSnoutr Code Analysis & Quality Metrics"
                    ],
                    "$name:badge" => [
                        "map" => "$name.unresolved_issues",
                        "default" => "0"
                    ]
                ];
            }

            /**
             * Get CodeSnoutr settings
             */
            protected function getSettings()
            {
                return Cache::remember('codesnoutr.debugbar.settings', 300, function () {
                    $defaultSettings = [
                        'debugbar_enabled' => config('codesnoutr.debugbar.enabled', false),
                        'show_scan_count' => config('codesnoutr.debugbar.show_scan_count', true),
                        'show_issue_count' => config('codesnoutr.debugbar.show_issue_count', true),
                        'show_health_score' => config('codesnoutr.debugbar.show_health_score', true),
                        'show_recent_activity' => config('codesnoutr.debugbar.show_recent_activity', true),
                        'max_recent_items' => config('codesnoutr.debugbar.max_recent_items', 5),
                        'cache_duration' => config('codesnoutr.debugbar.cache_duration', 300),
                    ];

                    // Try to get settings from database
                    try {
                        if (class_exists('Rafaelogic\CodeSnoutr\Models\Setting')) {
                            $dbSettings = Setting::pluck('value', 'key')->toArray();
                            return array_merge($defaultSettings, $dbSettings);
                        }
                    } catch (\Exception $e) {
                        // Fall back to config if database is not available
                    }

                    return $defaultSettings;
                });
            }

            /**
             * Get enhanced statistics
             */
            protected function getStats($settings)
            {
                return Cache::remember('codesnoutr.debugbar.stats', $settings['cache_duration'] ?? 300, function () {
                    try {
                        $stats = [
                            'total_scans' => 0,
                            'scans_today' => 0,
                            'total_issues' => 0,
                            'unresolved_issues' => 0,
                            'critical_issues' => 0,
                            'high_issues' => 0,
                            'medium_issues' => 0,
                            'low_issues' => 0,
                            'security_issues' => 0,
                            'performance_issues' => 0,
                            'quality_issues' => 0,
                            'resolved_today' => 0,
                            'health_score' => 0,
                            'last_scan' => 'Never',
                        ];

                        if (class_exists('Rafaelogic\CodeSnoutr\Models\Scan')) {
                            $stats['total_scans'] = Scan::count();
                            $stats['scans_today'] = Scan::whereDate('created_at', today())->count();
                            
                            $latestScan = Scan::latest()->first();
                            if ($latestScan) {
                                $stats['last_scan'] = $latestScan->created_at->diffForHumans();
                            }
                        }

                        if (class_exists('Rafaelogic\CodeSnoutr\Models\Issue')) {
                            $stats['total_issues'] = Issue::count();
                            $stats['unresolved_issues'] = Issue::where('status', '!=', 'resolved')->count();
                            $stats['critical_issues'] = Issue::where('severity', 'critical')->count();
                            $stats['high_issues'] = Issue::where('severity', 'high')->count();
                            $stats['medium_issues'] = Issue::where('severity', 'medium')->count();
                            $stats['low_issues'] = Issue::where('severity', 'low')->count();
                            $stats['security_issues'] = Issue::where('category', 'security')->count();
                            $stats['performance_issues'] = Issue::where('category', 'performance')->count();
                            $stats['quality_issues'] = Issue::where('category', 'quality')->count();
                            $stats['resolved_today'] = Issue::where('status', 'resolved')
                                ->whereDate('updated_at', today())
                                ->count();
                        }

                        $stats['health_score'] = $this->calculateHealthScore($stats);

                        return $stats;
                    } catch (\Exception $e) {
                        return [
                            'error' => 'Unable to fetch statistics: ' . $e->getMessage()
                        ];
                    }
                });
            }

            /**
             * Get enhanced system information
             */
            protected function getSystemInfo()
            {
                $memoryLimit = ini_get('memory_limit');
                $memoryUsage = memory_get_usage(true);
                $memoryUsageFormatted = $this->formatBytes($memoryUsage);
                $memoryUsagePercent = $this->parseMemoryLimit($memoryLimit) > 0 ? 
                    round(($memoryUsage / $this->parseMemoryLimit($memoryLimit)) * 100, 1) : 'Unknown';

                return [
                    'version' => config('codesnoutr.version', '1.0.0'),
                    'memory_usage' => $memoryUsageFormatted,
                    'memory_usage_percent' => $memoryUsagePercent . '%',
                    'memory_limit' => $memoryLimit,
                    'cache_enabled' => config('cache.default') !== 'null',
                    'cache_driver' => config('cache.default'),
                    'ai_enabled' => config('codesnoutr.ai.enabled', false),
                    'ai_provider' => config('codesnoutr.ai.provider', 'none'),
                    'queue_enabled' => config('codesnoutr.queue.enabled', false),
                    'queue_connection' => config('queue.default'),
                    'debugbar_version' => $this->getDebugbarVersion(),
                    'environment' => app()->environment(),
                    'uptime' => $this->getUptime(),
                ];
            }

            /**
             * Format bytes into human readable format
             */
            public function formatBytes($bytes, $precision = 2)
            {
                $units = array('B', 'KB', 'MB', 'GB', 'TB');

                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }

                return round($bytes, $precision) . ' ' . $units[$i];
            }

            /**
             * Parse memory limit string to bytes
             */
            protected function parseMemoryLimit($memoryLimit)
            {
                if ($memoryLimit === '-1') {
                    return PHP_INT_MAX;
                }

                $unit = strtolower(substr($memoryLimit, -1));
                $value = (int) $memoryLimit;

                switch ($unit) {
                    case 'g':
                        $value *= 1024 * 1024 * 1024;
                        break;
                    case 'm':
                        $value *= 1024 * 1024;
                        break;
                    case 'k':
                        $value *= 1024;
                        break;
                }

                return $value;
            }

            /**
             * Get application uptime (approximate)
             */
            protected function getUptime()
            {
                if (function_exists('sys_getloadavg')) {
                    // Try to get system uptime on Unix-like systems
                    $uptime = shell_exec('uptime');
                    if ($uptime && preg_match('/up\s+(.*?),/', $uptime, $matches)) {
                        return trim($matches[1]);
                    }
                }
                
                return 'Unknown';
            }

            /**
             * Get Laravel Debugbar version safely
             */
            protected function getDebugbarVersion()
            {
                if (!class_exists('Barryvdh\Debugbar\LaravelDebugbar')) {
                    return 'not installed';
                }

                try {
                    // Try to get version from composer.lock
                    $composerLock = base_path('composer.lock');
                    if (file_exists($composerLock)) {
                        $lockData = json_decode(file_get_contents($composerLock), true);
                        if (isset($lockData['packages'])) {
                            foreach ($lockData['packages'] as $package) {
                                if ($package['name'] === 'barryvdh/laravel-debugbar') {
                                    return $package['version'] ?? 'unknown';
                                }
                            }
                        }
                    }

                    // Fallback: try to get version from package file
                    $packagePath = base_path('vendor/barryvdh/laravel-debugbar/composer.json');
                    if (file_exists($packagePath)) {
                        $packageData = json_decode(file_get_contents($packagePath), true);
                        if (isset($packageData['version'])) {
                            return $packageData['version'];
                        }
                    }

                    return 'installed';
                } catch (\Exception $e) {
                    return 'unknown';
                }
            }

            /**
             * Calculate health score based on issues
             */
            protected function calculateHealthScore($stats)
            {
                $totalIssues = $stats['total_issues'] ?? 0;
                $unresolvedIssues = $stats['unresolved_issues'] ?? 0;
                $criticalIssues = $stats['critical_issues'] ?? 0;
                $highIssues = $stats['high_issues'] ?? 0;

                if ($totalIssues === 0) {
                    return 100;
                }

                // Start with base score
                $score = 100;

                // Deduct points for unresolved issues
                $score -= ($unresolvedIssues / max($totalIssues, 1)) * 40;

                // Heavy penalty for critical issues
                $score -= $criticalIssues * 10;

                // Moderate penalty for high severity issues
                $score -= $highIssues * 5;

                // Ensure score is between 0 and 100
                return max(0, min(100, round($score)));
            }

            /**
             * Get recent activity
             */
            protected function getRecentActivity($settings)
            {
                $maxItems = $settings['max_recent_items'] ?? 5;
                $activity = [];

                try {
                    // Get recent scans
                    if (class_exists('Rafaelogic\CodeSnoutr\Models\Scan')) {
                        $recentScans = Scan::latest()
                            ->limit($maxItems)
                            ->get()
                            ->map(function ($scan) {
                                return [
                                    'type' => 'scan',
                                    'id' => $scan->id,
                                    'issues_count' => $scan->issues()->count(),
                                    'created_at' => $scan->created_at->diffForHumans(),
                                ];
                            });
                        
                        $activity = array_merge($activity, $recentScans->toArray());
                    }

                    // Get recent critical issues
                    if (class_exists('Rafaelogic\CodeSnoutr\Models\Issue')) {
                        $recentIssues = Issue::where('severity', 'critical')
                            ->latest()
                            ->limit($maxItems)
                            ->get()
                            ->map(function ($issue) {
                                return [
                                    'type' => 'issue',
                                    'id' => $issue->id,
                                    'severity' => $issue->severity,
                                    'category' => $issue->category,
                                    'file_path' => $issue->file_path,
                                    'created_at' => $issue->created_at->diffForHumans(),
                                ];
                            });
                        
                        $activity = array_merge($activity, $recentIssues->toArray());
                    }

                    // Sort by creation time and limit
                    usort($activity, function ($a, $b) {
                        return strcmp($b['created_at'], $a['created_at']);
                    });

                    return array_slice($activity, 0, $maxItems);
                } catch (\Exception $e) {
                    return [];
                }
            }

            /**
             * Get queue status
             */
            protected function getQueueStatus()
            {
                try {
                    $queueEnabled = config('codesnoutr.queue.enabled', false);
                    
                    if (!$queueEnabled) {
                        return [
                            'enabled' => false,
                            'connection' => 'disabled',
                            'pending_jobs' => 0,
                            'failed_jobs' => 0,
                        ];
                    }

                    $connection = config('queue.default');
                    $pendingJobs = 0;
                    $failedJobs = 0;

                    // Try to get queue metrics
                    if ($connection === 'database') {
                        $pendingJobs = DB::table('jobs')->count();
                        $failedJobs = DB::table('failed_jobs')->count();
                    }

                    return [
                        'enabled' => true,
                        'connection' => $connection,
                        'pending_jobs' => $pendingJobs,
                        'failed_jobs' => $failedJobs,
                    ];
                } catch (\Exception $e) {
                    return [
                        'enabled' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            /**
             * Get performance metrics
             */
            protected function getPerformanceMetrics()
            {
                try {
                    $metrics = [
                        'avg_scan_time' => 'Unknown',
                        'slowest_scan_time' => 'Unknown',
                        'fastest_scan_time' => 'Unknown',
                        'most_problematic_file' => null,
                    ];

                    if (class_exists('Rafaelogic\CodeSnoutr\Models\Scan')) {
                        // Get scan time metrics
                        $scanTimes = Scan::whereNotNull('duration')
                            ->pluck('duration')
                            ->filter();

                        if ($scanTimes->isNotEmpty()) {
                            $metrics['avg_scan_time'] = round($scanTimes->avg(), 2) . 's';
                            $metrics['slowest_scan_time'] = $scanTimes->max() . 's';
                            $metrics['fastest_scan_time'] = $scanTimes->min() . 's';
                        }
                    }

                    if (class_exists('Rafaelogic\CodeSnoutr\Models\Issue')) {
                        // Find most problematic file
                        $fileCounts = Issue::selectRaw('file_path, COUNT(*) as issue_count')
                            ->groupBy('file_path')
                            ->orderByDesc('issue_count')
                            ->first();

                        if ($fileCounts) {
                            $metrics['most_problematic_file'] = [
                                'file' => basename($fileCounts->file_path),
                                'issues' => $fileCounts->issue_count,
                            ];
                        }
                    }

                    return $metrics;
                } catch (\Exception $e) {
                    return [
                        'error' => $e->getMessage(),
                    ];
                }
            }

            /**
             * Get alerts
             */
            protected function getAlerts()
            {
                $alerts = [];

                try {
                    if (class_exists('Rafaelogic\CodeSnoutr\Models\Issue')) {
                        // Check for critical issues
                        $criticalCount = Issue::where('severity', 'critical')
                            ->where('status', '!=', 'resolved')
                            ->count();

                        if ($criticalCount > 0) {
                            $alerts[] = [
                                'type' => 'critical',
                                'message' => "You have {$criticalCount} unresolved critical issues!"
                            ];
                        }

                        // Check for stale issues
                        $staleCount = Issue::where('created_at', '<', Carbon::now()->subDays(30))
                            ->where('status', '!=', 'resolved')
                            ->count();

                        if ($staleCount > 0) {
                            $alerts[] = [
                                'type' => 'warning',
                                'message' => "{$staleCount} issues are older than 30 days"
                            ];
                        }
                    }

                    if (class_exists('Rafaelogic\CodeSnoutr\Models\Scan')) {
                        // Check for stale scans
                        $lastScan = Scan::latest()->first();
                        if (!$lastScan || $lastScan->created_at < Carbon::now()->subDays(7)) {
                            $alerts[] = [
                                'type' => 'warning',
                                'message' => 'No scans performed in the last 7 days'
                            ];
                        }
                    }

                    // Check system health
                    $memoryUsage = memory_get_usage(true);
                    $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
                    
                    if ($memoryLimit > 0 && ($memoryUsage / $memoryLimit) > 0.8) {
                        $alerts[] = [
                            'type' => 'warning',
                            'message' => 'High memory usage detected'
                        ];
                    }

                    return $alerts;
                } catch (\Exception $e) {
                    return [[
                        'type' => 'info',
                        'message' => 'Unable to check alerts: ' . $e->getMessage()
                    ]];
                }
            }

            /**
             * Get enhanced panel content (HTML)
             */
            public function getPanel()
            {
                // Use stored panel data if available, otherwise collect fresh data
                $data = !empty($this->panelData) ? 
                    array_merge(['enabled' => true], $this->panelData) : 
                    $this->collect();
                
                if (!$data['enabled']) {
                    return '<div class="php-debugbar-panel">
                        <div style="padding: 15px; text-align: center; color: #666;">
                            <strong>CodeSnoutr Debugbar Integration is Disabled</strong>
                            <p>Enable in configuration to see code quality metrics</p>
                        </div>
                    </div>';
                }

                $html = '<div class="php-debugbar-panel" style="max-height: 400px; overflow-y: auto;">';
                
                // Add custom CSS
                $html .= $this->getPanelStyles();
                
                // Use the complex data if available
                if (!empty($this->panelData)) {
                    // Alerts section
                    if (!empty($this->panelData['alerts'])) {
                        $html .= $this->renderAlertsSection($this->panelData['alerts']);
                    }
                    
                    // Health score and key metrics
                    if (!empty($this->panelData['stats'])) {
                        $html .= $this->renderHealthSection($this->panelData['stats']);
                    }
                    
                    // Statistics section
                    if (!empty($this->panelData['stats'])) {
                        $html .= $this->renderStatsSection($this->panelData['stats']);
                    }
                    
                    // Recent activity
                    if (!empty($this->panelData['recent_activity'])) {
                        $html .= $this->renderRecentActivitySection($this->panelData['recent_activity']);
                    }
                    
                    // Queue status
                    if (!empty($this->panelData['queue_status'])) {
                        $html .= $this->renderQueueSection($this->panelData['queue_status']);
                    }
                    
                    // Performance metrics
                    if (!empty($this->panelData['performance_metrics'])) {
                        $html .= $this->renderPerformanceSection($this->panelData['performance_metrics']);
                    }

                    // System info section
                    if (!empty($this->panelData['system_info'])) {
                        $html .= $this->renderSystemInfoSection($this->panelData['system_info']);
                    }
                } else {
                    // Fallback to simple display
                    $html .= '<div class="codesnoutr-section">';
                    $html .= '<h4>CodeSnoutr Data</h4>';
                    $html .= '<table class="php-debugbar-widgets-table">';
                    foreach ($data as $key => $value) {
                        if ($key !== 'enabled') {
                            $label = ucwords(str_replace('_', ' ', $key));
                            $html .= "<tr><td>{$label}</td><td>{$value}</td></tr>";
                        }
                    }
                    $html .= '</table></div>';
                }

                $html .= '</div>';
                return $html;
            }

            /**
             * Get custom CSS styles for the panel
             */
            protected function getPanelStyles()
            {
                return '<style>
                    .codesnoutr-section { margin-bottom: 15px; }
                    .codesnoutr-section h4 { 
                        margin: 0 0 8px 0; 
                        padding: 5px 0; 
                        border-bottom: 1px solid #ddd; 
                        font-size: 12px;
                        font-weight: bold;
                        color: #333;
                    }
                    .codesnoutr-alert { 
                        padding: 6px 10px; 
                        margin: 3px 0; 
                        border-radius: 3px; 
                        font-size: 11px;
                    }
                    .codesnoutr-alert.critical { background: #ffebee; border-left: 3px solid #f44336; }
                    .codesnoutr-alert.warning { background: #fff8e1; border-left: 3px solid #ff9800; }
                    .codesnoutr-alert.info { background: #e3f2fd; border-left: 3px solid #2196f3; }
                    .codesnoutr-health { 
                        text-align: center; 
                        padding: 10px; 
                        background: #f5f5f5; 
                        border-radius: 5px; 
                        margin-bottom: 10px;
                    }
                    .codesnoutr-health-score { 
                        font-size: 24px; 
                        font-weight: bold; 
                        margin: 5px 0;
                    }
                    .codesnoutr-health-good { color: #4caf50; }
                    .codesnoutr-health-warning { color: #ff9800; }
                    .codesnoutr-health-critical { color: #f44336; }
                    .codesnoutr-metric { 
                        display: inline-block; 
                        margin: 2px 5px; 
                        padding: 3px 8px; 
                        background: #e8f4f8; 
                        border-radius: 12px; 
                        font-size: 10px;
                        border: 1px solid #b3d9e0;
                    }
                    .codesnoutr-activity { 
                        font-size: 10px; 
                        padding: 4px 8px; 
                        margin: 2px 0; 
                        background: #fafafa; 
                        border-left: 2px solid #ddd;
                    }
                    .codesnoutr-activity.scan { border-left-color: #2196f3; }
                    .codesnoutr-activity.issue { border-left-color: #ff5722; }
                    .php-debugbar-widgets-table td { font-size: 11px; padding: 2px 5px; }
                </style>';
            }

            /**
             * Render alerts section
             */
            protected function renderAlertsSection($alerts)
            {
                if (empty($alerts)) return '';
                
                $html = '<div class="codesnoutr-section">';
                $html .= '<h4>🚨 Alerts</h4>';
                
                foreach ($alerts as $alert) {
                    $class = $alert['type'] ?? 'info';
                    $html .= '<div class="codesnoutr-alert ' . $class . '">';
                    $html .= htmlspecialchars($alert['message']);
                    $html .= '</div>';
                }
                
                $html .= '</div>';
                return $html;
            }

            /**
             * Render health section
             */
            protected function renderHealthSection($stats)
            {
                $healthScore = $stats['health_score'] ?? 0;
                $healthClass = $healthScore >= 80 ? 'good' : ($healthScore >= 60 ? 'warning' : 'critical');
                
                $html = '<div class="codesnoutr-section">';
                $html .= '<div class="codesnoutr-health">';
                $html .= '<div>Code Health Score</div>';
                $html .= '<div class="codesnoutr-health-score codesnoutr-health-' . $healthClass . '">' . $healthScore . '%</div>';
                
                // Key metrics
                if (isset($stats['unresolved_issues'])) {
                    $html .= '<span class="codesnoutr-metric">🔍 ' . $stats['unresolved_issues'] . ' unresolved</span>';
                }
                if (isset($stats['critical_issues'])) {
                    $html .= '<span class="codesnoutr-metric">🚨 ' . $stats['critical_issues'] . ' critical</span>';
                }
                if (isset($stats['scans_today'])) {
                    $html .= '<span class="codesnoutr-metric">📊 ' . $stats['scans_today'] . ' scans today</span>';
                }
                
                $html .= '</div></div>';
                return $html;
            }

            /**
             * Render statistics section
             */
            protected function renderStatsSection($stats)
            {
                $html = '<div class="codesnoutr-section">';
                $html .= '<h4>📊 Statistics</h4>';
                $html .= '<table class="php-debugbar-widgets-table">';
                
                $importantStats = [
                    'total_scans' => 'Total Scans',
                    'scans_today' => 'Scans Today',
                    'total_issues' => 'Total Issues',
                    'security_issues' => 'Security Issues',
                    'performance_issues' => 'Performance Issues',
                    'quality_issues' => 'Quality Issues',
                    'resolved_today' => 'Resolved Today',
                    'last_scan' => 'Last Scan'
                ];
                
                foreach ($importantStats as $key => $label) {
                    if (isset($stats[$key])) {
                        $value = $stats[$key];
                        $html .= "<tr><td>{$label}</td><td><strong>{$value}</strong></td></tr>";
                    }
                }
                
                $html .= '</table></div>';
                return $html;
            }

            /**
             * Render recent activity section
             */
            protected function renderRecentActivitySection($activity)
            {
                $html = '<div class="codesnoutr-section">';
                $html .= '<h4>🕒 Recent Activity</h4>';
                
                foreach (array_slice($activity, 0, 5) as $item) {
                    $class = $item['type'] ?? 'info';
                    $html .= '<div class="codesnoutr-activity ' . $class . '">';
                    
                    if ($item['type'] === 'scan') {
                        $html .= "📋 Scan #{$item['id']} - {$item['issues_count']} issues - {$item['created_at']}";
                    } elseif ($item['type'] === 'issue') {
                        $severity = strtoupper($item['severity']);
                        $icon = $item['severity'] === 'critical' ? '🚨' : ($item['severity'] === 'high' ? '⚠️' : '🔍');
                        $html .= "{$icon} {$severity} {$item['category']} in {$item['file_path']} - {$item['created_at']}";
                    }
                    
                    $html .= '</div>';
                }
                
                $html .= '</div>';
                return $html;
            }

            /**
             * Render queue section
             */
            protected function renderQueueSection($queueStatus)
            {
                $html = '<div class="codesnoutr-section">';
                $html .= '<h4>⚡ Queue Status</h4>';
                
                if ($queueStatus['enabled']) {
                    $html .= '<table class="php-debugbar-widgets-table">';
                    $html .= "<tr><td>Connection</td><td>{$queueStatus['connection']}</td></tr>";
                    $html .= "<tr><td>Pending Jobs</td><td>{$queueStatus['pending_jobs']}</td></tr>";
                    $html .= "<tr><td>Failed Jobs</td><td>{$queueStatus['failed_jobs']}</td></tr>";
                    $html .= '</table>';
                } else {
                    $html .= '<p style="color: #666; font-size: 11px;">Queue processing is disabled</p>';
                }
                
                $html .= '</div>';
                return $html;
            }

            /**
             * Render performance section
             */
            protected function renderPerformanceSection($metrics)
            {
                $html = '<div class="codesnoutr-section">';
                $html .= '<h4>📈 Performance</h4>';
                $html .= '<table class="php-debugbar-widgets-table">';
                
                foreach ($metrics as $key => $value) {
                    if ($key === 'most_problematic_file' && is_array($value)) {
                        $html .= "<tr><td>Most Issues</td><td>{$value['file']} ({$value['issues']})</td></tr>";
                    } else {
                        $label = ucwords(str_replace('_', ' ', $key));
                        $html .= "<tr><td>{$label}</td><td>{$value}</td></tr>";
                    }
                }
                
                $html .= '</table></div>';
                return $html;
            }

            /**
             * Render system info section
             */
            protected function renderSystemInfoSection($systemInfo)
            {
                $html = '<div class="codesnoutr-section">';
                $html .= '<h4>🔧 System Information</h4>';
                $html .= '<table class="php-debugbar-widgets-table">';
                
                $importantInfo = [
                    'version' => 'CodeSnoutr Version',
                    'memory_usage' => 'Memory Usage',
                    'memory_usage_percent' => 'Memory Usage %',
                    'cache_enabled' => 'Cache Enabled',
                    'ai_enabled' => 'AI Enabled',
                    'queue_enabled' => 'Queue Enabled',
                    'environment' => 'Environment'
                ];
                
                foreach ($importantInfo as $key => $label) {
                    if (isset($systemInfo[$key])) {
                        $value = is_bool($systemInfo[$key]) ? 
                            ($systemInfo[$key] ? 'Yes' : 'No') : $systemInfo[$key];
                        $html .= "<tr><td>{$label}</td><td>{$value}</td></tr>";
                    }
                }
                
                $html .= '</table></div>';
                return $html;
            }
        }
    }
}