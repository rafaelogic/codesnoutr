<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Quality;

use Rafaelogic\CodeSnoutr\Scanners\Rules\AbstractRuleEngine;

abstract class AbstractQualityRule extends AbstractRuleEngine
{
    /**
     * Analyze code for quality issues
     *
     * @param string $filePath The path to the file being analyzed
     * @param array $ast The AST representation (not used in quality rules)
     * @param string $content The file content as string
     * @return array Array of issues found
     */
    abstract public function analyze(string $filePath, array $ast, string $content): array;

    /**
     * Get the lines of content as an array
     *
     * @param string $content
     * @return array
     */
    protected function getLines(string $content): array
    {
        return explode("\n", $content);
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

    // Laravel/PHP framework detection methods

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
}