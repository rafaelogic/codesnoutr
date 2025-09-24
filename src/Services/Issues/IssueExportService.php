<?php

namespace Rafaelogic\CodeSnoutr\Services\Issues;

use Rafaelogic\CodeSnoutr\Models\Scan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class IssueExportService
{
    /**
     * Export issues in the specified format
     */
    public function export(Collection $issues, string $format = 'json', ?Scan $scan = null)
    {
        try {
            switch (strtolower($format)) {
                case 'json':
                    return $this->exportToJson($issues, $scan);
                    
                case 'csv':
                    return $this->exportToCsv($issues, $scan);
                    
                default:
                    throw new \InvalidArgumentException("Unsupported export format: {$format}");
            }
        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export issues to JSON format
     */
    protected function exportToJson(Collection $issues, ?Scan $scan = null)
    {
        $data = [
            'export_info' => [
                'exported_at' => now()->toISOString(),
                'format' => 'json',
                'total_issues' => $issues->count(),
                'scan_info' => $scan ? [
                    'id' => $scan->id,
                    'name' => $scan->name,
                    'created_at' => $scan->created_at->toISOString(),
                    'scan_type' => $scan->scan_type,
                    'target_path' => $scan->target_path
                ] : null
            ],
            'summary' => $this->generateSummary($issues),
            'issues' => $issues->map(function ($issue) {
                return [
                    'id' => $issue->id,
                    'title' => $issue->title,
                    'description' => $issue->description,
                    'category' => $issue->category,
                    'severity' => $issue->severity,
                    'rule_name' => $issue->rule_name,
                    'file_path' => $issue->file_path,
                    'line_number' => $issue->line_number,
                    'column_number' => $issue->column_number,
                    'fixed' => $issue->fixed,
                    'fix_method' => $issue->fix_method,
                    'fixed_at' => $issue->fixed_at?->toISOString(),
                    'ai_fix' => $issue->ai_fix,
                    'ai_confidence' => $issue->ai_confidence,
                    'created_at' => $issue->created_at->toISOString(),
                    'updated_at' => $issue->updated_at->toISOString()
                ];
            })->toArray()
        ];

        $filename = $this->generateFilename($scan, 'json');
        
        return Response::json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export issues to CSV format
     */
    protected function exportToCsv(Collection $issues, ?Scan $scan = null)
    {
        $csvData = [];
        
        // Add headers
        $csvData[] = [
            'ID',
            'Title',
            'Description',
            'Category',
            'Severity',
            'Rule Name',
            'File Path',
            'Line Number',
            'Column Number',
            'Fixed',
            'Fix Method',
            'Fixed At',
            'Has AI Fix',
            'AI Confidence',
            'Created At',
            'Updated At'
        ];

        // Add issue data
        foreach ($issues as $issue) {
            $csvData[] = [
                $issue->id,
                $issue->title,
                $issue->description,
                $issue->category,
                $issue->severity,
                $issue->rule_name ?? '',
                $issue->file_path,
                $issue->line_number,
                $issue->column_number ?? '',
                $issue->fixed ? 'Yes' : 'No',
                $issue->fix_method ?? '',
                $issue->fixed_at?->toDateTimeString() ?? '',
                !empty($issue->ai_fix) ? 'Yes' : 'No',
                $issue->ai_confidence ?? '',
                $issue->created_at->toDateTimeString(),
                $issue->updated_at->toDateTimeString()
            ];
        }

        $filename = $this->generateFilename($scan, 'csv');
        
        $output = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Generate summary statistics for export
     */
    protected function generateSummary(Collection $issues): array
    {
        return [
            'total_issues' => $issues->count(),
            'fixed_issues' => $issues->where('fixed', true)->count(),
            'unfixed_issues' => $issues->where('fixed', false)->count(),
            'severity_breakdown' => $issues->groupBy('severity')->map->count()->toArray(),
            'category_breakdown' => $issues->groupBy('category')->map->count()->toArray(),
            'files_with_issues' => $issues->pluck('file_path')->unique()->count(),
            'issues_with_ai_fixes' => $issues->filter(fn($issue) => !empty($issue->ai_fix))->count(),
            'average_ai_confidence' => $issues
                ->filter(fn($issue) => !empty($issue->ai_confidence))
                ->avg('ai_confidence')
        ];
    }

    /**
     * Generate filename for export
     */
    protected function generateFilename(?Scan $scan, string $format): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $scanName = $scan ? str_replace([' ', '/', '\\'], '_', $scan->name) : 'scan_results';
        
        return "codesnoutr_{$scanName}_{$timestamp}.{$format}";
    }

    /**
     * Get export statistics
     */
    public function getExportStats(Collection $issues): array
    {
        return [
            'total_exportable' => $issues->count(),
            'categories' => $issues->groupBy('category')->keys()->toArray(),
            'severities' => $issues->groupBy('severity')->keys()->toArray(),
            'date_range' => [
                'oldest' => $issues->min('created_at'),
                'newest' => $issues->max('created_at')
            ]
        ];
    }
}