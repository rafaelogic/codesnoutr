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
            
            // Check for raw SQL in Eloquent models with exceptions for complex queries
            if (preg_match('/DB::(select|insert|update|delete|statement)/', $line)) {
                // Skip if this appears to be a legitimate complex query scenario
                if (!$this->isLegitimateRawSqlUsage($line, $content)) {
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
                        'Explicitly define $timestamps property in models for clarity. Must use public visibility (Laravel requirement).',
                        'Add public $timestamps = true; (or false) to your model. Note: Must be public, not protected.',
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
            
            // Check for routes without names (only route definitions, not utility methods)
            if (preg_match('/Route::(get|post|put|patch|delete)\s*\(/', $line) && 
                !preg_match('/->name\(/', $line) &&
                !preg_match('/Route::(getRoutes|has|current|is|currentRouteName|getCurrentRoute)\s*\(/', $line)) {
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

    /**
     * Check if raw SQL usage is legitimate (complex queries, performance optimizations)
     */
    protected function isLegitimateRawSqlUsage(string $line, string $content): bool
    {
        // Allow raw SQL for complex aggregations
        if (preg_match('/(COUNT|SUM|AVG|MAX|MIN|GROUP_CONCAT|CASE\s+WHEN)/i', $line)) {
            return true;
        }

        // Allow raw SQL for database-specific functions
        if (preg_match('/(JSON_EXTRACT|MATCH\s+AGAINST|ST_|REGEXP|SUBSTRING)/i', $line)) {
            return true;
        }

        // Allow raw SQL in migration files
        if (preg_match('/\d{4}_\d{2}_\d{2}_\d{6}_/', $content)) {
            return true;
        }

        // Allow raw SQL in seeder files
        if (str_contains($content, 'extends Seeder') || str_contains($content, 'database/seeds/')) {
            return true;
        }

        // Allow raw SQL for performance-critical operations (with comment indicating intention)
        if (preg_match('/\/\*.*performance.*\*\/|\/\/.*performance/i', $line)) {
            return true;
        }

        return false;
    }

    /**
     * Check if a class implements inheritance patterns that might require specific properties
     */
    protected function hasFrameworkInheritance(string $content): bool
    {
        $patterns = [
            '/extends\s+(Command|Controller|Model|Job|Event|Listener|Request|Mailable|Notification|ServiceProvider)/',
            '/implements\s+(ShouldQueue|ShouldBroadcast|Arrayable|Jsonable)/',
            '/use\s+(Dispatchable|InteractsWithQueue|Queueable|SerializesModels|InteractsWithSockets)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if this is a legitimate Laravel framework property
     */
    protected function isFrameworkProperty(string $variableName, string $content): bool
    {
        // Console Command properties
        if (preg_match('/extends\s+Command/', $content) && 
            in_array($variableName, ['signature', 'description', 'hidden', 'name'])) {
            return true;
        }

        // Model properties
        if (preg_match('/extends\s+Model/', $content)) {
            $modelProps = [
                'table', 'primaryKey', 'keyType', 'incrementing', 'timestamps',
                'dateFormat', 'connection', 'fillable', 'guarded', 'hidden',
                'visible', 'appends', 'dates', 'casts', 'touches', 'with',
                'withCount', 'perPage', 'exists', 'wasRecentlyCreated'
            ];
            if (in_array($variableName, $modelProps)) {
                return true;
            }
        }

        // Controller properties
        if (preg_match('/extends\s+(Controller|BaseController)/', $content) && 
            $variableName === 'middleware') {
            return true;
        }

        // Job properties
        if (preg_match('/implements\s+ShouldQueue|use\s+Queueable/', $content)) {
            $jobProps = ['connection', 'queue', 'tries', 'timeout', 'retryAfter', 'maxExceptions', 'backoff'];
            if (in_array($variableName, $jobProps)) {
                return true;
            }
        }

        // Event properties
        if (preg_match('/implements\s+ShouldBroadcast/', $content)) {
            $eventProps = ['broadcastOn', 'broadcastAs', 'broadcastWith'];
            if (in_array($variableName, $eventProps)) {
                return true;
            }
        }

        // Form Request properties
        if (preg_match('/extends\s+FormRequest/', $content)) {
            if (in_array($variableName, ['redirectRoute', 'redirect', 'errorBag'])) {
                return true;
            }
        }

        // Service Provider properties
        if (preg_match('/extends\s+ServiceProvider/', $content)) {
            if (in_array($variableName, ['defer', 'provides'])) {
                return true;
            }
        }

        return false;
    }
}
