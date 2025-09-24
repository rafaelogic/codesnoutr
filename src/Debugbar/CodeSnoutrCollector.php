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

                    $data = [
                        'enabled' => true,
                        'stats' => $this->getStats($settings),
                        'system_info' => $this->getSystemInfo(),
                        'recent_activity' => $this->getRecentActivity($settings),
                        'queue_status' => $this->getQueueStatus(),
                        'performance_metrics' => $this->getPerformanceMetrics(),
                        'alerts' => $this->getAlerts(),
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
                        "map" => "$name.stats.unresolved_issues",
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
                        'show_last_scan' => config('codesnoutr.debugbar.show_last_scan', true),
                        'show_performance' => config('codesnoutr.debugbar.show_performance', true),
                        'show_queue_status' => config('codesnoutr.debugbar.show_queue_status', true),
                        'show_alerts' => config('codesnoutr.debugbar.show_alerts', true),
                        'max_recent_items' => config('codesnoutr.debugbar.max_recent_items', 5),
                    ];

                    try {
                        $dbSettings = Setting::whereIn('key', array_keys($defaultSettings))
                            ->pluck('value', 'key')
                            ->toArray();
                        return array_merge($defaultSettings, $dbSettings);
                    } catch (\Exception $e) {
                        return $defaultSettings;
                    }
                });
            }

            /**
             * Get comprehensive statistics
             */
            protected function getStats($settings)
            {
                $stats = [];

                try {
                    if ($settings['show_scan_count']) {
                        $stats['total_scans'] = Scan::count();
                        $stats['scans_today'] = Scan::whereDate('created_at', today())->count();
                        $stats['scans_this_week'] = Scan::whereBetween('created_at', [now()->startOfWeek(), now()])->count();
                        
                        $lastScan = Scan::latest()->first();
                        $stats['last_scan'] = $lastScan ? $lastScan->created_at->diffForHumans() : 'Never';
                    }

                    if ($settings['show_issue_count']) {
                        $stats['total_issues'] = Issue::count();
                        $stats['critical_issues'] = Issue::where('severity', 'critical')->count();
                        $stats['high_issues'] = Issue::where('severity', 'high')->count();
                        $stats['medium_issues'] = Issue::where('severity', 'medium')->count();
                        $stats['low_issues'] = Issue::where('severity', 'low')->count();
                        $stats['unresolved_issues'] = Issue::where('status', '!=', 'resolved')->count();
                        $stats['resolved_today'] = Issue::where('status', 'resolved')
                            ->whereDate('updated_at', today())->count();
                        
                        // Issue categories
                        $stats['security_issues'] = Issue::where('category', 'security')
                            ->where('status', '!=', 'resolved')->count();
                        $stats['performance_issues'] = Issue::where('category', 'performance')
                            ->where('status', '!=', 'resolved')->count();
                        $stats['quality_issues'] = Issue::where('category', 'quality')
                            ->where('status', '!=', 'resolved')->count();
                    }
                    
                    // Calculate health score
                    $stats['health_score'] = $this->calculateHealthScore();
                    
                } catch (\Exception $e) {
                    $stats['error'] = 'Unable to load stats: ' . $e->getMessage();
                }

                return $stats;
            }

            /**
             * Get enhanced system information
             */
            protected function getSystemInfo()
            {
                $memoryUsage = memory_get_usage(true);
                $memoryPeak = memory_get_peak_usage(true);
                $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
                
                return [
                    'version' => config('codesnoutr.version', '1.0.0'),
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'memory_usage' => $this->formatBytes($memoryUsage),
                    'memory_peak' => $this->formatBytes($memoryPeak),
                    'memory_usage_percent' => $memoryLimit > 0 ? round(($memoryUsage / $memoryLimit) * 100, 1) : 0,
                    'memory_limit' => $this->formatBytes($memoryLimit),
                    'cache_enabled' => config('codesnoutr.cache.enabled', true),
                    'cache_driver' => config('cache.default'),
                    'ai_enabled' => config('codesnoutr.ai.enabled', false),
                    'ai_provider' => config('codesnoutr.ai.provider', 'none'),
                    'queue_enabled' => config('codesnoutr.queue.enabled', false),
                    'queue_connection' => config('queue.default'),
                    'debugbar_version' => class_exists('Barryvdh\Debugbar\LaravelDebugbar') ? 
                        \Barryvdh\Debugbar\LaravelDebugbar::VERSION ?? 'unknown' : 'not installed',
                    'environment' => app()->environment(),
                    'uptime' => $this->getUptime(),
                ];
            }

            /**
             * Format bytes into human readable format
             */
            public function formatBytes($bytes, $precision = 2)
            {
                if ($bytes <= 0) return '0 B';
                
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];

                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }

                return round($bytes, $precision) . ' ' . $units[$i];
            }

            /**
             * Parse memory limit string to bytes
             */
            protected function parseMemoryLimit($limit)
            {
                $limit = trim($limit);
                $last = strtolower($limit[strlen($limit) - 1]);
                $value = intval($limit);
                
                switch ($last) {
                    case 'g':
                        $value *= 1024;
                    case 'm':
                        $value *= 1024;
                    case 'k':
                        $value *= 1024;
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
             * Calculate overall health score based on issues
             */
            protected function calculateHealthScore()
            {
                try {
                    $totalIssues = Issue::count();
                    $criticalIssues = Issue::where('severity', 'critical')->count();
                    $highIssues = Issue::where('severity', 'high')->count();
                    $resolvedIssues = Issue::where('status', 'resolved')->count();
                    
                    if ($totalIssues === 0) {
                        return 100;
                    }
                    
                    // Calculate weighted score
                    $score = 100;
                    $score -= ($criticalIssues * 20); // Critical issues heavily impact score
                    $score -= ($highIssues * 10);     // High issues moderately impact score
                    $score -= (($totalIssues - $resolvedIssues) * 2); // Unresolved issues impact score
                    
                    return max(0, min(100, $score));
                } catch (\Exception $e) {
                    return 0;
                }
            }

            /**
             * Get recent activity
             */
            protected function getRecentActivity($settings)
            {
                $activity = [];
                $maxItems = $settings['max_recent_items'] ?? 5;
                
                try {
                    // Recent scans
                    $recentScans = Scan::with('issues')
                        ->latest()
                        ->limit($maxItems)
                        ->get()
                        ->map(function ($scan) {
                            return [
                                'type' => 'scan',
                                'id' => $scan->id,
                                'created_at' => $scan->created_at->diffForHumans(),
                                'issues_count' => $scan->issues()->count(),
                                'scan_type' => $scan->scan_type ?? 'unknown',
                                'status' => $scan->status ?? 'completed'
                            ];
                        });
                    
                    // Recent issues
                    $recentIssues = Issue::latest()
                        ->limit($maxItems)
                        ->get()
                        ->map(function ($issue) {
                            return [
                                'type' => 'issue',
                                'id' => $issue->id,
                                'severity' => $issue->severity,
                                'category' => $issue->category,
                                'file_path' => basename($issue->file_path ?? ''),
                                'created_at' => $issue->created_at->diffForHumans(),
                                'status' => $issue->status
                            ];
                        });
                    
                    $activity = collect($recentScans)
                        ->concat($recentIssues)
                        ->sortByDesc('created_at')
                        ->take($maxItems)
                        ->values()
                        ->toArray();
                        
                } catch (\Exception $e) {
                    $activity = [['type' => 'error', 'message' => 'Unable to load recent activity']];
                }
                
                return $activity;
            }

            /**
             * Get queue status information
             */
            protected function getQueueStatus()
            {
                try {
                    if (!config('codesnoutr.queue.enabled', false)) {
                        return ['enabled' => false, 'message' => 'Queue processing disabled'];
                    }
                    
                    $connection = config('codesnoutr.queue.connection', config('queue.default'));
                    $queueName = config('codesnoutr.queue.name', 'default');
                    
                    // Try to get queue size (this varies by queue driver)
                    $queueSize = 0;
                    try {
                        $queueSize = Queue::size($queueName);
                    } catch (\Exception $e) {
                        // Some queue drivers don't support size()
                    }
                    
                    return [
                        'enabled' => true,
                        'connection' => $connection,
                        'queue_name' => $queueName,
                        'pending_jobs' => $queueSize,
                        'failed_jobs' => DB::table('failed_jobs')->count(),
                        'auto_start' => config('codesnoutr.queue.auto_start', false),
                    ];
                } catch (\Exception $e) {
                    return [
                        'enabled' => false,
                        'error' => 'Unable to get queue status: ' . $e->getMessage()
                    ];
                }
            }

            /**
             * Get performance metrics
             */
            protected function getPerformanceMetrics()
            {
                try {
                    $metrics = [];
                    
                    // Average scan duration
                    $avgScanTime = Scan::whereNotNull('completed_at')
                        ->whereNotNull('started_at')
                        ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration')
                        ->value('avg_duration');
                    
                    $metrics['avg_scan_duration'] = $avgScanTime ? round($avgScanTime, 2) . 's' : 'N/A';
                    
                    // Issues per scan average
                    $avgIssuesPerScan = Scan::withCount('issues')
                        ->get()
                        ->avg('issues_count');
                    
                    $metrics['avg_issues_per_scan'] = $avgIssuesPerScan ? round($avgIssuesPerScan, 1) : 0;
                    
                    // Most problematic file
                    $problematicFile = Issue::select('file_path', DB::raw('COUNT(*) as issue_count'))
                        ->where('status', '!=', 'resolved')
                        ->groupBy('file_path')
                        ->orderByDesc('issue_count')
                        ->first();
                    
                    if ($problematicFile) {
                        $metrics['most_problematic_file'] = [
                            'file' => basename($problematicFile->file_path),
                            'issues' => $problematicFile->issue_count
                        ];
                    }
                    
                    // Scanning efficiency
                    $totalScans = Scan::count();
                    $successfulScans = Scan::where('status', 'completed')->count();
                    $metrics['scan_success_rate'] = $totalScans > 0 ? 
                        round(($successfulScans / $totalScans) * 100, 1) . '%' : '0%';
                    
                    return $metrics;
                } catch (\Exception $e) {
                    return ['error' => 'Unable to calculate performance metrics'];
                }
            }

            /**
             * Get alerts and warnings
             */
            protected function getAlerts()
            {
                $alerts = [];
                
                try {
                    // Critical issues alert
                    $criticalCount = Issue::where('severity', 'critical')
                        ->where('status', '!=', 'resolved')
                        ->count();
                    
                    if ($criticalCount > 0) {
                        $alerts[] = [
                            'type' => 'critical',
                            'message' => "{$criticalCount} critical security issues need immediate attention",
                            'count' => $criticalCount
                        ];
                    }
                    
                    // Memory usage alert
                    $memoryUsage = memory_get_usage(true);
                    $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
                    
                    if ($memoryLimit > 0 && ($memoryUsage / $memoryLimit) > 0.8) {
                        $alerts[] = [
                            'type' => 'warning',
                            'message' => 'High memory usage detected (' . 
                                round(($memoryUsage / $memoryLimit) * 100, 1) . '%)',
                            'count' => 1
                        ];
                    }
                    
                    // Stale scans alert
                    $lastScan = Scan::latest()->first();
                    if ($lastScan && $lastScan->created_at->diffInDays() > 7) {
                        $alerts[] = [
                            'type' => 'info',
                            'message' => 'Last scan was ' . $lastScan->created_at->diffForHumans(),
                            'count' => 1
                        ];
                    }
                    
                    // Failed jobs alert
                    $failedJobs = DB::table('failed_jobs')->count();
                    if ($failedJobs > 0) {
                        $alerts[] = [
                            'type' => 'warning',
                            'message' => "{$failedJobs} failed queue jobs",
                            'count' => $failedJobs
                        ];
                    }
                    
                } catch (\Exception $e) {
                    $alerts[] = [
                        'type' => 'error',
                        'message' => 'Unable to load alerts: ' . $e->getMessage(),
                        'count' => 1
                    ];
                }
                
                return $alerts;
            }

            /**
             * Get enhanced panel content (HTML)
             */
            public function getPanel()
            {
                $data = $this->collect();
                
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
                
                // Alerts section
                if (!empty($data['alerts'])) {
                    $html .= $this->renderAlertsSection($data['alerts']);
                }
                
                // Health score and key metrics
                if (!empty($data['stats'])) {
                    $html .= $this->renderHealthSection($data['stats']);
                }
                
                // Statistics section
                if (!empty($data['stats'])) {
                    $html .= $this->renderStatsSection($data['stats']);
                }
                
                // Recent activity
                if (!empty($data['recent_activity'])) {
                    $html .= $this->renderRecentActivitySection($data['recent_activity']);
                }
                
                // Queue status
                if (!empty($data['queue_status'])) {
                    $html .= $this->renderQueueSection($data['queue_status']);
                }
                
                // Performance metrics
                if (!empty($data['performance_metrics'])) {
                    $html .= $this->renderPerformanceSection($data['performance_metrics']);
                }

                // System info section
                if (!empty($data['system_info'])) {
                    $html .= $this->renderSystemInfoSection($data['system_info']);
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
    } else {
        // Fallback class when DebugBar is not available
        class CodeSnoutrCollector
        {
            public function getName()
            {
                return 'codesnoutr';
            }

            public function collect()
            {
                return [
                    'enabled' => false,
                    'message' => 'Laravel Debugbar not installed'
                ];
            }
        }
    }
}
