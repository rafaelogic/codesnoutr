<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CodeSnoutr Scan Report #{{ $scan->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .scan-info {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .scan-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .scan-info td {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .scan-info td:first-child {
            font-weight: bold;
            width: 30%;
            color: #374151;
        }
        
        .stats-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            width: 18%;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .critical { color: #dc2626; }
        .high { color: #ea580c; }
        .medium { color: #ca8a04; }
        .low { color: #65a30d; }
        
        .issues-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .issues-table th {
            background-color: #f3f4f6;
            padding: 12px 8px;
            text-align: left;
            border-bottom: 2px solid #d1d5db;
            font-weight: bold;
            color: #374151;
        }
        
        .issues-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        
        .issues-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .severity-badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .severity-critical {
            background-color: #fecaca;
            color: #dc2626;
        }
        
        .severity-high {
            background-color: #fed7aa;
            color: #ea580c;
        }
        
        .severity-medium {
            background-color: #fef3c7;
            color: #ca8a04;
        }
        
        .severity-low {
            background-color: #dcfce7;
            color: #65a30d;
        }
        
        .file-path {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            color: #4b5563;
            max-width: 200px;
            word-wrap: break-word;
        }
        
        .description {
            max-width: 250px;
            word-wrap: break-word;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        @page {
            margin: 20mm 15mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CodeSnoutr Scan Report</h1>
        <p>Generated on {{ $exported_at }}</p>
    </div>

    <div class="scan-info">
        <table>
            <tr>
                <td>Scan ID</td>
                <td>#{{ $scan->id }}</td>
            </tr>
            <tr>
                <td>Scan Type</td>
                <td>{{ ucfirst($scan->type) }}</td>
            </tr>
            <tr>
                <td>Target</td>
                <td>{{ $scan->target }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>{{ ucfirst($scan->status) }}</td>
            </tr>
            <tr>
                <td>Files Scanned</td>
                <td>{{ number_format($scan->files_scanned) }}</td>
            </tr>
            <tr>
                <td>Issues Found</td>
                <td>{{ number_format($scan->issues_found) }}</td>
            </tr>
            <tr>
                <td>Started At</td>
                <td>{{ $scan->started_at ? $scan->started_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Completed At</td>
                <td>{{ $scan->completed_at ? $scan->completed_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <h2>Issue Summary</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number critical">{{ $stats['critical_issues'] }}</div>
            <div class="stat-label">Critical</div>
        </div>
        <div class="stat-card">
            <div class="stat-number high">{{ $stats['high_issues'] }}</div>
            <div class="stat-label">High</div>
        </div>
        <div class="stat-card">
            <div class="stat-number medium">{{ $stats['medium_issues'] }}</div>
            <div class="stat-label">Medium</div>
        </div>
        <div class="stat-card">
            <div class="stat-number low">{{ $stats['low_issues'] }}</div>
            <div class="stat-label">Low</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_issues'] }}</div>
            <div class="stat-label">Total</div>
        </div>
    </div>

    @if($issues->count() > 0)
        <h2>Issues Detail</h2>
        <table class="issues-table">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Line</th>
                    <th>Severity</th>
                    <th>Category</th>
                    <th>Title</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($issues as $issue)
                    <tr>
                        <td class="file-path">{{ $issue->file_path }}</td>
                        <td>{{ $issue->line_number }}</td>
                        <td>
                            <span class="severity-badge severity-{{ $issue->severity }}">
                                {{ strtoupper($issue->severity) }}
                            </span>
                        </td>
                        <td>{{ $issue->category }}</td>
                        <td>{{ $issue->title }}</td>
                        <td class="description">
                            {{ Str::limit($issue->description, 100) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; margin: 40px 0; color: #6b7280;">
            <p>No issues found in this scan.</p>
        </div>
    @endif

    <div class="footer">
        <p>Generated by CodeSnoutr v1.0 | {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>