<?php

namespace Rafaelogic\CodeSnoutr\Services\AI;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Models\Setting;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

class AutoFixService
{
    protected $aiService;
    protected $backupDisk;

    public function __construct(AiAssistantService $aiService)
    {
        $this->aiService = $aiService;
        $this->backupDisk = config('codesnoutr.ai.auto_fix.backup_disk', 'local');
    }

    /**
     * Generate an AI-powered fix for an issue
     */
    public function generateFix(Issue $issue): ?array
    {
        if (!$this->aiService->isAvailable()) {
            return null;
        }

        try {
            $prompt = $this->buildAutoFixPrompt($issue);
            $response = $this->aiService->askAI($prompt, 800);

            if (!$response) {
                return null;
            }

            return $this->parseFixResponse($response);

        } catch (\Exception $e) {
            Log::error('AI fix generation failed for issue ' . $issue->id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Apply an automatic fix to a file
     */
    public function applyFix(Issue $issue, array $fixData): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'backup_path' => null,
            'preview' => null
        ];

        try {
            // Get fix data - handle both JSON and plain text formats
            $fixData = $this->parseAiFixData($issue->ai_fix);
            
            // Validate fix data
            if (!$this->validateFixData($fixData)) {
                $result['message'] = 'Invalid fix data';
                return $result;
            }

            // Check if file exists and is writable
            if (!File::exists($issue->file_path) || !File::isWritable($issue->file_path)) {
                $result['message'] = 'File does not exist or is not writable';
                return $result;
            }

            // Create backup
            $backupPath = $this->createBackup($issue->file_path);
            if (!$backupPath) {
                $result['message'] = 'Failed to create backup';
                return $result;
            }
            $result['backup_path'] = $backupPath;

            // Read current file content
            $originalContent = File::get($issue->file_path);
            $lines = explode("\n", $originalContent);

            // Pre-validate the AI fix BEFORE applying
            if (!$this->validateAiFixData($fixData, $issue)) {
                $result['message'] = 'AI fix validation failed - fix appears incomplete or invalid';
                return $result;
            }
            
            // CRITICAL: Check if we're trying to insert class-level code inside an array
            if ($this->isInsertingClassCodeInArray($lines, $issue->line_number - 1, $fixData['code'])) {
                Log::warning('❌ AI trying to insert class-level code inside array - SKIPPING', [
                    'issue_id' => $issue->id,
                    'target_line' => $issue->line_number,
                    'line_content' => $lines[$issue->line_number - 1] ?? '',
                    'generated_code' => $fixData['code'],
                ]);
                $result['message'] = 'Cannot insert class-level code (const/property/method) inside an array';
                return $result;
            }

            // Apply the fix
            $modifiedContent = $this->applyFixToContent($lines, $issue, $fixData);

            if ($modifiedContent === null) {
                $result['message'] = 'Failed to apply fix to content';
                return $result;
            }

            // Validate the modified content
            if (!$this->validateModifiedContent($modifiedContent, $issue->file_path)) {
                $result['message'] = 'Modified content failed validation';
                return $result;
            }
            
            // Write the modified content
            File::put($issue->file_path, $modifiedContent);

            // Mark issue as fixed
            $issue->update([
                'fixed' => true,
                'fixed_at' => now(),
                'fix_method' => 'ai_auto',
                'ai_fix' => $fixData['code'] ?? null,
                'ai_explanation' => $fixData['explanation'] ?? null,
                'ai_confidence' => $fixData['confidence'] ?? 0,
                'metadata' => array_merge($issue->metadata ?? [], [
                    'auto_fix_applied' => true,
                    'backup_path' => $backupPath,
                    'fix_timestamp' => now()->toISOString()
                ])
            ]);

            $result['success'] = true;
            $result['message'] = 'Fix applied successfully';
            $result['preview'] = $this->generatePreview($originalContent, $modifiedContent);

            Log::info('AI fix applied successfully', [
                'issue_id' => $issue->id,
                'file_path' => $issue->file_path,
                'backup_path' => $backupPath
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('AI fix application failed', [
                'issue_id' => $issue->id,
                'error' => $e->getMessage()
            ]);

            $result['message'] = 'Failed to apply fix: ' . $e->getMessage();
            return $result;
        }
    }

    /**
     * Preview what a fix would look like without applying it
     */
    public function previewFix(Issue $issue, array $fixData): ?array
    {
        try {
            if (!File::exists($issue->file_path)) {
                return null;
            }

            $originalContent = File::get($issue->file_path);
            $lines = explode("\n", $originalContent);

            $modifiedContent = $this->applyFixToContent($lines, $issue, $fixData);

            if ($modifiedContent === null) {
                return null;
            }

            return $this->generatePreview($originalContent, $modifiedContent);

        } catch (\Exception $e) {
            Log::error('Fix preview failed', [
                'issue_id' => $issue->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Restore a file from backup
     */
    public function restoreFromBackup(Issue $issue): bool
    {
        try {
            $backupPath = $issue->metadata['backup_path'] ?? null;

            if (!$backupPath || !Storage::disk($this->backupDisk)->exists($backupPath)) {
                return false;
            }

            $backupContent = Storage::disk($this->backupDisk)->get($backupPath);
            File::put($issue->file_path, $backupContent);

            // Update issue status
            $issue->update([
                'fixed' => false,
                'fixed_at' => null,
                'fix_method' => null,
                'metadata' => array_merge($issue->metadata ?? [], [
                    'restored_from_backup' => true,
                    'restore_timestamp' => now()->toISOString()
                ])
            ]);

            Log::info('File restored from backup', [
                'issue_id' => $issue->id,
                'file_path' => $issue->file_path,
                'backup_path' => $backupPath
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Backup restoration failed', [
                'issue_id' => $issue->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Build the prompt for AI fix generation
     */
    protected function buildAutoFixPrompt(Issue $issue): string
    {
        $fileContent = File::exists($issue->file_path) ? File::get($issue->file_path) : '';
        $lines = explode("\n", $fileContent);
        $contextLines = $this->getContextLines($fileContent, $issue->line_number, 15);
        $fileStructure = $this->analyzeFileStructure($fileContent, $issue->line_number);

        return "Generate an automatic code fix for this issue.\n\n" .
               "ISSUE DETAILS:\n" .
               "File: {$issue->file_path}\n" .
               "Line: {$issue->line_number}\n" .
               "Category: {$issue->category}\n" .
               "Severity: {$issue->severity}\n" .
               "Issue: {$issue->description}\n" .
               "Rule: {$issue->rule_name}\n\n" .
               "FILE STRUCTURE ANALYSIS:\n" .
               "Target line context: {$fileStructure['context']}\n" .
               "Inside class: {$fileStructure['inside_class']}\n" .
               "Class name: {$fileStructure['class_name']}\n" .
               "Current indentation: '{$fileStructure['indentation']}'\n" .
               "Laravel Model: " . ($fileStructure['laravel_model'] ?? 'false') . "\n" .
               "Target line content: " . trim($lines[$issue->line_number - 1] ?? 'N/A') . "\n\n" .
               "CONTEXT CODE:\n```php\n{$contextLines}\n```\n\n" .
               "CRITICAL INSTRUCTIONS:\n" .
               "1. You must respond ONLY with valid JSON - no markdown, no explanations outside JSON\n" .
               "2. Study the file structure analysis to understand WHERE your code belongs\n" .
               "3. If inside_class is 'true', ensure properties/methods go INSIDE the class with proper indentation\n" .
               "4. For docblocks, use 'insert' type and place BEFORE the target element\n" .
               "5. Match the existing indentation pattern exactly\n" .
               "6. Never place class properties/methods outside the class body\n" .
               "7. CRITICAL: For Laravel Model properties use 'public \$timestamps = true;' or just '\$timestamps = true;' (NEVER 'protected')\n" .
               "8. METHODS: If you're fixing a method issue, provide the COMPLETE method including signature, body, and closing brace\n" .
               "9. NEVER provide just a return statement - always include the complete method structure\n" .
               "10. When replacing method code, include: visibility + function + name + parameters + opening brace + body + closing brace\n\n" .
               "IMPORTANT: For docblocks with multiple lines, use actual newlines in JSON, NOT \\n escape sequences\n\n" .
               "REQUIRED JSON FORMAT (respond with ONLY this JSON, nothing else):\n" .
               "{\n" .
               "  \"code\": \"exact PHP code with proper indentation\",\n" .
               "  \"explanation\": \"brief explanation\",\n" .
               "  \"confidence\": 0.85,\n" .
               "  \"safe_to_automate\": true,\n" .
               "  \"affected_lines\": [" . $issue->line_number . "],\n" .
               "  \"type\": \"replace\"\n" .
               "}\n\n" .
               "PLACEMENT RULES:\n" .
               "- ANALYZE target_line_content first to understand what you're modifying\n" .
               "- CLASS DOCBLOCKS: If target line is 'class MyClass', use 'insert' type to place docblock BEFORE the class\n" .
               "- CLASS MEMBERS: If target line is INSIDE a class, ensure proper class member indentation (4 spaces from class level)\n" .
               "- METHOD DOCBLOCKS: Use 'insert' type, place BEFORE the method declaration with same indentation\n" .
               "- PROPERTY FIXES: Use 'replace' type for existing properties, 'insert' for new properties after similar properties\n" .
               "- METHOD FIXES: For complete method replacement, use 'replace' with full method structure (signature + body + closing brace)\n" .
               "- INDENTATION: Match existing file indentation pattern exactly (spaces vs tabs, indent size)\n" .
               "- CONTEXT AWARENESS: Consider surrounding code structure (namespace, imports, existing members)\n" .
               "- NEVER place class members outside class body or replace class declaration with member code\n" .
               "- VALIDATION: Ensure generated code maintains proper PHP syntax and Laravel conventions\n" .
               "\n" .
               "CODE FORMATTING RULES:\n" .
               "- LINE LENGTH: Break lines longer than 120 characters using proper formatting\n" .
               "- METHOD CHAINS: Break each method call to new line with proper indentation\n" .
               "- LONG CONDITIONALS: Break ternary operators and long conditions across multiple lines\n" .
               "- SQL QUERIES: For whereRaw, where clauses - break at logical points (AND, OR, parameters)\n" .
               "- ARRAY PARAMETERS: When array has multiple elements, format one per line\n" .
               "- INDENTATION: Use 4 spaces for each level, align method chains with initial call\n" .
               "- PRESERVE FUNCTIONALITY: NEVER change method logic, only format long lines\n" .
               "- PRESERVE RETURN STATEMENTS: If original has 'return', keep it in the fix\n" .
               "- PRESERVE QUERY METHODS: Don't change where() to with(), whereHas() to has(), etc.\n" .
               "- EXAMPLE Long Line Fix:\n" .
               "  BEFORE: return \$condition ? \$this->builder->whereRaw('(table.col1 LIKE ? OR table.col2 LIKE ?)', ['%'.\$val.'%', '%'.\$val.'%']) : \$this->builder;\n" .
               "  AFTER: return isset(\$condition)\n" .
               "      ? \$this->builder->whereRaw(\n" .
               "          '(table.col1 LIKE ? OR table.col2 LIKE ?)',\n" .
               "          ['%'.\$val.'%', '%'.\$val.'%']\n" .
               "      )\n" .
               "      : \$this->builder;\n" .
               "\n" .
               "CRITICAL: ONLY FORMAT - NEVER CHANGE LOGIC!\n" .
               "- If fixing line length: break long lines but keep exact same method calls\n" .
               "- If original has 'return \$this->relation()->where()->get()', keep all parts\n" .
               "- NEVER change where() to with(), they serve different purposes\n" .
               "- NEVER remove return statements from methods that return values\n" .
               "\n" .
               "LARAVEL-SPECIFIC RULES:\n" .
               "- IF Laravel Model is 'true': Follow Laravel Eloquent Model conventions\n" .
               "- Model Properties: \$fillable, \$guarded, \$hidden, \$casts, \$dates should be PROTECTED (not public)\n" .
               "- CORRECT: 'protected \$fillable = [];' or 'protected \$timestamps = false;'\n" .
               "- WRONG: 'public \$fillable = [];' (breaks encapsulation)\n" .
               "- Special Properties: \$table, \$primaryKey, \$keyType can be public or protected\n" .
               "- Relationships: Always public methods (public function user())\n" .
               "- Accessors/Mutators: Follow Laravel naming (getUserNameAttribute, setUserNameAttribute)\n" .
               "- Scopes: Always public methods prefixed with 'scope' (public function scopeActive())\n" .
               "- Use Laravel conventions: camelCase for methods, snake_case for database columns\n" .
               "- Add proper type hints and return types for Laravel 9+ compatibility\n" .
               "- For missing docblocks: Include @property annotations for dynamic properties\n" .
               "- QUERY BUILDER: Break long whereRaw, where clauses across multiple lines for readability\n" .
               "- CRITICAL QUERY METHODS: NEVER change where() to with() - they serve different purposes!\n" .
               "  * where() filters database records by column values\n" .
               "  * with() eager loads relationships\n" .
               "  * whereHas() filters by relationship existence\n" .
               "  * has() just checks relationship existence\n" .
               "- PRESERVE RETURN CHAINS: Methods like ->where()->get() must keep 'return' statement\n" .
               "- LINE LENGTH ONLY: For query builder issues, only break long lines, never change method calls";
    }

    /**
     * Parse the AI response for fix data
     */
    protected function parseFixResponse($response): ?array
    {
        if (is_string($response)) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $response = $decoded;
            } else {
                return null;
            }
        }

        if (!is_array($response)) {
            return null;
        }

        return [
            'code' => $response['code'] ?? null,
            'explanation' => $response['explanation'] ?? null,
            'confidence' => (float) ($response['confidence'] ?? 0),
            'safe_to_automate' => (bool) ($response['safe_to_automate'] ?? false),
            'affected_lines' => $response['affected_lines'] ?? [],
            'type' => $response['type'] ?? 'replace'
        ];
    }

    /**
     * Parse AI fix data using robust JSON parsing
     */
    protected function parseAiFixData(string $aiFixData): array
    {
        // Debug logging
        Log::info('Parsing AI fix data', [
            'data_preview' => substr($aiFixData, 0, 300),
            'data_length' => strlen($aiFixData)
        ]);

        // Clean up potential extra content around JSON
        $cleanedData = $this->extractJsonFromResponse($aiFixData);

        // Try robust JSON parsing with JsonLint
        $jsonData = $this->parseJsonWithLint($cleanedData);
        
        if ($jsonData !== null && is_array($jsonData) && isset($jsonData['code'])) {
            Log::info('Successfully parsed as JSON format', [
                'code_preview' => substr($jsonData['code'], 0, 200),
                'explanation_preview' => substr($jsonData['explanation'] ?? '', 0, 200),
                'confidence' => $jsonData['confidence'] ?? 'not set',
                'type' => $jsonData['type'] ?? 'not set'
            ]);
            return $jsonData;
        }

        // If JSON parsing fails, log error and return fallback
        Log::warning('Failed to parse AI response as JSON - please generate a new AI fix', [
            'json_error' => 'Could not parse with JsonLint library',
            'data_preview' => substr($aiFixData, 0, 500)
        ]);

        // Return fallback structure
        return [
            'code' => '',
            'explanation' => 'Failed to parse AI response. Please generate a new AI fix to replace the old format.',
            'confidence' => 0.0,
            'safe_to_automate' => false,
            'type' => 'replace',
            'affected_lines' => []
        ];
    }

    /**
     * Parse JSON using JsonLint library for robust parsing
     */
    protected function parseJsonWithLint(string $json): ?array
    {
        // Check if JsonLint classes are available
        if (!class_exists('Seld\JsonLint\JsonParser')) {
            Log::warning('JsonLint not available, falling back to native JSON parsing');
            return $this->fallbackJsonParse($json);
        }

        try {
            $parser = new JsonParser();
            
            // First try: direct parsing
            try {
                $result = $parser->parse($json, JsonParser::PARSE_TO_ASSOC);
                Log::debug('JsonLint: Direct parsing successful');
                return $result;
            } catch (ParsingException $e) {
                Log::debug('JsonLint: Direct parsing failed', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine() ?? 'unknown'
                ]);
            }
            
            // Second try: after basic cleaning
            $cleanedJson = $this->cleanJsonControlCharacters($json);
            try {
                $result = $parser->parse($cleanedJson, JsonParser::PARSE_TO_ASSOC);
                Log::debug('JsonLint: Parsing successful after cleaning');
                return $result;
            } catch (ParsingException $e) {
                Log::debug('JsonLint: Parsing failed after cleaning', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine() ?? 'unknown'
                ]);
            }
            
            // Third try: aggressive cleaning
            $aggressiveClean = $this->aggressiveJsonClean($cleanedJson);
            try {
                $result = $parser->parse($aggressiveClean, JsonParser::PARSE_TO_ASSOC);
                Log::debug('JsonLint: Parsing successful after aggressive cleaning');
                return $result;
            } catch (ParsingException $e) {
                Log::warning('JsonLint: All parsing attempts failed', [
                    'final_error' => $e->getMessage(),
                    'line' => $e->getLine() ?? 'unknown',
                    'json_preview' => substr($aggressiveClean, 0, 200)
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('JsonLint: Unexpected error during parsing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return null;
    }

    /**
     * Fallback JSON parsing when JsonLint is not available
     */
    protected function fallbackJsonParse(string $json): ?array
    {
        // First try: direct parsing
        $result = json_decode($json, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            Log::debug('Fallback: Direct JSON parsing successful');
            return $result;
        }

        // Second try: after basic cleaning
        $cleanedJson = $this->cleanJsonControlCharacters($json);
        $result = json_decode($cleanedJson, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            Log::debug('Fallback: JSON parsing successful after cleaning');
            return $result;
        }

        // Third try: aggressive cleaning
        $aggressiveClean = $this->aggressiveJsonClean($cleanedJson);
        $result = json_decode($aggressiveClean, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            Log::debug('Fallback: JSON parsing successful after aggressive cleaning');
            return $result;
        }

        Log::warning('Fallback: All JSON parsing attempts failed', [
            'last_error' => json_last_error_msg(),
            'json_preview' => substr($aggressiveClean, 0, 200)
        ]);

        return null;
    }

    /**
     * Extract JSON from AI response that might have extra content
     */
    protected function extractJsonFromResponse(string $response): string
    {
        // Remove any leading/trailing whitespace and BOM  
        $response = trim($response);
        $response = preg_replace('/^\xEF\xBB\xBF/', '', $response);

        // First, try to find JSON block markers (if AI wrapped JSON in ```json blocks)
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $response, $matches)) {
            Log::debug('Found JSON in code block');
            return $matches[1];
        }
        
        // Look for JSON object that starts with { and contains expected fields
        $openBrace = strpos($response, '{');
        if ($openBrace === false) {
            Log::debug('No opening brace found in response');
            return $response;
        }

        // Find the matching closing brace by counting braces
        $braceCount = 0;
        $jsonEnd = null;
        
        for ($i = $openBrace; $i < strlen($response); $i++) {
            if ($response[$i] === '{') {
                $braceCount++;
            } elseif ($response[$i] === '}') {
                $braceCount--;
                if ($braceCount === 0) {
                    $jsonEnd = $i;
                    break;
                }
            }
        }

        if ($jsonEnd !== null) {
            $jsonPart = substr($response, $openBrace, $jsonEnd - $openBrace + 1);
            Log::debug('Extracted JSON from response', [
                'length' => strlen($jsonPart),
                'has_code_field' => str_contains($jsonPart, '"code"'),
                'has_explanation_field' => str_contains($jsonPart, '"explanation"')
            ]);
            return $jsonPart;
        }

        Log::debug('Could not find valid JSON structure, returning original response');
        return $response;
    }

    /**
     * Clean control characters from JSON that break parsing
     */
    protected function cleanJsonControlCharacters(string $json): string
    {
        // Remove byte order mark (BOM) if present
        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);
        
        // Log the raw JSON for debugging
        Log::debug('Cleaning JSON control characters', [
            'original_length' => strlen($json),
            'contains_backslashes' => substr_count($json, '\\'),
            'raw_preview' => bin2hex(substr($json, 0, 100)),
            'contains_docblock' => str_contains($json, '/**'),
            'contains_properties' => str_contains($json, '@property')
        ]);
        
        // Remove various control characters that break JSON parsing
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $json);
        
        // Special handling for docblocks - often the source of control character issues
        if (str_contains($json, '/**')) {
            Log::debug('Processing docblock JSON', [
                'has_escaped_newlines' => str_contains($json, '\\n'),
                'has_double_backslashes' => str_contains($json, '\\\\'),
                'docblock_preview' => substr($json, strpos($json, '/**'), 100)
            ]);
            
            // Fix the most common issue: AI generating \\n instead of actual newlines in docblocks
            $json = str_replace('\\n', "\n", $json);  // Fix literal \n in docblocks
            $json = str_replace('\n ', "\n * ", $json); // Fix broken docblock line continuation
            
            // Fix multiple backslashes in namespaces that often accompany docblock issues
            $json = preg_replace('/\\\\{3,}/', '\\\\', $json); // Reduce triple+ backslashes to double
            $json = str_replace('\\\\\\\\', '\\\\', $json);    // Fix quadruple backslashes
        }
        
        // Special handling for docblocks - often the source of control character issues
        if (str_contains($json, '/**') && str_contains($json, '@property')) {
            // Fix common docblock issues that cause control character errors
            $json = str_replace('\\n', "\n", $json);  // Fix literal \n in docblocks
            $json = str_replace('\n ', "\n * ", $json); // Fix broken docblock line continuation
            $json = preg_replace('/\\+/', '\\', $json); // Reduce multiple backslashes
        }
        
        // Handle the specific issue with docblock backslashes in App\\Models
        // This is the most common cause of "Control character error"
        $json = str_replace('\\\\\\\\', '\\\\', $json); // Fix quadruple backslashes (\\\\\\\\  → \\\\)
        $json = str_replace('\\\\\\', '\\', $json);     // Fix triple backslashes   (\\\\\\   → \\)
        
        // Fix common escaping issues
        $json = str_replace('\\n', "\n", $json);     // Fix escaped newlines
        $json = str_replace('\\t', "\t", $json);     // Fix escaped tabs
        $json = str_replace('\\r', "\r", $json);     // Fix escaped carriage returns
        
                // Try to decode and re-encode to normalize\n        $decoded = json_decode($json, true);\n        if ($decoded !== null) {\n            // Successfully parsed - re-encode cleanly\n            $result = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);\n            Log::debug('JSON successfully cleaned and re-encoded', [\n                'cleaned_length' => strlen($result)\n            ]);\n            return $result;\n        } else {\n            // Log what's preventing JSON parsing\n            Log::debug('JSON parsing failed after initial cleaning', [\n                'json_error' => json_last_error_msg(),\n                'first_100_chars' => substr($json, 0, 100),\n                'contains_literal_backslash_n' => str_contains($json, '\\\\n'),\n                'contains_actual_newlines' => str_contains($json, \"\\n\")\n            ]);\n        }
        
        // If still failing, try character-by-character cleaning
        $cleanJson = '';
        for ($i = 0; $i < strlen($json); $i++) {
            $char = $json[$i];
            $ord = ord($char);
            
            // Keep printable ASCII, newlines, tabs, and basic Unicode
            if (($ord >= 32 && $ord <= 126) || $ord === 10 || $ord === 13 || $ord === 9 || $ord > 127) {
                $cleanJson .= $char;
            }
        }
        
        // Final attempt to fix any remaining issues
        $cleanJson = mb_convert_encoding($cleanJson, 'UTF-8', 'UTF-8');
        
        // Try the aggressive cleaner on the character-cleaned JSON
        $cleanJson = $this->aggressiveJsonClean($cleanJson);
        
        Log::warning('Had to use aggressive JSON cleaning', [
            'original_length' => strlen($json),
            'cleaned_length' => strlen($cleanJson),
            'cleaned_preview' => substr($cleanJson, 0, 200)
        ]);
        
        return $cleanJson;
    }

    /**
     * Aggressive JSON cleaning for problematic responses
     */
    protected function aggressiveJsonClean(string $json): string
    {
        // Remove any invisible characters and normalize whitespace
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $json);
        
        // Priority fix: Handle docblock newline issues that cause most control character errors
        if (str_contains($json, '/**') && str_contains($json, '\\n')) {
            Log::debug('Aggressive cleaning: Fixing docblock newlines');
            $json = str_replace('\\n', "\n", $json);  // Convert literal \n to actual newlines
            $json = str_replace('\n     *', "\n     *", $json); // Fix docblock formatting
        }
        
        // Fix common namespace issues in docblocks
        $json = str_replace('\\\\Carbon\\\\Carbon', '\\Carbon\\Carbon', $json);
        $json = str_replace('\\Carbon\\Carbon', '\\\\Carbon\\\\Carbon', $json); // Re-escape for JSON
        
        // Fix Illuminate\\Support\\Carbon specifically
        $json = str_replace('\\\\Illuminate\\\\Support\\\\Carbon', '\\Illuminate\\Support\\Carbon', $json);
        $json = str_replace('\\Illuminate\\Support\\Carbon', '\\\\Illuminate\\\\Support\\\\Carbon', $json);
        
        // Fix other common Laravel namespace issues
        $json = str_replace('\\\\Illuminate\\\\Database\\\\', '\\Illuminate\\Database\\', $json);
        $json = str_replace('\\Illuminate\\Database\\', '\\\\Illuminate\\\\Database\\\\', $json);
        $json = str_replace('\\\\Illuminate\\\\Http\\\\', '\\Illuminate\\Http\\', $json);
        $json = str_replace('\\Illuminate\\Http\\', '\\\\Illuminate\\\\Http\\\\', $json);
        
        // Fix common JSON formatting issues
        $json = str_replace(['\n', '\r', '\t'], ["\n", "\r", "\t"], $json);
        
        // Try to fix broken escape sequences
        $json = preg_replace('/\\\\{3,}/', '\\\\', $json); // Fix triple+ backslashes
        
        // Remove any trailing commas before closing braces/brackets
        $json = preg_replace('/,(\s*[}\]])/', '$1', $json);
        
        // Try one more time to parse after aggressive cleaning
        $testDecode = json_decode($json, true);
        if ($testDecode !== null) {
            // Success! Re-encode cleanly
            return json_encode($testDecode, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        
        return $json;
    }

    /**
     * Parse legacy Markdown format AI fix data for backward compatibility
     */
    protected function parseLegacyMarkdownFormat(string $content): ?array
    {
        $code = '';
        $explanation = '';
        $confidence = 0.75;

        // Extract explanation from **EXPLANATION:** section
        if (preg_match('/\*\*EXPLANATION:\*\*\s*(.*?)(?:\*\*FIX:\*\*|$)/s', $content, $matches)) {
            $explanation = trim($matches[1]);
        }

        // Extract code from **FIX:** section
        if (preg_match('/\*\*FIX:\*\*\s*(.*?)(?:\*\*CONSIDERATIONS:\*\*|$)/s', $content, $matches)) {
            $fixContent = trim($matches[1]);
            
            // Remove explanatory text and extract PHP code
            $code = $this->extractPhpCodeFromLegacyFix($fixContent);
            
            if (empty($code)) {
                return null;
            }
        } else {
            return null;
        }

        return [
            'code' => $code,
            'explanation' => $explanation,
            'confidence' => $confidence,
            'safe_to_automate' => true,
            'type' => 'replace',
            'affected_lines' => []
        ];
    }

    /**
     * Extract PHP code from legacy fix content
     */
    protected function extractPhpCodeFromLegacyFix(string $content): string
    {
        // Remove leading explanatory text
        $lines = explode("\n", $content);
        $codeLines = [];
        $foundCode = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            // Skip empty lines
            if (empty($trimmed)) {
                continue;
            }
            
            // Skip explanatory lines
            if (str_starts_with($trimmed, 'To ') || 
                str_starts_with($trimmed, 'Here') ||
                str_starts_with($trimmed, 'You should') ||
                str_contains($trimmed, 'example') ||
                str_starts_with($trimmed, '```php') ||
                str_starts_with($trimmed, '```')) {
                
                // But mark that we're now in the code section
                if (str_contains($trimmed, '```php') || str_contains($trimmed, 'Route::')) {
                    $foundCode = true;
                }
                continue;
            }
            
            // Look for PHP code patterns
            if ($foundCode || 
                str_contains($trimmed, 'Route::') || 
                str_contains($trimmed, '->') ||
                str_contains($trimmed, 'function') ||
                str_contains($trimmed, 'class ') ||
                str_starts_with($trimmed, '<?php')) {
                $codeLines[] = $trimmed;
                $foundCode = true;
            }
        }

        return implode("\n", $codeLines);
    }

    /**
     * Validate fix data
     */
    protected function validateFixData(array $fixData): bool
    {
        return isset($fixData['code']) && 
               isset($fixData['type']) && 
               isset($fixData['confidence']) &&
               $fixData['confidence'] >= 0.3 && // Minimum confidence threshold
               in_array($fixData['type'], ['replace', 'insert', 'delete']) &&
               !empty(trim($fixData['code'])); // Ensure code is not empty
    }

    /**
     * Apply fix to file content
     */
    protected function applyFixToContent(array $lines, Issue $issue, array $fixData): ?string
    {
        try {
            $targetLine = $issue->line_number - 1; // Convert to 0-based index
            $affectedLines = $fixData['affected_lines'] ?? [$issue->line_number];

            Log::info('Applying fix to content', [
                'issue_id' => $issue->id,
                'file_path' => $issue->file_path,
                'fix_type' => $fixData['type'] ?? 'unknown',
                'target_line' => $targetLine + 1,
                'affected_lines' => $affectedLines,
                'fix_code_preview' => substr($fixData['code'] ?? '', 0, 200) . (strlen($fixData['code'] ?? '') > 200 ? '...' : ''),
                'total_file_lines' => count($lines)
            ]);

            switch ($fixData['type']) {
                case 'replace':
                    return $this->applyReplacement($lines, $targetLine, $fixData['code'], $affectedLines);

                case 'insert':
                    return $this->applyInsertion($lines, $targetLine, $fixData['code']);

                case 'delete':
                    return $this->applyDeletion($lines, $affectedLines);

                default:
                    Log::error('Unknown fix type', ['type' => $fixData['type'] ?? 'null']);
                    return null;
            }

        } catch (\Exception $e) {
            Log::error('Content modification failed', [
                'issue_id' => $issue->id,
                'file_path' => $issue->file_path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Apply a replacement fix
     */
    protected function applyReplacement(array $lines, int $targetLine, string $newCode, array $affectedLines): string
    {
        // Convert affected lines to 0-based indices
        $affectedIndices = array_map(fn($line) => $line - 1, $affectedLines);
        sort($affectedIndices);

        // If no affected lines, fall back to target line (already 0-based)
        if (empty($affectedIndices)) {
            $affectedIndices = [$targetLine];
        }

        // Determine proper indentation context
        $originalIndent = '';
        if (isset($lines[$targetLine])) {
            preg_match('/^(\s*)/', $lines[$targetLine], $matches);
            $originalIndent = $matches[1] ?? '';
        }
        
        // Check if we're trying to replace a class declaration line
        $isClassDeclarationLine = isset($lines[$targetLine]) && 
            preg_match('/^\s*(?:abstract\s+|final\s+)?class\s+/', trim($lines[$targetLine]));
        
        // Handle class docblocks - they should go BEFORE the class declaration
        if ($this->isClassDocblock($newCode)) {
            if ($isClassDeclarationLine) {
                return $this->insertClassDocblock($lines, $targetLine, $newCode);
            }
            // If not on class declaration line, treat as regular replacement with proper indentation
            preg_match('/^(\s*)/', $lines[$targetLine] ?? '', $matches);
            $originalIndent = $matches[1] ?? '';
        }
        // Handle method/property docblocks - they should go BEFORE the method/property
        else if ($this->isMethodDocblock($newCode)) {
            return $this->insertMethodDocblock($lines, $targetLine, $newCode);
        }
        // For class properties/methods, ensure we use class-level indentation
        else if ($this->isClassMemberCode($newCode)) {
            if ($isClassDeclarationLine) {
                // If we're replacing the class line itself, we need to place the code AFTER the class opening brace
                return $this->insertAfterClassDeclaration($lines, $targetLine, $newCode);
            } else {
                // Handle complete method implementations specially
                if ($this->isCompleteMethodImplementation($newCode)) {
                    return $this->replaceCompleteMethod($lines, $targetLine, $newCode, $affectedLines);
                }
                
                $classIndent = $this->findClassIndentation($lines, $targetLine);
                if ($classIndent !== null) {
                    $originalIndent = $classIndent;
                }
            }
        }

        // Apply proper indentation to new code
        $newCode = $this->applyIndentation($newCode, $originalIndent);

        // Replace the lines
        $modifiedLines = $lines;
        
        // Remove affected lines from the end to the beginning to maintain indices
        $reversedIndices = array_reverse($affectedIndices);
        foreach ($reversedIndices as $index) {
            if (isset($modifiedLines[$index])) {
                unset($modifiedLines[$index]);
            }
        }

        // Insert new code at the first affected line position
        $firstIndex = min($affectedIndices);
        $beforeLines = array_slice($modifiedLines, 0, $firstIndex, true);
        $afterLines = array_slice($modifiedLines, $firstIndex, null, true);

        $result = array_merge($beforeLines, [$newCode], $afterLines);
        return implode("\n", $result);
    }

    /**
     * Apply an insertion fix
     */
    protected function applyInsertion(array $lines, int $targetLine, string $newCode): string
    {
        // Preserve indentation from the target line
        $originalIndent = '';
        if (isset($lines[$targetLine])) {
            preg_match('/^(\s*)/', $lines[$targetLine], $matches);
            $originalIndent = $matches[1] ?? '';
        }

        // For docblocks and other code that should go before the target line
        $insertBefore = false;
        $targetLineContent = isset($lines[$targetLine]) ? trim($lines[$targetLine]) : '';
        
        // If target line is a class declaration and we're inserting a docblock, insert before
        if ((strpos($newCode, '/**') !== false || strpos($newCode, 'docblock') !== false) ||
            (preg_match('/^(?:abstract\\s+|final\\s+)?class\\s+/', $targetLineContent) && strpos($newCode, '/**') !== false)) {
            $insertBefore = true;
        }

        // Apply indentation to new code
        $indentedCode = $originalIndent . ltrim($newCode);

        if ($insertBefore) {
            // Insert before the target line
            $beforeLines = array_slice($lines, 0, $targetLine);
            $afterLines = array_slice($lines, $targetLine);
            $result = array_merge($beforeLines, [$indentedCode], $afterLines);
        } else {
            // Insert after the target line (original behavior)
            $beforeLines = array_slice($lines, 0, $targetLine + 1);
            $afterLines = array_slice($lines, $targetLine + 1);
            $result = array_merge($beforeLines, [$indentedCode], $afterLines);
        }
        return implode("\n", $result);
    }

    /**
     * Apply a deletion fix
     */
    protected function applyDeletion(array $lines, array $affectedLines): string
    {
        $modifiedLines = $lines;
        
        // Convert to 0-based indices and sort in reverse order
        $affectedIndices = array_map(fn($line) => $line - 1, $affectedLines);
        rsort($affectedIndices);

        // Remove lines from end to beginning to maintain indices
        foreach ($affectedIndices as $index) {
            if (isset($modifiedLines[$index])) {
                unset($modifiedLines[$index]);
            }
        }

        return implode("\n", array_values($modifiedLines));
    }

    /**
     * Create a backup of the file
     */
    protected function createBackup(string $filePath): ?string
    {
        try {
            $relativePath = str_replace(base_path() . '/', '', $filePath);
            $backupPath = 'backups/' . date('Y/m/d') . '/' . time() . '_' . str_replace('/', '_', $relativePath);

            $content = File::get($filePath);
            Storage::disk($this->backupDisk)->put($backupPath, $content);

            return $backupPath;

        } catch (\Exception $e) {
            Log::error('Backup creation failed', ['file' => $filePath, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Validate modified content (basic syntax check for PHP files)
     */
    protected function validateModifiedContent(string $content, string $filePath): bool
    {
        // Only validate PHP files
        if (!Str::endsWith($filePath, '.php')) {
            return true;
        }

        // Basic syntax check
        $tempFile = tempnam(sys_get_temp_dir(), 'codesnoutr_validation_');
        file_put_contents($tempFile, $content);

        $output = [];
        $returnCode = 0;
        
        // Try to find PHP binary in common locations
        $phpBinary = $this->findPhpBinary();
        exec("{$phpBinary} -l {$tempFile} 2>&1", $output, $returnCode);

        // Log validation details for debugging
        if ($returnCode !== 0) {
            Log::warning('Content validation failed', [
                'file_path' => $filePath,
                'return_code' => $returnCode,
                'php_binary_used' => $phpBinary,
                'php_lint_output' => implode("\n", $output),
                'content_preview' => substr($content, 0, 500) . (strlen($content) > 500 ? '...' : ''),
                'content_length' => strlen($content)
            ]);
        }

        unlink($tempFile);

        return $returnCode === 0;
    }

    /**
     * Generate a preview of changes
     */
    protected function generatePreview(string $original, string $modified): array
    {
        $originalLines = explode("\n", $original);
        $modifiedLines = explode("\n", $modified);

        $diff = [];
        $maxLines = max(count($originalLines), count($modifiedLines));

        for ($i = 0; $i < $maxLines; $i++) {
            $originalLine = $originalLines[$i] ?? '';
            $modifiedLine = $modifiedLines[$i] ?? '';

            if ($originalLine !== $modifiedLine) {
                $diff[] = [
                    'line' => $i + 1,
                    'type' => 'changed',
                    'original' => $originalLine,
                    'modified' => $modifiedLine
                ];
            }
        }

        return [
            'total_changes' => count($diff),
            'diff' => $diff,
            'original_lines' => count($originalLines),
            'modified_lines' => count($modifiedLines)
        ];
    }

    /**
     * Analyze file structure around the target line
     */
    protected function analyzeFileStructure(string $content, int $lineNumber): array
    {
        $lines = explode("\n", $content);
        $targetIndex = $lineNumber - 1; // Convert to 0-based
        
        $structure = [
            'context' => 'unknown',
            'inside_class' => 'false',
            'class_name' => '',
            'indentation' => '',
            'method_context' => ''
        ];
        
        // Get current line indentation
        if (isset($lines[$targetIndex])) {
            preg_match('/^(\s*)/', $lines[$targetIndex], $matches);
            $structure['indentation'] = $matches[1] ?? '';
        }
        
        // Look backwards to find class context
        $foundClass = false;
        for ($i = $targetIndex; $i >= 0; $i--) {
            $line = trim($lines[$i] ?? '');
            
            // Found class declaration
            if (preg_match('/^(?:abstract\s+|final\s+)?class\s+(\w+)/', $line, $matches)) {
                $structure['inside_class'] = 'true';
                $structure['class_name'] = $matches[1];
                $structure['context'] = 'inside class ' . $matches[1];
                $foundClass = true;
                
                // Now look forward to find the opening brace to confirm we're inside
                for ($j = $i; $j < count($lines) && $j <= $targetIndex + 5; $j++) {
                    if (str_contains($lines[$j] ?? '', '{')) {
                        // We found the opening brace, so target line is definitely inside class
                        break;
                    }
                }
                break;
            }
            
            // Found opening PHP tag or namespace - we're at file level
            if (str_starts_with($line, '<?php') || str_starts_with($line, 'namespace')) {
                if (!$foundClass) {
                    $structure['context'] = 'file level';
                }
                break;
            }
        }
        
                // If inside class, determine if we're in method context
        if ($structure['inside_class'] === 'true') {
            for ($i = $targetIndex; $i >= 0; $i--) {
                $line = trim($lines[$i] ?? '');
                
                if (preg_match('/(?:public|private|protected)\\s+function\\s+(\\w+)/', $line, $matches)) {
                    $structure['method_context'] = $matches[1];
                    $structure['context'] .= ', inside method ' . $matches[1];
                    break;
                }
                
                // Stop at class level
                if (preg_match('/^(?:abstract\\s+|final\\s+)?class\\s+/', $line)) {
                    break;
                }
            }
            
            // Check if this appears to be a Laravel Model class
            if (str_contains(strtolower($structure['class_name']), 'model') || 
                str_contains($structure['context'], 'Model') ||
                // Check for common Laravel model indicators in surrounding lines
                $this->appearsToBelaravelModel($lines, $targetIndex)) {
                $structure['laravel_model'] = 'true';
                $structure['context'] .= ' (Laravel Model - use public properties)';
            }
        }
        
        return $structure;
    }

    /**
     * Check if the class appears to be a Laravel Model
     */
    protected function appearsToBelaravelModel(array $lines, int $targetIndex): bool
    {
        // Look for common Laravel Model indicators
        for ($i = max(0, $targetIndex - 20); $i < min(count($lines), $targetIndex + 20); $i++) {
            $line = $lines[$i] ?? '';
            
            // Check for common Laravel Model patterns
            if (str_contains($line, 'use Illuminate\\Database\\Eloquent\\Model') ||
                str_contains($line, 'extends Model') ||
                str_contains($line, 'use HasFactory') ||
                str_contains($line, '$fillable') ||
                str_contains($line, '$guarded') ||
                str_contains($line, '$timestamps')) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get context lines around a specific line number
     */
    protected function getContextLines(string $content, int $lineNumber, int $contextSize = 5): string
    {
        $lines = explode("\n", $content);
        $startLine = max(0, $lineNumber - $contextSize - 1);
        $endLine = min(count($lines) - 1, $lineNumber + $contextSize - 1);

        $contextLines = [];
        for ($i = $startLine; $i <= $endLine; $i++) {
            $contextLines[] = ($i + 1) . ': ' . ($lines[$i] ?? '');
        }

        return implode("\n", $contextLines);
    }

    /**
     * Check if AI fixes are available
     */
    public function isAutoFixEnabled(): bool
    {
        return Setting::getValue('ai_enabled', false) && $this->aiService->isAvailable();
    }

    /**
     * Get AI fix statistics
     */
    public function getAutoFixStats(): array
    {
        try {
            $totalAutoFixed = Issue::where('fix_method', 'ai_auto')->count();
            $successRate = 0;

            if ($totalAutoFixed > 0) {
                $successful = Issue::where('fix_method', 'ai_auto')
                    ->whereJsonDoesntContain('metadata->restore_timestamp', null)
                    ->count();
                $successRate = ($totalAutoFixed - $successful) / $totalAutoFixed * 100;
            }

            return [
                'total_auto_fixed' => $totalAutoFixed,
                'success_rate' => round($successRate, 2),
                'average_confidence' => Issue::where('fix_method', 'ai_auto')
                    ->whereNotNull('ai_confidence')
                    ->avg('ai_confidence') ?? 0
            ];

        } catch (\Exception $e) {
            return [
                'total_auto_fixed' => 0,
                'success_rate' => 0,
                'average_confidence' => 0
            ];
        }
    }

    /**
     * Find PHP binary in common locations
     */
    protected function findPhpBinary(): string
    {
        // Common PHP CLI binary locations (prioritize CLI, avoid FPM)
        $commonPaths = [
            PHP_BINARY, // Current PHP binary
            '/opt/homebrew/bin/php', // Homebrew on Apple Silicon
            '/usr/local/bin/php', // Homebrew on Intel Mac  
            '/usr/bin/php', // System PHP
            '/Applications/Herd.app/Contents/Resources/bin/php', // Laravel Herd CLI
            'php' // Fallback to PATH
        ];

        foreach ($commonPaths as $path) {
            if (!empty($path) && $this->isValidPhpCli($path)) {
                return escapeshellarg($path);
            }
        }

        // Try to find php in Herd's bin directory but avoid FPM versions
        if (is_dir('/Users/' . get_current_user() . '/Library/Application Support/Herd/bin')) {
            $herdBinDir = '/Users/' . get_current_user() . '/Library/Application Support/Herd/bin';
            $herdFiles = glob($herdBinDir . '/php*');
            
            foreach ($herdFiles as $herdFile) {
                // Skip FPM binaries, only use CLI
                if (!str_contains(basename($herdFile), 'fpm') && $this->isValidPhpCli($herdFile)) {
                    return escapeshellarg($herdFile);
                }
            }
        }

        // If none found, try to use the same PHP that's running this script
        return escapeshellarg(PHP_BINARY);
    }

    /**
     * Check if code is a class member (property or method)
     */
    protected function isClassMemberCode(string $code): bool
    {
        $trimmed = trim($code);
        return preg_match('/^(public|private|protected)\s+/', $trimmed) ||
               str_starts_with($trimmed, 'use ') ||
               preg_match('/^\$\w+\s*=/', $trimmed) || // Property assignment
               $this->isCompleteMethodImplementation($code);
    }

    /**
     * Check if code is a complete method implementation (including body)
     */
    protected function isCompleteMethodImplementation(string $code): bool
    {
        $trimmed = trim($code);
        // Check if it starts with visibility modifier + function and contains opening/closing braces
        return preg_match('/^(public|private|protected)\s+function\s+\w+/', $trimmed) &&
               str_contains($trimmed, '{') &&
               str_contains($trimmed, '}');
    }

    /**
     * Check if code is a class docblock that should go before the class declaration
     */
    protected function isClassDocblock(string $code): bool
    {
        $trimmed = trim($code);
        // Check if it's a docblock that mentions "Class" or has @package
        // Also check if it's a combined docblock + class declaration
        return (str_starts_with($trimmed, '/**') && 
               (str_contains($trimmed, '* Class ') || 
                str_contains($trimmed, '@package') ||
                preg_match('/\*\s*@[a-zA-Z]+\s/', $trimmed))) ||
               $this->isCombinedDocblockAndClass($code);
    }

    /**
     * Check if code contains both docblock and class declaration
     */
    protected function isCombinedDocblockAndClass(string $code): bool
    {
        $trimmed = trim($code);
        return str_starts_with($trimmed, '/**') && 
               str_contains($trimmed, '*/') &&
               preg_match('/\*\/\s*\n?\s*(?:abstract\s+|final\s+)?class\s+/', $trimmed);
    }

    /**
     * Extract only the docblock part from combined docblock+class code
     */
    protected function extractDocblockOnly(string $code): string
    {
        $trimmed = trim($code);
        
        // If it's a combined docblock + class, extract only the docblock
        if ($this->isCombinedDocblockAndClass($trimmed)) {
            if (preg_match('/(\/\*\*.*?\*\/)/s', $trimmed, $matches)) {
                return $matches[1];
            }
        }
        
        // If it's just a docblock, return as-is
        return $code;
    }

    /**
     * Check if code is a method/property docblock
     */
    protected function isMethodDocblock(string $code): bool
    {
        $trimmed = trim($code);
        
        // Must be a docblock
        if (!str_starts_with($trimmed, '/**') || !str_ends_with($trimmed, '*/')) {
            return false;
        }
        
        // If it's a class docblock, it's not a method docblock
        if (str_contains($trimmed, '* Class ') || str_contains($trimmed, '@package')) {
            return false;
        }
        
        // If it's a complete method implementation, it's not just a docblock
        if ($this->isCompleteMethodImplementation($trimmed)) {
            return false;
        }
        
        // Check for method/property docblock indicators
        return str_contains($trimmed, '@param') ||
               str_contains($trimmed, '@return') ||
               str_contains($trimmed, '@var') ||
               preg_match('/\*\s+[A-Z][a-z]/', $trimmed); // Method description starting with capital
    }

    /**
     * Find the proper indentation for class members
     */
    protected function findClassIndentation(array $lines, int $targetLine): ?string
    {
        // Look backwards for class declaration
        for ($i = $targetLine; $i >= 0; $i--) {
            $line = $lines[$i] ?? '';
            
            if (preg_match('/^(\s*)(?:abstract\s+|final\s+)?class\s+/', $line, $matches)) {
                $classIndent = $matches[1];
                
                // Look for existing class members to determine indentation
                for ($j = $i + 1; $j < count($lines); $j++) {
                    $memberLine = $lines[$j] ?? '';
                    
                    // Found a class member (property or method)
                    if (preg_match('/^(\s*)(?:public|private|protected|use)\s+/', $memberLine, $memberMatches)) {
                        return $memberMatches[1]; // Use existing member indentation
                    }
                    
                    // Stop at class closing brace
                    if (preg_match('/^' . preg_quote($classIndent) . '}/', $memberLine)) {
                        break;
                    }
                }
                
                // Fallback: class indent + 4 spaces
                return $classIndent . '    ';
            }
        }
        
        return null;
    }

    /**
     * Insert code after class declaration opening brace
     */
    protected function insertAfterClassDeclaration(array $lines, int $classLineIndex, string $newCode): string
    {
        // Find the opening brace for the class
        $braceLineIndex = null;
        for ($i = $classLineIndex; $i < count($lines) && $i < $classLineIndex + 10; $i++) {
            if (str_contains($lines[$i] ?? '', '{')) {
                $braceLineIndex = $i;
                break;
            }
        }
        
        if ($braceLineIndex === null) {
            // No opening brace found, fall back to regular replacement
            Log::warning('Could not find class opening brace', [
                'class_line_index' => $classLineIndex,
                'class_line_content' => $lines[$classLineIndex] ?? 'not found'
            ]);
            return implode("\n", $lines);
        }
        
        // Get class indentation and add 4 spaces for member indentation
        preg_match('/^(\s*)/', $lines[$classLineIndex], $matches);
        $classIndent = $matches[1] ?? '';
        $memberIndent = $classIndent . '    ';
        
        // Apply proper indentation to the new code
        $indentedCode = $memberIndent . ltrim($newCode);
        
        // Insert the code after the opening brace
        $beforeBrace = array_slice($lines, 0, $braceLineIndex + 1);
        $afterBrace = array_slice($lines, $braceLineIndex + 1);
        
        $result = array_merge($beforeBrace, [$indentedCode], $afterBrace);
        
        Log::info('Inserted code after class declaration', [
            'class_line' => $classLineIndex + 1,
            'brace_line' => $braceLineIndex + 1,
            'member_indent' => strlen($memberIndent),
            'code_preview' => substr($indentedCode, 0, 100)
        ]);
        
        return implode("\n", $result);
    }

    /**
     * Insert class docblock before the class declaration
     */
    protected function insertClassDocblock(array $lines, int $classLineIndex, string $docblock): string
    {
        // Extract only the docblock part (in case AI included class declaration too)
        $pureDocblock = $this->extractDocblockOnly($docblock);
        
        // Get the class line indentation to match it
        preg_match('/^(\s*)/', $lines[$classLineIndex], $matches);
        $classIndent = $matches[1] ?? '';
        
        // Apply the same indentation to the docblock
        $indentedDocblock = $this->applyIndentation($pureDocblock, $classIndent);
        
        // Insert the docblock before the class declaration
        $beforeClass = array_slice($lines, 0, $classLineIndex);
        $classAndAfter = array_slice($lines, $classLineIndex);
        
        $result = array_merge($beforeClass, [$indentedDocblock], $classAndAfter);
        
        Log::info('Inserted class docblock before class declaration', [
            'class_line' => $classLineIndex + 1,
            'class_indent' => strlen($classIndent),
            'docblock_preview' => substr($indentedDocblock, 0, 100),
            'extracted_docblock_only' => $this->isCombinedDocblockAndClass($docblock)
        ]);
        
        return implode("\n", $result);
    }

    /**
     * Insert method docblock before the method declaration
     */
    protected function insertMethodDocblock(array $lines, int $methodLineIndex, string $docblock): string
    {
        // Get the method line indentation to match it
        preg_match('/^(\s*)/', $lines[$methodLineIndex], $matches);
        $methodIndent = $matches[1] ?? '';
        
        // Apply the same indentation to the docblock
        $indentedDocblock = $this->applyIndentation($docblock, $methodIndent);
        
        // Insert the docblock before the method declaration
        $beforeMethod = array_slice($lines, 0, $methodLineIndex);
        $methodAndAfter = array_slice($lines, $methodLineIndex);
        
        $result = array_merge($beforeMethod, [$indentedDocblock], $methodAndAfter);
        
        Log::info('Inserted method docblock before method declaration', [
            'method_line' => $methodLineIndex + 1,
            'method_indent' => strlen($methodIndent),
            'docblock_preview' => substr($indentedDocblock, 0, 100)
        ]);
        
        return implode("\n", $result);
    }

    /**
     * Replace a complete method implementation
     */
    protected function replaceCompleteMethod(array $lines, int $targetLine, string $newMethodCode, array $affectedLines): string
    {
        // Find the method boundaries - look for the opening and closing braces
        $methodStartLine = $targetLine;
        $methodEndLine = $targetLine;
        
        // If affected_lines is provided and has multiple lines, use that range
        if (count($affectedLines) > 1) {
            $affectedIndices = array_map(fn($line) => $line - 1, $affectedLines);
            $methodStartLine = min($affectedIndices);
            $methodEndLine = max($affectedIndices);
        } else {
            // Try to find the complete method by looking for braces
            $braceCount = 0;
            $foundStart = false;
            
            // Look for method start (might be current line or lines above)
            for ($i = $targetLine; $i >= max(0, $targetLine - 10); $i--) {
                $line = $lines[$i] ?? '';
                if (preg_match('/^\s*(public|private|protected)\s+function\s+/', $line)) {
                    $methodStartLine = $i;
                    $foundStart = true;
                    break;
                }
            }
            
            // Look for method end (closing brace)
            if ($foundStart) {
                for ($i = $methodStartLine; $i < count($lines) && $i < $methodStartLine + 50; $i++) {
                    $line = $lines[$i] ?? '';
                    $braceCount += substr_count($line, '{') - substr_count($line, '}');
                    if ($braceCount === 0 && $i > $methodStartLine && str_contains($line, '}')) {
                        $methodEndLine = $i;
                        break;
                    }
                }
            }
        }
        
        // Get proper indentation from the original method
        $methodIndent = '';
        if (isset($lines[$methodStartLine])) {
            preg_match('/^(\s*)/', $lines[$methodStartLine], $matches);
            $methodIndent = $matches[1] ?? '';
        }
        
        // Apply proper indentation to the new method
        $indentedMethod = $this->applyIndentation($newMethodCode, $methodIndent);
        
        // Replace the method lines
        $beforeMethod = array_slice($lines, 0, $methodStartLine);
        $afterMethod = array_slice($lines, $methodEndLine + 1);
        
        $result = array_merge($beforeMethod, [$indentedMethod], $afterMethod);
        
        Log::info('Replaced complete method implementation', [
            'method_start_line' => $methodStartLine + 1,
            'method_end_line' => $methodEndLine + 1,
            'method_indent' => strlen($methodIndent),
            'method_preview' => substr($indentedMethod, 0, 100)
        ]);
        
        return implode("\n", $result);
    }

    /**
     * Apply proper indentation to code
     */
    protected function applyIndentation(string $code, string $baseIndent): string
    {
        $lines = explode("\n", $code);
        $indentedLines = [];
        
        foreach ($lines as $index => $line) {
            if (empty(trim($line))) {
                $indentedLines[] = $line; // Keep empty lines as-is
            } else {
                // For first line, check if it already has proper indentation
                if ($index === 0) {
                    if (str_starts_with($line, $baseIndent)) {
                        $indentedLines[] = $line; // Already properly indented
                    } else {
                        $indentedLines[] = $baseIndent . ltrim($line);
                    }
                } else {
                    // For subsequent lines, apply base indentation if not already indented
                    if (preg_match('/^\s/', $line)) {
                        $indentedLines[] = $line; // Keep existing indentation
                    } else {
                        $indentedLines[] = $baseIndent . $line;
                    }
                }
            }
        }
        
        return implode("\n", $indentedLines);
    }

    /**
     * Check if a PHP binary path is valid CLI binary (not FPM)
     */
    protected function isValidPhpCli(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // For PHP_BINARY constant, it's always valid if it's CLI
        if ($path === PHP_BINARY) {
            return !str_contains($path, 'fpm');
        }

        // Check if file exists and is executable
        if (!is_file($path) || !is_executable($path)) {
            return false;
        }

        // Skip FPM binaries
        if (str_contains(basename($path), 'fpm')) {
            return false;
        }

        // Test if it supports -l flag by running a quick test
        $output = [];
        $returnCode = 0;
        exec(escapeshellarg($path) . ' --help 2>/dev/null', $output, $returnCode);
        
        // Check if help output mentions syntax checking (-l flag)
        $helpText = implode(' ', $output);
        return str_contains($helpText, '-l') && str_contains($helpText, 'syntax');
    }

    /**
     * Validate AI fix data before applying it
     */
    protected function validateAiFixData(array $fixData, Issue $issue): bool
    {
        $code = $fixData['code'] ?? '';
        $type = $fixData['type'] ?? 'replace';

        // Check if code is empty
        if (empty(trim($code))) {
            Log::warning('AI fix validation failed: Empty code', [
                'issue_id' => $issue->id,
                'fix_type' => $type
            ]);
            return false;
        }

        // For replace type, check if we're generating incomplete method code
        if ($type === 'replace') {
            $trimmedCode = trim($code);
            
            // Check if it's just a return statement without method structure
            if (preg_match('/^return\s+/', $trimmedCode) && !str_contains($trimmedCode, 'function')) {
                Log::warning('AI fix validation failed: Incomplete method - just return statement', [
                    'issue_id' => $issue->id,
                    'code_preview' => substr($trimmedCode, 0, 100)
                ]);
                return false;
            }

            // Check for missing return statements in methods that should return values
            if ($this->shouldHaveReturnStatement($issue, $trimmedCode)) {
                Log::warning('AI fix validation failed: Method missing return statement', [
                    'issue_id' => $issue->id,
                    'code_preview' => substr($trimmedCode, 0, 200),
                    'has_return' => str_contains($trimmedCode, 'return'),
                    'has_get_call' => str_contains($trimmedCode, '->get()')
                ]);
                return false;
            }

            // Check for incorrect query builder modifications
            if ($this->hasIncorrectQueryBuilderChanges($issue, $trimmedCode)) {
                Log::warning('AI fix validation failed: Incorrect query builder modifications', [
                    'issue_id' => $issue->id,
                    'code_preview' => substr($trimmedCode, 0, 200)
                ]);
                return false;
            }

            // Laravel-specific validation
            if ($this->isLaravelModelContext($issue)) {
                if (!$this->validateLaravelModelCode($trimmedCode, $issue)) {
                    return false;
                }
            }

            // Validate line length formatting fixes
            if ($this->isLongLineIssue($issue)) {
                if (!$this->validateLineBreakFormatting($trimmedCode, $issue)) {
                    Log::warning('AI fix validation failed: Improper line length formatting', [
                        'issue_id' => $issue->id,
                        'code_preview' => substr($trimmedCode, 0, 200)
                    ]);
                    return false;
                }
            }

            // Check if it contains standalone statements that should be in methods
            $standaloneStatements = [
                '/^(echo|print|var_dump|die|exit)\s+/',
                '/^[a-zA-Z_][a-zA-Z0-9_]*\s*=\s*/', // variable assignments
                '/^(if|while|for|foreach|switch)\s*\(/', // control structures without method wrapper
            ];

            foreach ($standaloneStatements as $pattern) {
                if (preg_match($pattern, $trimmedCode)) {
                    // But allow if it's clearly within a method structure
                    if (!str_contains($trimmedCode, 'function') && !str_contains($trimmedCode, '{')) {
                        Log::warning('AI fix validation failed: Standalone statement without method context', [
                            'issue_id' => $issue->id,
                            'pattern' => $pattern,
                            'code_preview' => substr($trimmedCode, 0, 100)
                        ]);
                        return false;
                    }
                }
            }
        }

        Log::info('AI fix validation passed', [
            'issue_id' => $issue->id,
            'fix_type' => $type,
            'code_length' => strlen($code)
        ]);

        return true;
    }

    /**
     * Check if the issue is in a Laravel Model context
     */
    protected function isLaravelModelContext(Issue $issue): bool
    {
        if (!File::exists($issue->file_path)) {
            return false;
        }

        $content = File::get($issue->file_path);
        
        // Check for Laravel Model indicators
        return str_contains($content, 'use Illuminate\\Database\\Eloquent\\Model') ||
               str_contains($content, 'extends Model') ||
               str_contains($content, 'use HasFactory') ||
               str_contains($content, '$fillable') ||
               str_contains($content, '$guarded') ||
               str_contains($content, '$timestamps');
    }

    /**
     * Validate Laravel Model-specific code patterns
     */
    protected function validateLaravelModelCode(string $code, Issue $issue): bool
    {
        // Check for incorrect public model properties
        $incorrectPublicProperties = [
            'public $fillable',
            'public $guarded', 
            'public $hidden',
            'public $casts',
            'public $dates',
            'public $with',
            'public $withCount',
            'public $appends'
        ];

        foreach ($incorrectPublicProperties as $incorrectPattern) {
            if (str_contains($code, $incorrectPattern)) {
                Log::warning('AI fix validation failed: Incorrect public Laravel Model property', [
                    'issue_id' => $issue->id,
                    'incorrect_pattern' => $incorrectPattern,
                    'code_preview' => substr($code, 0, 100),
                    'suggestion' => 'Use protected instead of public for Laravel Model properties'
                ]);
                return false;
            }
        }

        // Check for missing method visibility in relationships/scopes
        if (preg_match('/function\s+(belongsTo|hasOne|hasMany|belongsToMany|scope\w+)/i', $code)) {
            if (!preg_match('/^(public|protected|private)\s+function/', trim($code))) {
                Log::warning('AI fix validation failed: Missing visibility modifier for Laravel method', [
                    'issue_id' => $issue->id,
                    'code_preview' => substr($code, 0, 100),
                    'suggestion' => 'Laravel relationship and scope methods should be public'
                ]);
                return false;
            }
        }

        // Check for incorrect scope method naming
        if (preg_match('/public\s+function\s+(scope\w+)/i', $code, $matches)) {
            $methodName = $matches[1];
            if (!preg_match('/^scope[A-Z]/', $methodName)) {
                Log::warning('AI fix validation failed: Incorrect Laravel scope method naming', [
                    'issue_id' => $issue->id,
                    'method_name' => $methodName,
                    'code_preview' => substr($code, 0, 100),
                    'suggestion' => 'Scope methods should start with "scope" followed by PascalCase'
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the issue is related to long lines that need breaking
     */
    protected function isLongLineIssue(Issue $issue): bool
    {
        // Check if the issue description mentions line length
        $description = strtolower($issue->description ?? '');
        $ruleName = strtolower($issue->rule_name ?? '');
        
        return str_contains($description, 'line length') ||
               str_contains($description, 'long line') ||
               str_contains($description, 'line too long') ||
               str_contains($description, 'exceeds maximum') ||
               str_contains($ruleName, 'line_length') ||
               str_contains($ruleName, 'max_line_length') ||
               str_contains($ruleName, 'generic.files.linelength') ||
               $this->hasLongLineInContext($issue);
    }

    /**
     * Check if the actual line content is too long
     */
    protected function hasLongLineInContext(Issue $issue): bool
    {
        if (!File::exists($issue->file_path)) {
            return false;
        }

        $content = File::get($issue->file_path);
        $lines = explode("\n", $content);
        $targetLine = $lines[$issue->line_number - 1] ?? '';
        
        // Check if line is longer than 120 characters (common PSR-12 limit)
        return strlen($targetLine) > 120;
    }

    /**
     * Validate that line break formatting is proper
     */
    protected function validateLineBreakFormatting(string $code, Issue $issue): bool
    {
        // Check if the code properly breaks long lines
        $lines = explode("\n", $code);
        
        foreach ($lines as $line) {
            // Allow some tolerance, but flag if still too long
            if (strlen($line) > 140) {
                Log::warning('AI fix still contains long lines', [
                    'issue_id' => $issue->id,
                    'line_length' => strlen($line),
                    'line_content' => substr($line, 0, 100) . '...'
                ]);
                return false;
            }
        }

        // Check for proper indentation in method chains
        if (str_contains($code, '->') && str_contains($code, "\n")) {
            $hasProperChaining = false;
            
            foreach ($lines as $line) {
                $trimmed = trim($line);
                // Look for properly indented method chains
                if (str_starts_with($trimmed, '->') || 
                    str_starts_with($trimmed, '?') || 
                    str_starts_with($trimmed, ':')) {
                    $hasProperChaining = true;
                    break;
                }
            }
            
            // For method chains, we expect to see proper line breaking
            if (!$hasProperChaining && str_contains($code, 'whereRaw')) {
                Log::info('Method chain formatting could be improved but acceptable', [
                    'issue_id' => $issue->id,
                    'contains_whereraw' => true
                ]);
            }
        }

        return true;
    }

    /**
     * Check if a method should have a return statement based on context
     */
    protected function shouldHaveReturnStatement(Issue $issue, string $code): bool
    {
        // If the original file has a return statement, the fix should preserve it
        if (!File::exists($issue->file_path)) {
            return false;
        }

        $originalContent = File::get($issue->file_path);
        $lines = explode("\n", $originalContent);
        
        // Get context around the issue line
        $contextStart = max(0, $issue->line_number - 10);
        $contextEnd = min(count($lines), $issue->line_number + 10);
        
        $hasOriginalReturn = false;
        $isInMethodThatReturns = false;
        
        // Check if the original context has a return statement
        for ($i = $contextStart; $i < $contextEnd; $i++) {
            $line = trim($lines[$i] ?? '');
            
            if (str_starts_with($line, 'return ')) {
                $hasOriginalReturn = true;
                break;
            }
            
            // Check for method signature with return type
            if (preg_match('/function\s+\w+\([^)]*\)\s*:\s*\w+/', $line)) {
                $isInMethodThatReturns = true;
            }
        }
        
        // If original had return but fixed code doesn't, that's likely wrong
        if ($hasOriginalReturn && !str_contains($code, 'return')) {
            return true;
        }
        
        // If method signature indicates return type but code doesn't return
        if ($isInMethodThatReturns && !str_contains($code, 'return')) {
            // Exception: void methods or constructors don't need return
            if (!str_contains($code, ': void') && !str_contains($code, '__construct')) {
                return true;
            }
        }
        
        // Check for specific patterns that usually need return statements
        $needsReturn = [
            '->get()',
            '->first()',
            '->find(',
            '->pluck(',
            '->count()',
            '->sum(',
            '->avg(',
            '->max(',
            '->min(',
            '->value(',
            '->exists()',
            '->doesntExist()',
            '->paginate(',
            '->simplePaginate(',
            '->chunk(',
            '->lazy(',
            '->cursor()',
        ];
        
        foreach ($needsReturn as $pattern) {
            if (str_contains($code, $pattern) && !str_contains($code, 'return')) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check for incorrect query builder modifications
     */
    protected function hasIncorrectQueryBuilderChanges(Issue $issue, string $code): bool
    {
        if (!File::exists($issue->file_path)) {
            return false;
        }

        $originalContent = File::get($issue->file_path);
        $lines = explode("\n", $originalContent);
        
        // Get the original line content
        $originalLine = trim($lines[$issue->line_number - 1] ?? '');
        
        // Check for common incorrect transformations
        $incorrectChanges = [
            // Changing where() to with() - completely different purposes
            [
                'original_pattern' => '/->where\s*\([^)]+\)/',
                'fixed_pattern' => '/->with\s*\([^)]+\)/',
                'description' => 'Incorrectly changed where() to with()'
            ],
            
            // Removing return from methods that clearly need it
            [
                'original_pattern' => '/return\s+/',
                'fixed_pattern' => '/^(?!.*return).*$/',
                'description' => 'Removed return statement from method that needs it'
            ],
            
            // Changing specific query methods incorrectly
            [
                'original_pattern' => '/->whereHas\s*\([^)]+\)/',
                'fixed_pattern' => '/->has\s*\([^)]+\)/',
                'description' => 'Incorrectly changed whereHas() to has()'
            ],
            
            // Changing database column references incorrectly
            [
                'original_pattern' => '/where\s*\(\s*[\'"]([^\'",]+)[\'"]\s*,/',
                'fixed_pattern' => '/with\s*\(\s*[\'"]([^\'",]+)[\'"]\s*[,\)]/',
                'description' => 'Incorrectly changed where clause with column to with() relationship'
            ]
        ];
        
        foreach ($incorrectChanges as $change) {
            $originalMatches = preg_match($change['original_pattern'], $originalLine);
            $fixedMatches = preg_match($change['fixed_pattern'], $code);
            
            // If original had the pattern and fixed code shows the incorrect transformation
            if ($originalMatches && $fixedMatches) {
                Log::warning('Detected incorrect query builder change', [
                    'issue_id' => $issue->id,
                    'change_type' => $change['description'],
                    'original_line' => $originalLine,
                    'fixed_code_preview' => substr($code, 0, 200)
                ]);
                return true;
            }
        }
        
        // Special check for your specific case: where('locale', $locale) -> with('locale')
        if (str_contains($originalLine, "where('locale'") && 
            str_contains($code, "with('locale')") && 
            !str_contains($code, 'where(')) {
            
            Log::warning('Detected incorrect locale where->with transformation', [
                'issue_id' => $issue->id,
                'original_line' => $originalLine,
                'fixed_code_preview' => substr($code, 0, 200)
            ]);
            return true;
        }
        
        return false;
    }

    /**
     * Check if we're trying to insert class-level code inside an array
     * 
     * This prevents AI from generating const/property/method declarations
     * in response to issues inside array definitions (e.g., magic numbers in arrays)
     */
    protected function isInsertingClassCodeInArray(array $lines, int $targetLine, string $code): bool
    {
        // Check if the generated code is class-level (const, property, method)
        $trimmedCode = trim($code);
        $isClassLevelCode = preg_match('/^(public|protected|private|const)\s+/', $trimmedCode);
        
        Log::info('🔍 Array detection check', [
            'target_line' => $targetLine + 1, // Convert to 1-indexed
            'code' => substr($trimmedCode, 0, 100),
            'is_class_level_code' => $isClassLevelCode,
            'line_content' => substr($lines[$targetLine] ?? 'N/A', 0, 100),
        ]);
        
        if (!$isClassLevelCode) {
            Log::info('✅ Not class-level code, safe to proceed');
            return false; // Not class-level code, so it's safe
        }
        
        // Check if the target line is inside an array
        // Look backwards for unclosed array opening
        $openBrackets = 0;
        $inArray = false;
        
        for ($i = $targetLine; $i >= 0 && $i >= $targetLine - 50; $i--) {
            $line = $lines[$i] ?? '';
            
            // Count brackets on this line
            $openCount = substr_count($line, '[');
            $closeCount = substr_count($line, ']');
            $openBrackets += ($closeCount - $openCount); // Going backwards, so reversed
            
            Log::debug('Bracket counting', [
                'line_num' => $i + 1,
                'line' => substr($line, 0, 80),
                'open_count' => $openCount,
                'close_count' => $closeCount,
                'cumulative_open_brackets' => $openBrackets,
            ]);
            
            // If we find an opening bracket without close, we're in an array
            if ($openBrackets > 0) {
                $inArray = true;
                Log::warning('🚫 DETECTED: Inside array context!', [
                    'stopped_at_line' => $i + 1,
                    'open_brackets' => $openBrackets,
                ]);
                break;
            }
            
            // If we hit a semicolon or closing brace at the start of a line, we've exited any array
            if (preg_match('/^\s*[;{}]/', $line)) {
                Log::info('Found statement terminator, stopping search', [
                    'line' => $i + 1,
                ]);
                break;
            }
        }
        
        Log::info($inArray ? '❌ BLOCKING: Class code in array' : '✅ Not in array, allowing', [
            'in_array' => $inArray,
        ]);
        
        return $inArray;
    }

}
