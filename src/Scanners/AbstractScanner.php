<?php

namespace Rafaelogic\CodeSnoutr\Scanners;

use Illuminate\Foundation\Application;
use Rafaelogic\CodeSnoutr\Scanners\Rules\SecurityRules;
use Rafaelogic\CodeSnoutr\Scanners\Rules\PerformanceRules;
use Rafaelogic\CodeSnoutr\Scanners\Rules\QualityRules;
use Rafaelogic\CodeSnoutr\Scanners\Rules\LaravelRules;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use Symfony\Component\Finder\Finder;

abstract class AbstractScanner
{
    protected Application $app;
    protected Parser $parser;
    protected array $rules = [];
    protected array $issues = [];
    protected array $stats = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
        $this->initializeRules();
    }

    /**
     * Initialize scanning rules based on configuration
     */
    protected function initializeRules(): void
    {
        $this->rules = [
            'security' => new SecurityRules(),
            'performance' => new PerformanceRules(),
            'quality' => new QualityRules(),
            'laravel' => new LaravelRules(),
        ];
    }

    /**
     * Abstract method for performing scans
     */
    abstract public function scan(?string $path, array $categories, array $options = []): array;

    /**
     * Abstract method for performing scans with progress tracking
     */
    public function scanWithProgress(?string $path, array $categories, array $options = [], ?callable $progressCallback = null): array
    {
        // Default implementation - subclasses can override for better progress tracking
        if ($progressCallback) {
            $progressCallback(30, 'Performing scan...');
        }
        
        $result = $this->scan($path, $categories, $options);
        
        if ($progressCallback) {
            $progressCallback(70, 'Scan completed, processing results...');
        }
        
        return $result;
    }

    /**
     * Scan a single file
     */
    protected function scanFile(string $filePath, array $categories): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        $issues = [];

        // Only scan PHP files
        if ($this->isPhpFile($filePath)) {
            $issues = $this->analyzePhpFile($filePath, $content, $categories);
        } elseif ($this->isBladeFile($filePath)) {
            $issues = $this->analyzeBladeFile($filePath, $content, $categories);
        }

        return [
            'file_path' => $filePath,
            'issues' => $issues,
            'file_size' => strlen($content),
            'line_count' => substr_count($content, "\n") + 1,
        ];
    }

    /**
     * Analyze PHP file for issues
     */
    protected function analyzePhpFile(string $filePath, string $content, array $categories): array
    {
        $issues = [];

        try {
            // Parse PHP code into AST
            $ast = $this->parser->parse($content);
            
            if ($ast === null) {
                return [];
            }

            // Run category-specific rules
            foreach ($categories as $category) {
                if (isset($this->rules[$category])) {
                    $categoryIssues = $this->rules[$category]->analyze($filePath, $ast, $content);
                    $issues = array_merge($issues, $categoryIssues);
                }
            }

        } catch (\Exception $e) {
            // Log parse errors but don't fail the scan
            $issues[] = [
                'file_path' => $filePath,
                'line_number' => 1,
                'column_number' => 1,
                'category' => 'quality',
                'severity' => 'warning',
                'rule_name' => 'parse_error',
                'rule_id' => 'quality.parse_error',
                'title' => 'PHP Parse Error',
                'description' => 'Unable to parse PHP file: ' . $e->getMessage(),
                'suggestion' => 'Check for syntax errors in the PHP code.',
                'context' => [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine() ?? 1,
                ],
            ];
        }

        return $issues;
    }

    /**
     * Analyze Blade template file
     */
    protected function analyzeBladeFile(string $filePath, string $content, array $categories): array
    {
        $issues = [];

        // Basic Blade-specific checks
        if (in_array('security', $categories)) {
            $issues = array_merge($issues, $this->checkBladeSecurityIssues($filePath, $content));
        }

        if (in_array('quality', $categories)) {
            $issues = array_merge($issues, $this->checkBladeQualityIssues($filePath, $content));
        }

        return $issues;
    }

    /**
     * Check Blade files for security issues
     */
    protected function checkBladeSecurityIssues(string $filePath, string $content): array
    {
        $issues = [];
        $lines = explode("\n", $content);

        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based line numbers

            // Check for unescaped output
            if (preg_match('/\{\!\!\s*\$.*?\!\!\}/', $line)) {
                $issues[] = [
                    'file_path' => $filePath,
                    'line_number' => $lineNumber,
                    'category' => 'security',
                    'severity' => 'warning',
                    'rule_name' => 'unescaped_output',
                    'rule_id' => 'security.unescaped_output',
                    'title' => 'Unescaped Output in Blade',
                    'description' => 'Using {!! !!} syntax can lead to XSS vulnerabilities.',
                    'suggestion' => 'Use {{ }} syntax for automatic escaping, or explicitly escape with e() helper.',
                    'context' => [
                        'code' => [$line],
                        'line_content' => trim($line),
                    ],
                ];
            }

            // Check for potential XSS in JavaScript
            if (preg_match('/\{\{\s*\$.*?\}\}/', $line) && stripos($line, '<script') !== false) {
                $issues[] = [
                    'file_path' => $filePath,
                    'line_number' => $lineNumber,
                    'category' => 'security',
                    'severity' => 'critical',
                    'rule_name' => 'xss_in_script',
                    'rule_id' => 'security.xss_in_script',
                    'title' => 'Potential XSS in JavaScript',
                    'description' => 'Blade output inside script tags can lead to XSS vulnerabilities.',
                    'suggestion' => 'Use JSON encoding: @json($variable) or escape properly.',
                    'context' => [
                        'code' => [$line],
                        'line_content' => trim($line),
                    ],
                ];
            }
        }

        return $issues;
    }

    /**
     * Check Blade files for quality issues
     */
    protected function checkBladeQualityIssues(string $filePath, string $content): array
    {
        $issues = [];
        $lines = explode("\n", $content);

        // Check for overly complex templates
        $phpBlocks = preg_match_all('/@php.*?@endphp/s', $content);
        if ($phpBlocks > 3) {
            $issues[] = [
                'file_path' => $filePath,
                'line_number' => 1,
                'category' => 'quality',
                'severity' => 'warning',
                'rule_name' => 'complex_blade_template',
                'rule_id' => 'quality.complex_blade_template',
                'title' => 'Complex Blade Template',
                'description' => 'Template contains too many @php blocks, consider moving logic to controller.',
                'suggestion' => 'Move complex logic to the controller or create a view composer.',
                'context' => [
                    'php_blocks_count' => $phpBlocks,
                    'recommendation' => 'Max recommended: 3 @php blocks',
                ],
            ];
        }

        return $issues;
    }

    /**
     * Get files to scan based on path and configuration
     */
    protected function getFilesToScan(string $path): Finder
    {
        $finder = new Finder();
        $finder->files();

        // Configure based on path type
        if (is_file($path)) {
            $finder->in(dirname($path))->name(basename($path));
        } else {
            $finder->in($path);
        }

        // Apply file extension filters
        $extensions = config('codesnoutr.scan.file_extensions', ['php', 'blade.php']);
        foreach ($extensions as $extension) {
            $finder->name("*.{$extension}");
        }

        // Apply exclusion patterns
        $excludePaths = config('codesnoutr.scan.exclude_paths', ['vendor', 'node_modules']);
        foreach ($excludePaths as $excludePath) {
            $finder->exclude($excludePath);
        }

        // Apply file size limit
        $maxFileSize = config('codesnoutr.scan.max_file_size', 1024 * 1024); // 1MB
        $finder->size("<= {$maxFileSize}");

        return $finder;
    }

    /**
     * Check if file is a PHP file
     */
    protected function isPhpFile(string $filePath): bool
    {
        return pathinfo($filePath, PATHINFO_EXTENSION) === 'php';
    }

    /**
     * Check if file is a Blade template
     */
    protected function isBladeFile(string $filePath): bool
    {
        return str_ends_with($filePath, '.blade.php');
    }

    /**
     * Get code context around a line
     */
    protected function getCodeContext(string $content, int $lineNumber, int $contextLines = 3): array
    {
        $lines = explode("\n", $content);
        $start = max(0, $lineNumber - $contextLines - 1);
        $end = min(count($lines), $lineNumber + $contextLines);

        $context = [];
        for ($i = $start; $i < $end; $i++) {
            $context[$i + 1] = $lines[$i] ?? '';
        }

        return $context;
    }

    /**
     * Calculate complexity score for a file
     */
    protected function calculateComplexity(string $content): int
    {
        $complexity = 1; // Base complexity

        // Count decision points
        $patterns = [
            '/\bif\s*\(/',           // if statements
            '/\bwhile\s*\(/',        // while loops
            '/\bfor\s*\(/',          // for loops
            '/\bforeach\s*\(/',      // foreach loops
            '/\bswitch\s*\(/',       // switch statements
            '/\bcase\s+/',           // case statements
            '/\bcatch\s*\(/',        // catch blocks
            '/\?\s*.*?\s*:/',        // ternary operators
            '/\&\&|\|\|/',           // logical operators
        ];

        foreach ($patterns as $pattern) {
            $complexity += preg_match_all($pattern, $content);
        }

        return $complexity;
    }

    /**
     * Update scan statistics
     */
    protected function updateStats(string $key, $value): void
    {
        $this->stats[$key] = $value;
    }

    /**
     * Get current scan statistics
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * Validate categories against available categories
     */
    protected function validateCategories(array $categories): array
    {
        $availableCategories = array_keys($this->rules);
        return array_intersect($categories, $availableCategories);
    }

    /**
     * Check if scanning should be skipped for this file
     */
    protected function shouldSkipFile(string $filePath): bool
    {
        // Skip if file is too large
        $maxSize = config('codesnoutr.scan.max_file_size', 1024 * 1024);
        if (filesize($filePath) > $maxSize) {
            return true;
        }

        // Skip if file is in excluded paths
        $excludePaths = config('codesnoutr.scan.exclude_paths', []);
        foreach ($excludePaths as $excludePath) {
            if (strpos($filePath, $excludePath) !== false) {
                return true;
            }
        }

        return false;
    }
}
