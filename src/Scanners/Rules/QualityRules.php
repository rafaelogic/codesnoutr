<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules;

class QualityRules extends AbstractRuleEngine
{
    /**
     * Analyze code for quality issues
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        
        // Check coding standards
        $this->checkCodingStandards($filePath, $content);
        
        // Check complexity issues
        $this->checkComplexity($filePath, $content);
        
        // Check documentation
        $this->checkDocumentation($filePath, $content);
        
        // Check naming conventions
        $this->checkNamingConventions($filePath, $content);
        
        // Check best practices
        $this->checkBestPractices($filePath, $content);
        
        return $this->getIssues();
    }

    /**
     * Check coding standards
     */
    protected function checkCodingStandards(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for long lines
            if (strlen($line) > 120) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'info',
                    'quality.long_line',
                    'Line Too Long',
                    'Line exceeds 120 characters which can hurt readability.',
                    'Consider breaking the line into multiple shorter lines.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for trailing whitespace
            if (preg_match('/\\s+$/', $line) && !empty(trim($line))) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'info',
                    'quality.trailing_whitespace',
                    'Trailing Whitespace',
                    'Line has trailing whitespace characters.',
                    'Remove trailing whitespace for cleaner code.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for mixed indentation
            if (preg_match('/^\\t+ +/', $line) || preg_match('/^ +\\t+/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'warning',
                    'quality.mixed_indentation',
                    'Mixed Indentation',
                    'Line uses mixed tabs and spaces for indentation.',
                    'Use consistent indentation (either tabs or spaces, not both).',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check complexity issues
     */
    protected function checkComplexity(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for deeply nested conditions
            $indentLevel = strlen($line) - strlen(ltrim($line));
            if ($indentLevel > 24) { // More than 6 levels of indentation (4 spaces each)
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'warning',
                    'quality.deep_nesting',
                    'Deep Nesting',
                    'Code is deeply nested which can hurt readability and maintainability.',
                    'Consider extracting methods or using early returns to reduce nesting.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for complex conditionals
            $conditionCount = substr_count($line, '&&') + substr_count($line, '||');
            if ($conditionCount > 3) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'warning',
                    'quality.complex_condition',
                    'Complex Conditional',
                    'Conditional statement has too many logical operators.',
                    'Consider breaking complex conditions into multiple simpler conditions or extract to variables.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for long parameter lists
            if (preg_match('/function\\s+\\w+\\s*\\([^)]{80,}\\)/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'warning',
                    'quality.long_parameter_list',
                    'Long Parameter List',
                    'Function has a very long parameter list which can be hard to maintain.',
                    'Consider using parameter objects or reducing the number of parameters.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check documentation
     */
    protected function checkDocumentation(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for public methods without docblocks
            if (preg_match('/public\\s+function\\s+\\w+/', $line)) {
                // Look for docblock in previous lines
                $hasDocblock = false;
                for ($i = max(0, $lineNumber - 5); $i < $lineNumber - 1; $i++) {
                    if (isset($lines[$i]) && preg_match('/\\/\\*\\*/', $lines[$i])) {
                        $hasDocblock = true;
                        break;
                    }
                }
                
                if (!$hasDocblock) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'quality.missing_docblock',
                        'Missing Documentation',
                        'Public method lacks proper documentation.',
                        'Add a PHPDoc comment describing the method, parameters, and return value.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for class without docblock
            if (preg_match('/class\\s+\\w+/', $line)) {
                // Look for docblock in previous lines
                $hasDocblock = false;
                for ($i = max(0, $lineNumber - 5); $i < $lineNumber - 1; $i++) {
                    if (isset($lines[$i]) && preg_match('/\\/\\*\\*/', $lines[$i])) {
                        $hasDocblock = true;
                        break;
                    }
                }
                
                if (!$hasDocblock) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'quality.missing_class_docblock',
                        'Missing Class Documentation',
                        'Class lacks proper documentation.',
                        'Add a PHPDoc comment describing the class purpose and usage.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
        }
    }

    /**
     * Check naming conventions
     */
    protected function checkNamingConventions(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for non-descriptive variable names
            if (preg_match('/\\$([a-z])\\s*=/', $line, $matches)) {
                $varName = $matches[1];
                if (strlen($varName) == 1 && !in_array($varName, ['i', 'j', 'k', 'x', 'y', 'z'])) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'quality.non_descriptive_variable',
                        'Non-descriptive Variable Name',
                        'Single letter variable names (except loop counters) reduce code readability.',
                        'Use descriptive variable names that clearly indicate their purpose.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for camelCase in variable names
            if (preg_match('/\\$([a-z]+_[a-z_]+)/', $line, $matches)) {
                $variableName = $matches[1];
                
                // Skip if variable is assigned from PHP constants or has legitimate snake_case usage
                if (!$this->isLegitimateSnakeCaseUsage($variableName, $line, $content)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'quality.snake_case_variable',
                        'Snake Case Variable',
                        'PHP conventions prefer camelCase for variable names.',
                        'Use camelCase naming: $userName instead of $user_name',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for PascalCase in class names
            if (preg_match('/class\\s+([a-z][a-zA-Z0-9_]*)/', $line, $matches)) {
                $className = $matches[1];
                if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $className)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'warning',
                        'quality.class_naming',
                        'Class Naming Convention',
                        'Class names should follow PascalCase convention.',
                        'Use PascalCase for class names: MyClass instead of myClass or my_class',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
        }
    }

    /**
     * Check best practices
     */
    protected function checkBestPractices(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for unused variables with enhanced context awareness
            if (preg_match('/\\$([a-zA-Z_][a-zA-Z0-9_]*)\\s*=/', $line, $matches) && 
                !preg_match('/\\$this->/', $line)) {
                
                $variableName = $matches[1];
                
                // Skip if this is a known exception
                if ($this->isVariableException($variableName, $filePath, $content, $lineNumber)) {
                    continue;
                }
                
                // This is a simple check - a more sophisticated implementation would track variable usage
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'info',
                    'quality.potential_unused_variable',
                    'Potential Unused Variable',
                    'Variable assignment detected - ensure it is used.',
                    'Remove unused variables or use them appropriately in your code.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for magic numbers
            if (preg_match('/[^\\w]([0-9]{2,})[^\\w]/', $line, $matches)) {
                $number = $matches[1];
                if ($number != '100' && $number != '200' && $number != '404') { // Common HTTP codes
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'quality.magic_number',
                        'Magic Number',
                        'Hard-coded numbers make code less maintainable.',
                        'Consider using named constants for magic numbers.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for empty catch blocks
            if (preg_match('/catch\\s*\\([^)]+\\)\\s*\\{\\s*\\}/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'warning',
                    'quality.empty_catch_block',
                    'Empty Catch Block',
                    'Empty catch blocks hide exceptions and make debugging difficult.',
                    'Handle the exception appropriately or at least log it.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for TODO comments
            if (preg_match('/(TODO|FIXME|HACK)/i', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'info',
                    'quality.todo_comment',
                    'TODO Comment',
                    'Code contains TODO, FIXME, or HACK comments.',
                    'Address these comments or create proper tickets for future work.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check if a variable should be considered an exception from unused variable rules
     */
    protected function isVariableException(string $variableName, string $filePath, string $content, int $lineNumber): bool
    {
        // Console Command exceptions
        if ($this->isConsoleCommand($content)) {
            // $signature and $description are framework properties in Console Commands
            if (in_array($variableName, ['signature', 'description', 'hidden', 'name'])) {
                return true;
            }
        }

        // Model exceptions
        if ($this->isEloquentModel($content)) {
            // Common Eloquent model properties
            $modelProperties = [
                'table', 'primaryKey', 'keyType', 'incrementing', 'timestamps', 
                'dateFormat', 'connection', 'fillable', 'guarded', 'hidden', 
                'visible', 'appends', 'dates', 'casts', 'touches', 'with', 
                'withCount', 'perPage', 'exists', 'wasRecentlyCreated'
            ];
            
            if (in_array($variableName, $modelProperties)) {
                return true;
            }
        }

        // Controller exceptions
        if ($this->isController($content, $filePath)) {
            // Middleware property in controllers
            if ($variableName === 'middleware') {
                return true;
            }
        }

        // Job/Queue exceptions
        if ($this->isJob($content)) {
            $jobProperties = ['connection', 'queue', 'tries', 'timeout', 'retryAfter', 'maxExceptions', 'backoff'];
            if (in_array($variableName, $jobProperties)) {
                return true;
            }
        }

        // Event/Listener exceptions
        if ($this->isEvent($content) || $this->isListener($content)) {
            $eventProperties = ['broadcastOn', 'broadcastAs', 'broadcastWith'];
            if (in_array($variableName, $eventProperties)) {
                return true;
            }
        }

        // Form Request exceptions
        if ($this->isFormRequest($content)) {
            if (in_array($variableName, ['redirectRoute', 'redirect', 'errorBag'])) {
                return true;
            }
        }

        // Mailable exceptions
        if ($this->isMailable($content)) {
            $mailableProperties = ['from', 'to', 'cc', 'bcc', 'replyTo', 'subject', 'view', 'markdown', 'theme', 'with'];
            if (in_array($variableName, $mailableProperties)) {
                return true;
            }
        }

        // Resource exceptions
        if ($this->isResource($content)) {
            if (in_array($variableName, ['wrap', 'preserveKeys'])) {
                return true;
            }
        }

        // Test exceptions
        if ($this->isTest($content, $filePath)) {
            // Common test setup variables that might appear unused
            $testProperties = ['seed', 'seeder'];
            if (in_array($variableName, $testProperties)) {
                return true;
            }
        }

        // Interface/Abstract/Trait implementations
        if ($this->hasInheritance($content)) {
            // Check if this variable might be implementing an interface or extending a class
            if ($this->isImplementingInterface($variableName, $content) || 
                $this->isOverridingParent($variableName, $content)) {
                return true;
            }
        }

        // Configuration files
        if ($this->isConfigFile($filePath)) {
            // Config arrays and return values
            return true;
        }

        // Migration files
        if ($this->isMigration($filePath)) {
            // Migration variables like $table, $schema
            if (in_array($variableName, ['table', 'schema'])) {
                return true;
            }
        }

        // Service Provider exceptions
        if ($this->isServiceProvider($content)) {
            if (in_array($variableName, ['defer', 'provides'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the content represents a Console Command
     */
    protected function isConsoleCommand(string $content): bool
    {
        return preg_match('/extends\s+(Command|Illuminate\\\\Console\\\\Command)/', $content) ||
               preg_match('/use\s+Illuminate\\\\Console\\\\Command/', $content);
    }

    /**
     * Check if the content represents an Eloquent Model
     */
    protected function isEloquentModel(string $content): bool
    {
        return preg_match('/extends\s+(Model|Illuminate\\\\Database\\\\Eloquent\\\\Model)/', $content) ||
               preg_match('/use\s+Illuminate\\\\Database\\\\Eloquent\\\\Model/', $content);
    }

    /**
     * Check if the content represents a Controller
     */
    protected function isController(string $content, string $filePath): bool
    {
        return preg_match('/extends\s+(Controller|BaseController)/', $content) ||
               str_contains($filePath, 'Controller') ||
               preg_match('/use\s+Illuminate\\\\Routing\\\\Controller/', $content);
    }

    /**
     * Check if the content represents a Job
     */
    protected function isJob(string $content): bool
    {
        return preg_match('/implements\s+ShouldQueue/', $content) ||
               preg_match('/use\s+(Dispatchable|InteractsWithQueue|Queueable|SerializesModels)/', $content);
    }

    /**
     * Check if the content represents an Event
     */
    protected function isEvent(string $content): bool
    {
        return preg_match('/implements\s+ShouldBroadcast/', $content) ||
               preg_match('/use\s+(Dispatchable|InteractsWithSockets|SerializesModels)/', $content);
    }

    /**
     * Check if the content represents a Listener
     */
    protected function isListener(string $content): bool
    {
        return preg_match('/implements\s+ShouldQueue/', $content) ||
               str_contains($content, 'public function handle(');
    }

    /**
     * Check if the content represents a Form Request
     */
    protected function isFormRequest(string $content): bool
    {
        return preg_match('/extends\s+(FormRequest|Illuminate\\\\Foundation\\\\Http\\\\FormRequest)/', $content);
    }

    /**
     * Check if the content represents a Mailable
     */
    protected function isMailable(string $content): bool
    {
        return preg_match('/extends\s+(Mailable|Illuminate\\\\Mail\\\\Mailable)/', $content);
    }

    /**
     * Check if the content represents a Resource
     */
    protected function isResource(string $content): bool
    {
        return preg_match('/extends\s+(JsonResource|ResourceCollection|Illuminate\\\\Http\\\\Resources)/', $content);
    }

    /**
     * Check if the content represents a Test
     */
    protected function isTest(string $content, string $filePath): bool
    {
        return preg_match('/extends\s+(TestCase|PHPUnit)/', $content) ||
               str_contains($filePath, 'Test') ||
               preg_match('/use\s+(PHPUnit\\\\Framework\\\\TestCase|Tests\\\\TestCase)/', $content);
    }

    /**
     * Check if the class has inheritance (extends, implements, uses traits)
     */
    protected function hasInheritance(string $content): bool
    {
        return preg_match('/(extends|implements|use\s+[A-Z])/', $content);
    }

    /**
     * Check if variable is implementing an interface method
     */
    protected function isImplementingInterface(string $variableName, string $content): bool
    {
        // Look for interface implementations that might require specific properties
        return preg_match('/implements\s+[\w\\\\]+/', $content);
    }

    /**
     * Check if variable is overriding a parent property
     */
    protected function isOverridingParent(string $variableName, string $content): bool
    {
        // Look for parent class extensions that might require specific properties
        return preg_match('/extends\s+[\w\\\\]+/', $content);
    }

    /**
     * Check if this is a configuration file
     */
    protected function isConfigFile(string $filePath): bool
    {
        return str_contains($filePath, '/config/') ||
               str_ends_with($filePath, '.config.php');
    }

    /**
     * Check if this is a migration file
     */
    protected function isMigration(string $filePath): bool
    {
        return str_contains($filePath, '/migrations/') ||
               preg_match('/\d{4}_\d{2}_\d{2}_\d{6}_/', basename($filePath));
    }

    /**
     * Check if the content represents a Service Provider
     */
    protected function isServiceProvider(string $content): bool
    {
        return preg_match('/extends\s+(ServiceProvider|Illuminate\\\\Support\\\\ServiceProvider)/', $content);
    }

    /**
     * Check if snake_case variable usage is legitimate
     */
    protected function isLegitimateSnakeCaseUsage(string $variableName, string $line, string $content): bool
    {
        // Exception 1: Variables assigned from PHP constants (e.g., curl_getinfo($ch, CURLINFO_HEADER_SIZE))
        if (preg_match('/\$' . preg_quote($variableName, '/') . '\s*=.*[A-Z_]{2,}[A-Z0-9_]*/', $line)) {
            return true;
        }

        // Exception 2: Variables that clearly correspond to API or external library naming conventions
        if (preg_match('/\$' . preg_quote($variableName, '/') . '\s*=.*(?:curl_|json_|array_|str_|preg_|file_|is_|in_|mime_|path)/', $line)) {
            return true;
        }

        // Exception 3: Variables in database-related contexts (column names, table names)
        if (preg_match('/\$' . preg_quote($variableName, '/') . '\s*=.*\$\w+->\w+/', $line)) {
            return true;
        }

        // Exception 4: Variables that match database column naming patterns
        $databaseColumnPatterns = [
            'created_at', 'updated_at', 'deleted_at', 'email_verified_at',
            'remember_token', 'api_token', 'user_id', 'post_id', 'category_id',
            'first_name', 'last_name', 'phone_number', 'zip_code', 'street_address',
            'http_code', 'header_size', 'content_type', 'total_time', 'redirect_count',
            'file_size', 'mime_type', 'is_readable', 'is_writable', 'file_perms',
            'file_extension', 'base_name', 'database_url', 'api_key', 'server_name',
            'document_root', 'request_uri', 'access_token', 'refresh_token', 'expires_in',
            'token_type', 'decoded_data'
        ];
        
        if (in_array($variableName, $databaseColumnPatterns)) {
            return true;
        }

        // Exception 5: Variables in migration files or schema definitions
        if (preg_match('/Schema::|Blueprint|\$table->/', $content)) {
            return true;
        }

        // Exception 6: Variables that are array keys or accessing array/object properties with snake_case
        if (preg_match('/\$' . preg_quote($variableName, '/') . '\s*=.*\[[\'"]\w+_\w+[\'"]\]/', $line) ||
            preg_match('/\$' . preg_quote($variableName, '/') . '\s*=.*->\w+_\w+/', $line)) {
            return true;
        }

        // Exception 7: Variables in test files (often use descriptive snake_case names)
        if ($this->isTest($content, '') && 
            preg_match('/(test_|expected_|actual_|mock_|stub_)/', $variableName)) {
            return true;
        }

        // Exception 8: Environment variable related assignments
        if (preg_match('/\$' . preg_quote($variableName, '/') . '\s*=.*(?:env\(|getenv\(|\$_ENV\[|\$_SERVER\[)/', $line)) {
            return true;
        }

        // Exception 9: Common PHP superglobal or built-in variable patterns
        $builtInPatterns = [
            'http_response_header', 'php_errormsg', 'this_file', 'script_name',
            'request_uri', 'query_string', 'document_root', 'server_name'
        ];
        
        if (in_array($variableName, $builtInPatterns)) {
            return true;
        }

        // Exception 10: Variables that are part of external API responses or configurations
        if (preg_match('/\$' . preg_quote($variableName, '/') . '\s*=.*(?:json_decode|simplexml_load|curl_exec|file_get_contents)/', $line)) {
            return true;
        }

        return false;
    }
}
