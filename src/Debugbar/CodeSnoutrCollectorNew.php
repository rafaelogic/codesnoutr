<?php

namespace Rafaelogic\CodeSnoutr\Debugbar;

use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
                    ];

                    return $data;
                } catch (\Exception $e) {
                    return [
                        'enabled' => false,
                        'error' => $e->getMessage()
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
                        "default" => "{}"
                    ],
                    "$name:badge" => [
                        "map" => "$name.stats.total_issues",
                        "default" => "null"
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
             * Get basic statistics
             */
            protected function getStats($settings)
            {
                $stats = [];

                try {
                    if ($settings['show_scan_count']) {
                        $stats['total_scans'] = Scan::count();
                        $stats['scans_today'] = Scan::whereDate('created_at', today())->count();
                    }

                    if ($settings['show_issue_count']) {
                        $stats['total_issues'] = Issue::count();
                        $stats['critical_issues'] = Issue::where('severity', 'critical')->count();
                        $stats['unresolved_issues'] = Issue::where('status', '!=', 'resolved')->count();
                    }
                } catch (\Exception $e) {
                    $stats['error'] = 'Unable to load stats';
                }

                return $stats;
            }

            /**
             * Get system information
             */
            protected function getSystemInfo()
            {
                return [
                    'version' => '1.0.0',
                    'php_version' => PHP_VERSION,
                    'memory_usage' => $this->formatBytes(memory_get_usage(true)),
                    'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
                    'cache_enabled' => config('codesnoutr.cache.enabled', true),
                    'ai_enabled' => config('codesnoutr.ai.enabled', false),
                ];
            }

            /**
             * Format bytes into human readable format
             */
            protected function formatBytes($bytes, $precision = 2)
            {
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];

                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }

                return round($bytes, $precision) . ' ' . $units[$i];
            }

            /**
             * Get panel content (HTML)
             */
            public function getPanel()
            {
                $data = $this->collect();
                
                if (!$data['enabled']) {
                    return '<div class="php-debugbar-panel"><p>CodeSnoutr debugbar integration is disabled</p></div>';
                }

                $html = '<div class="php-debugbar-panel">';
                
                // Stats section
                if (!empty($data['stats'])) {
                    $html .= '<h3>Statistics</h3>';
                    $html .= '<table class="php-debugbar-widgets-table">';
                    foreach ($data['stats'] as $key => $value) {
                        $label = ucwords(str_replace('_', ' ', $key));
                        $html .= "<tr><td>{$label}</td><td>{$value}</td></tr>";
                    }
                    $html .= '</table>';
                }

                // System info section
                if (!empty($data['system_info'])) {
                    $html .= '<h3>System Information</h3>';
                    $html .= '<table class="php-debugbar-widgets-table">';
                    foreach ($data['system_info'] as $key => $value) {
                        $label = ucwords(str_replace('_', ' ', $key));
                        $value = is_bool($value) ? ($value ? 'Yes' : 'No') : $value;
                        $html .= "<tr><td>{$label}</td><td>{$value}</td></tr>";
                    }
                    $html .= '</table>';
                }

                $html .= '</div>';
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
