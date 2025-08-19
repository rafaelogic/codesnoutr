<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules;

class LaravelRules extends AbstractRuleEngine
{
    /**
     * Analyze code for Laravel best practices
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        
        // Check Eloquent best practices
        $this->checkEloquentBestPractices($filePath, $content);
        
        // Check route definitions
        $this->checkRouteDefinitions($filePath, $content);
        
        // Check validation rules
        $this->checkValidationRules($filePath, $content);
        
        // Check service container usage
        $this->checkServiceContainer($filePath, $content);
        
        // Check blade directives
        $this->checkBladeDirectives($filePath, $content);
        
        return $this->getIssues();
    }

    /**
     * Check Eloquent best practices
     */
    protected function checkEloquentBestPractices(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for raw SQL in Eloquent models
            if (preg_match('/DB::(select|insert|update|delete|statement)/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'laravel',
                    'info',
                    'laravel.raw_sql_in_model',
                    'Raw SQL in Model',
                    'Using raw SQL queries in models bypasses Eloquent benefits.',
                    'Use Eloquent methods or Query Builder for better maintainability.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for missing timestamps
            if (preg_match('/class\s+\w+\s+extends\s+Model/', $line)) {
                // Look for timestamps property in the next 20 lines
                $hasTimestamps = false;
                for ($i = $lineNumber; $i < min($lineNumber + 20, count($lines)); $i++) {
                    if (preg_match('/\$timestamps\s*=/', $lines[$i])) {
                        $hasTimestamps = true;
                        break;
                    }
                }
                
                if (!$hasTimestamps) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'laravel',
                        'info',
                        'laravel.missing_timestamps_property',
                        'Missing Timestamps Property',
                        'Explicitly define $timestamps property in models for clarity.',
                        'Add public $timestamps = true; or false; to your model.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for select(*) usage
            if (preg_match('/->select\(\s*[\'\"]\*[\'\"]/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'laravel',
                    'warning',
                    'laravel.select_all_columns',
                    'Select All Columns',
                    'Using select(*) can impact performance by loading unnecessary data.',
                    'Specify only the columns you need: select([\'id\', \'name\', \'email\']).',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check route definitions
     */
    protected function checkRouteDefinitions(string $filePath, string $content): void
    {
        // Only check files that contain routes
        if (!preg_match('/Route::|router->/', $content)) {
            return;
        }
        
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for routes without names
            if (preg_match('/Route::(get|post|put|patch|delete)/', $line) && 
                !preg_match('/->name\(/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'laravel',
                    'info',
                    'laravel.route_without_name',
                    'Route Without Name',
                    'Routes should have names for better maintainability and testing.',
                    'Add ->name(\'route.name\') to your route definition.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for missing route model binding
            if (preg_match('/Route::(get|post|put|patch|delete)\s*\(\s*[\'\"]\S*\{id\}/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'laravel',
                    'info',
                    'laravel.missing_route_model_binding',
                    'Missing Route Model Binding',
                    'Consider using route model binding instead of manual ID resolution.',
                    'Use {model} instead of {id} and type-hint the model in your controller.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check validation rules
     */
    protected function checkValidationRules(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for request validation in controllers
            if (preg_match('/\$request->/', $line) && 
                preg_match('/(store|update|create)/', $filePath) &&
                !preg_match('/validate\(/', $content)) {
                
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'laravel',
                    'warning',
                    'laravel.missing_validation',
                    'Missing Request Validation',
                    'User input should be validated before processing.',
                    'Use $request->validate() or Form Request classes for validation.',
                    $this->getCodeContext($content, $lineNumber)
                ));
                break; // Only report once per file
            }
            
            // Check for weak validation rules
            if (preg_match('/[\'\"](email|password)[\'\"]\s*=>\s*[\'\"](required)[\'\"]\s*[,\]]/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'laravel',
                    'warning',
                    'laravel.weak_validation',
                    'Weak Validation Rules',
                    'Email and password fields need stronger validation rules.',
                    'Use \'email\' => \'required|email|unique:users\' and strong password rules.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check service container usage
     */
    protected function checkServiceContainer(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for app() helper overuse
            if (preg_match_all('/app\(/', $line) > 2) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'laravel',
                    'info',
                    'laravel.service_locator_overuse',
                    'Service Locator Overuse',
                    'Overusing app() helper can indicate poor dependency injection.',
                    'Use constructor injection or method injection instead of service locator pattern.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for facade usage in models
            if (preg_match('/class\s+\w+\s+extends\s+Model/', $content) &&
                preg_match('/(Auth::|Cache::|Log::|Storage::)/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'laravel',
                    'warning',
                    'laravel.facade_in_model',
                    'Facade Usage in Model',
                    'Using facades in models can make testing difficult and violate SRP.',
                    'Inject dependencies through constructor or use dedicated service classes.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check Blade directives
     */
    protected function checkBladeDirectives(string $filePath, string $content): void
    {
        // Only check .blade.php files
        if (!str_ends_with($filePath, '.blade.php')) {
            return;
        }
        
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for @php blocks
            if (preg_match('/@php/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'laravel',
                    'info',
                    'laravel.php_in_blade',
                    'PHP Code in Blade Template',
                    'Avoid @php blocks in Blade templates for better separation of concerns.',
                    'Move logic to controllers, view composers, or custom Blade directives.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for missing CSRF protection in forms
            if (preg_match('/<form.*method\s*=\s*[\'\"](post|put|patch|delete)/i', $line) &&
                !preg_match('/@csrf|{{ csrf_field\(\) }}/', $content)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'laravel',
                    'critical',
                    'laravel.missing_csrf',
                    'Missing CSRF Protection',
                    'Forms with non-GET methods must include CSRF protection.',
                    'Add @csrf directive inside your form tag.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for hardcoded URLs
            if (preg_match('/href\s*=\s*[\'\"]\/((?!http)[^\'\"]*)[\'\"]/i', $line, $matches)) {
                $url = $matches[1];
                if (!empty($url)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'laravel',
                        'info',
                        'laravel.hardcoded_url',
                        'Hardcoded URL in Template',
                        'Hardcoded URLs make applications less flexible and harder to maintain.',
                        'Use route() helper: href="{{ route(\'route.name\') }}" or url() helper.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
        }
    }
}
