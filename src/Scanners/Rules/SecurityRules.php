<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class SecurityRules extends AbstractRuleEngine
{
    protected string $filePath = '';
    protected string $content = '';
    
    /**
     * Set file context for node visitor usage
     */
    public function setFileContext(string $filePath, string $content): void
    {
        $this->filePath = $filePath;
        $this->content = $content;
    }
    
    /**
     * Analyze code for security issues
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        
        // Check for SQL injection vulnerabilities
        $this->checkSqlInjection($filePath, $content);
        
        // Check for XSS vulnerabilities
        $this->checkXssVulnerabilities($filePath, $content);
        
        // Check for hardcoded credentials
        $this->checkHardcodedCredentials($filePath, $content);
        
        // Check for insecure file operations
        $this->checkFileOperations($filePath, $content);
        
        // Check for unsafe deserialization
        $this->checkUnsafeDeserialization($filePath, $content);
        
        // Check for weak cryptography
        $this->checkWeakCryptography($filePath, $content);
        
        return $this->getIssues();
    }

    /**
     * Check for SQL injection vulnerabilities
     */
    protected function checkSqlInjection(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for raw SQL with concatenation
            if (preg_match('/DB::(select|insert|update|delete|statement)\\s*\\(\\s*[\'"]\\s*\\w+.*\\$/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'critical',
                    'security.sql_injection',
                    'Potential SQL Injection',
                    'Raw SQL queries with variable concatenation are vulnerable to SQL injection.',
                    'Use parameter binding: DB::select(\'SELECT * FROM users WHERE id = ?\', [$id])',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for SQL with string concatenation (more general pattern)
            if (preg_match('/\\$\\s*=\\s*[\'"].*?(SELECT|INSERT|UPDATE|DELETE).*?[\'"]\\s*\\.\\s*\\$/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'critical',
                    'security.sql_injection_concat',
                    'SQL Injection via String Concatenation',
                    'Building SQL queries with string concatenation is vulnerable to SQL injection.',
                    'Use parameter binding or Eloquent ORM instead of string concatenation.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for DB::statement with variables
            if (preg_match('/DB::statement\\s*\\(\\s*\\$/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'critical',
                    'security.db_statement_variable',
                    'Potential SQL Injection in DB::statement',
                    'Using variables directly in DB::statement can lead to SQL injection.',
                    'Use parameter binding: DB::statement(\'SQL ?\', [$param])',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for unsafe query building
            if (preg_match('/->whereRaw\\s*\\(\\s*[\'"]\\s*\\w+.*\\$/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'high',
                    'security.unsafe_where_raw',
                    'Unsafe whereRaw Usage',
                    'whereRaw with variable concatenation can lead to SQL injection.',
                    'Use parameter binding: ->whereRaw(\'column = ?\', [$value])',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for XSS vulnerabilities
     */
    protected function checkXssVulnerabilities(string $filePath, string $content): void
    {
        // Only check .blade.php files for XSS
        if (!str_ends_with($filePath, '.blade.php')) {
            return;
        }
        
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for unescaped output
            if (preg_match('/\\{!!\\s*\\$\\w+/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'high',
                    'security.unescaped_output',
                    'Unescaped Output (XSS Risk)',
                    'Unescaped output can lead to Cross-Site Scripting (XSS) attacks.',
                    'Use double braces for escaped output instead of triple braces unless you trust the content.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for dangerous HTML tags
            if (preg_match('/&lt;(script|iframe|object|embed|form)/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'warning',
                    'security.dangerous_html_tags',
                    'Potentially Dangerous HTML Tags',
                    'These HTML tags can be used for XSS attacks if user input is involved.',
                    'Ensure any dynamic content in these tags is properly sanitized.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for hardcoded credentials
     */
    protected function checkHardcodedCredentials(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Skip comments and common false positives
            $trimmedLine = trim($line);
            if (str_starts_with($trimmedLine, '//') || str_starts_with($trimmedLine, '#') || str_starts_with($trimmedLine, '*')) {
                continue;
            }
            
            // Skip cache keys, configuration keys, and other legitimate uses
            if (preg_match('/cache.*key|config.*key|redis.*key|session.*key|csrf.*key|uuid|_id/i', $line)) {
                continue;
            }
            
            // Skip environment variable assignments (these are fine)
            if (preg_match('/env\s*\(|\.env|\$_ENV/', $line)) {
                continue;
            }
            
            // Check for hardcoded passwords (but not cache keys or UUIDs)
            if (preg_match('/\b(password|pwd)\s*=\s*[\'"](?!.*\b(cache|key|uuid|tenant|config)\b)\w{6,}[\'\"]/i', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'critical',
                    'security.hardcoded_credentials',
                    'Hardcoded Credentials',
                    'Hardcoded passwords are a security risk.',
                    'Use environment variables and Laravel config system: env(\'DB_PASSWORD\')',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for API keys and secrets (but exclude cache keys and UUIDs)
            if (preg_match('/\b(api_key|apikey|access_token|secret_key|private_key)\s*=\s*[\'"](?!.*\b(cache|uuid|tenant|config|active|property|type)\b)[A-Za-z0-9_-]{10,}[\'\"]/i', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'critical',
                    'security.hardcoded_api_keys',
                    'Hardcoded API Keys',
                    'Hardcoded API keys should never be stored in source code.',
                    'Use environment variables: env(\'API_KEY\') and add to .env file',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for database connection strings with embedded passwords
            if (preg_match('/\b(mysql|postgresql|sqlite):\/\/\w+:\w+@/i', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'critical',
                    'security.hardcoded_db_credentials',
                    'Hardcoded Database Credentials',
                    'Database connection strings with embedded credentials are a security risk.',
                    'Use Laravel database configuration with environment variables.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for insecure file operations
     */
    protected function checkFileOperations(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for unsafe file uploads
            if (preg_match('/move_uploaded_file\\s*\\(\\s*\\$_FILES/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'high',
                    'security.unsafe_file_upload',
                    'Unsafe File Upload',
                    'Direct file upload without validation can lead to security vulnerabilities.',
                    'Validate file type, size, and use Laravel file upload methods.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for directory traversal
            if (preg_match('/file_get_contents\\s*\\(\\s*\\$\\w+\\s*\\.\\s*[\'"]\\.\\.[\\/\\\\]/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'high',
                    'security.directory_traversal',
                    'Directory Traversal Risk',
                    'File operations with user input can lead to directory traversal attacks.',
                    'Validate and sanitize file paths before file operations.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for eval() usage
            if (preg_match('/eval\\s*\\(/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'critical',
                    'security.eval_usage',
                    'Dangerous eval() Usage',
                    'eval() function can execute arbitrary code and is a major security risk.',
                    'Avoid eval(). Use proper parsing libraries or alternative approaches.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for unsafe deserialization
     */
    protected function checkUnsafeDeserialization(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for unserialize with user input
            if (preg_match('/unserialize\\s*\\(\\s*\\$_(GET|POST|REQUEST|COOKIE)/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'critical',
                    'security.unsafe_deserialization',
                    'Unsafe Deserialization',
                    'Deserializing user input can lead to object injection attacks.',
                    'Validate and sanitize data before unserializing, or use JSON instead.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for weak cryptography
     */
    protected function checkWeakCryptography(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for MD5 usage
            if (preg_match('/md5\\s*\\(/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'warning',
                    'security.weak_hash_md5',
                    'Weak Hashing Algorithm (MD5)',
                    'MD5 is cryptographically broken and should not be used for security.',
                    'Use hash(\'sha256\', $data) or Laravel Hash facade for passwords.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for SHA1 usage
            if (preg_match('/sha1\\s*\\(/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'warning',
                    'security.weak_hash_sha1',
                    'Weak Hashing Algorithm (SHA1)',
                    'SHA1 is vulnerable and should not be used for security purposes.',
                    'Use hash(\'sha256\', $data) or Laravel Hash facade for passwords.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }
}

class SecurityVisitor extends NodeVisitorAbstract
{
    protected SecurityRules $rules;
    protected string $filePath;
    protected string $content;

    public function __construct(SecurityRules $rules, string $filePath, string $content)
    {
        $this->rules = $rules;
        $this->filePath = $filePath;
        $this->content = $content;
    }

    public function enterNode(Node $node)
    {
        // Check for SQL injection vulnerabilities
        $this->checkSqlInjection($node);
        
        // Check for XSS vulnerabilities  
        $this->checkXssVulnerabilities($node);
        
        // Check mass assignment issues
        $this->checkMassAssignment($node);
        
        // Check for insecure file operations
        $this->checkFileOperations($node);
        
        // Check for weak crypto usage
        $this->checkWeakCrypto($node);
        
        return null;
    }

    /**
     * Check for SQL injection vulnerabilities
     */
    protected function checkSqlInjection(Node $node): void
    {
        // Check for DB::raw() with variables
        if ($node instanceof Node\Expr\StaticCall) {
            if ($this->isMethodCall($node, 'DB', 'raw')) {
                if ($this->containsVariable($node->args[0] ?? null)) {
                    $this->addIssue($this->createIssue(
                        $this->filePath,
                        $this->getLineNumber($node),
                        'security',
                        'critical',
                        'security.sql_injection_raw',
                        'SQL Injection Risk in DB::raw()',
                        'Using variables directly in DB::raw() can lead to SQL injection vulnerabilities.',
                        'Use parameter binding or DB::statement() with bindings instead.',
                        $this->getCodeContext($this->content, $this->getLineNumber($node))
                    ));
                }
            }
        }

        // Check for raw queries with string concatenation
        if ($node instanceof Node\Expr\BinaryOp\Concat) {
            $nodeString = $this->nodeToString($node);
            if (preg_match('/SELECT|INSERT|UPDATE|DELETE/i', $nodeString)) {
                $this->addIssue($this->createIssue(
                    $this->filePath,
                    $this->getLineNumber($node),
                    'security',
                    'critical',
                    'security.sql_injection_concat',
                    'SQL Injection Risk in String Concatenation',
                    'Building SQL queries with string concatenation can lead to SQL injection.',
                    'Use Eloquent ORM, Query Builder, or prepared statements with parameter binding.',
                    $this->getCodeContext($this->content, $this->getLineNumber($node))
                ));
            }
        }
    }

    /**
     * Check for XSS vulnerabilities
     */
    protected function checkXssVulnerabilities(Node $node): void
    {
        // Check for unescaped output functions
        if ($node instanceof Node\Expr\FuncCall) {
            $funcName = $this->getFunctionName($node);
            
            if (in_array($funcName, ['echo', 'print', 'printf'])) {
                if ($this->containsUserInput($node)) {
                    $this->addIssue($this->createIssue(
                        $this->filePath,
                        $this->getLineNumber($node),
                        'security',
                        'critical',
                        'security.xss_unescaped_output',
                        'XSS Risk: Unescaped Output',
                        'Outputting user input without escaping can lead to XSS attacks.',
                        'Use htmlspecialchars() or Laravel\'s e() helper to escape output.',
                        $this->getCodeContext($this->content, $this->getLineNumber($node))
                    ));
                }
            }
        }
    }

    /**
     * Check for mass assignment vulnerabilities
     */
    protected function checkMassAssignment(Node $node): void
    {
        // Check for models without fillable/guarded
        if ($node instanceof Node\Stmt\Class_) {
            if ($this->extendsModel($node)) {
                if (!$this->hasFillableOrGuarded($node)) {
                    $this->addIssue($this->createIssue(
                        $this->filePath,
                        $this->getLineNumber($node),
                        'security',
                        'warning',
                        'security.mass_assignment_unprotected',
                        'Mass Assignment Vulnerability',
                        'Model lacks $fillable or $guarded properties for mass assignment protection.',
                        'Add $fillable array with allowed fields or $guarded array with protected fields.',
                        $this->getCodeContext($this->content, $this->getLineNumber($node))
                    ));
                }
            }
        }
    }

    /**
     * Check for insecure file operations
     */
    protected function checkFileOperations(Node $node): void
    {
        if ($node instanceof Node\Expr\FuncCall) {
            $funcName = $this->getFunctionName($node);
            
            // Check for file operations with user input
            if (in_array($funcName, ['file_get_contents', 'file_put_contents', 'fopen', 'include', 'require'])) {
                if ($this->containsUserInput($node)) {
                    $this->addIssue($this->createIssue(
                        $this->filePath,
                        $this->getLineNumber($node),
                        'security',
                        'critical',
                        'security.file_operation_user_input',
                        'Insecure File Operation',
                        'File operations with user input can lead to directory traversal or code injection.',
                        'Validate and sanitize file paths, use basename(), and restrict to allowed directories.',
                        $this->getCodeContext($this->content, $this->getLineNumber($node))
                    ));
                }
            }
        }
    }

    /**
     * Check for weak cryptography
     */
    protected function checkWeakCrypto(Node $node): void
    {
        if ($node instanceof Node\Expr\FuncCall) {
            $funcName = $this->getFunctionName($node);
            
            // Check for weak hashing algorithms
            if ($funcName === 'md5' || $funcName === 'sha1') {
                $this->addIssue($this->createIssue(
                    $this->filePath,
                    $this->getLineNumber($node),
                    'security',
                    'warning',
                    'security.weak_hashing',
                    'Weak Hashing Algorithm',
                    'MD5 and SHA1 are cryptographically weak and vulnerable to attacks.',
                    'Use password_hash() for passwords or hash() with SHA-256 or better algorithms.',
                    $this->getCodeContext($this->content, $this->getLineNumber($node))
                ));
            }
        }
    }

    /**
     * Helper methods
     */
    protected function isMethodCall(Node $node, string $class, string $method): bool
    {
        return $node instanceof Node\Expr\StaticCall &&
               $node->class instanceof Node\Name &&
               $node->class->toString() === $class &&
               $node->name instanceof Node\Identifier &&
               $node->name->name === $method;
    }

    protected function containsVariable($arg): bool
    {
        return $arg && (
            $arg->value instanceof Node\Expr\Variable ||
            $arg->value instanceof Node\Expr\BinaryOp\Concat
        );
    }

    protected function containsUserInput(Node $node): bool
    {
        // Simplified check - in practice, this would be more sophisticated
        $nodeString = $this->nodeToString($node);
        return preg_match('/\$_GET|\$_POST|\$_REQUEST|\$_COOKIE|request\(\)|input\(\)/', $nodeString);
    }

    protected function getFunctionName(Node $node): ?string
    {
        if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
            return $node->name->toString();
        }
        return null;
    }

    protected function extendsModel(Node $node): bool
    {
        if ($node->extends && $node->extends instanceof Node\Name) {
            return in_array($node->extends->toString(), ['Model', 'Authenticatable']);
        }
        return false;
    }

    protected function hasFillableOrGuarded(Node $node): bool
    {
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                foreach ($stmt->props as $prop) {
                    if (in_array($prop->name->name, ['fillable', 'guarded'])) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    protected function nodeToString(Node $node): string
    {
        // Simplified node to string conversion
        return get_class($node);
    }
}
