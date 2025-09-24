# PDF Export Implementation

## Overview

The CodeSnoutr DashboardController now includes a comprehensive PDF export system that supports multiple PDF generation libraries. The implementation gracefully handles missing dependencies and provides clear instructions for enabling PDF functionality.

## Supported PDF Libraries

The system automatically detects and uses available PDF libraries in the following priority order:

1. **Spatie Laravel PDF** (Recommended)
   - Installation: `composer require spatie/laravel-pdf`
   - Uses Chrome/Chromium for high-quality rendering
   - Best for complex layouts and modern CSS

2. **TCPDF**
   - Installation: `composer require tecnickcom/tcpdf`
   - Pure PHP implementation
   - Good for server environments without Chrome

3. **Dompdf**
   - Installation: `composer require dompdf/dompdf`
   - Lightweight pure PHP solution
   - Basic HTML/CSS support

## Implementation Details

### Export Method Signature
```php
public function export(Scan $scan, string $format = 'json'): Response|JsonResponse
```

### Return Types Fixed
- Changed return type from `Response` to `Response|JsonResponse`
- Fixed `exportJson()` method return type to `JsonResponse`
- Proper handling of JSON responses for missing PDF libraries

### PDF Template
- Created `resources/views/exports/pdf-report.blade.php`
- Professional styling with responsive layout
- Issue severity color coding
- Comprehensive scan information display

### Graceful Degradation
When no PDF library is available, the system returns a JSON response with:
- Clear error message explaining the requirement
- Installation instructions for each supported library
- Alternative download link for JSON format
- List of available export formats

### Usage Examples

#### Route Usage
```php
// Export as PDF (if library available)
GET /codesnoutr/export/{scan}/pdf

// Export as JSON
GET /codesnoutr/export/{scan}/json

// Export as CSV
GET /codesnoutr/export/{scan}/csv
```

#### Programmatic Usage
```php
$controller = new DashboardController($scanManager);
$response = $controller->export($scan, 'pdf');
```

## Error Handling

### Library Detection
The system uses string-based class detection to avoid static analysis errors:
```php
if (class_exists('Spatie\\LaravelPdf\\Facades\\Pdf')) {
    // Use Spatie PDF
}
```

### Dynamic Instantiation
PDF libraries are instantiated using variable class names to prevent undefined class errors:
```php
$tcpdfClass = 'TCPDF';
$pdf = new $tcpdfClass('P', 'mm', 'A4', true, 'UTF-8', false);
```

## Installation Recommendations

For new installations, we recommend:
```bash
# Best option - modern PDF generation
composer require spatie/laravel-pdf

# Alternative - server compatibility
composer require tecnickcom/tcpdf

# Fallback - minimal dependencies
composer require dompdf/dompdf
```

## Benefits

1. **No Breaking Changes**: Existing JSON/CSV exports continue working
2. **Progressive Enhancement**: PDF becomes available when libraries are installed
3. **Clear Documentation**: Users know exactly how to enable PDF functionality
4. **Multiple Options**: Supports various deployment environments
5. **Professional Output**: High-quality PDF reports with proper formatting

## Future Enhancements

- Custom PDF templates per scan type
- Configuration options for PDF styling
- Batch export capabilities
- Email PDF reports directly