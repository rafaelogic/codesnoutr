<?php

namespace Rafaelogic\CodeSnoutr\Contracts\Issues;

use Illuminate\Support\Collection;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Symfony\Component\HttpFoundation\Response;

interface IssueExportServiceInterface
{
    /**
     * Export issues to specified format
     */
    public function export(Collection $issues, string $format, ?Scan $scan = null): Response;

    /**
     * Get supported export formats
     */
    public function getSupportedFormats(): array;

    /**
     * Export issues as JSON
     */
    public function exportAsJson(Collection $issues, ?Scan $scan = null): Response;

    /**
     * Export issues as CSV
     */
    public function exportAsCsv(Collection $issues, ?Scan $scan = null): Response;

    /**
     * Export issues as PDF report
     */
    public function exportAsPdf(Collection $issues, ?Scan $scan = null): Response;
}