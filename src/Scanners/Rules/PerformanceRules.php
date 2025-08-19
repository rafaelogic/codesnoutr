<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules;

class PerformanceRules extends AbstractRuleEngine
{
    /**
     * Analyze code for performance issues
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        
        // Check for N+1 query problems
        $this->checkNPlusOneQueries($filePath, $content);
        
        // Check for inefficient loops
        $this->checkInefficientLoops($filePath, $content);
        
        // Check for memory usage issues
        $this->checkMemoryUsage($filePath, $content);
        
        // Check for database performance issues
        $this->checkDatabasePerformance($filePath, $content);
        
        // Check for caching opportunities
        $this->checkCachingOpportunities($filePath, $content);
        
        return $this->getIssues();
    }

    /**
     * Check for N+1 query problems
     */
    protected function checkNPlusOneQueries(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for loops with database queries
            if (preg_match('/foreach\\s*\\(/', $line)) {
                // Look for database queries in the next 10 lines
                for ($i = $lineNumber; $i < min($lineNumber + 10, count($lines)); $i++) {
                    if (preg_match('/(Model::|->where\\(|->find\\(|->first\\(|DB::)/', $lines[$i])) {
                        $this->addIssue($this->createIssue(
                            $filePath,
                            $lineNumber,
                            'performance',
                            'high',
                            'performance.n_plus_one_query',
                            'Potential N+1 Query Problem',
                            'Database queries inside loops can cause N+1 query problems.',
                            'Use eager loading with ->with() or load queries outside the loop.',
                            $this->getCodeContext($content, $lineNumber)
                        ));
                        break;
                    }
                }
            }
            
            // Check for missing eager loading
            if (preg_match('/->get\\(\\)\\s*;/', $line) && 
                !preg_match('/->with\\(/', $content)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'performance',
                    'medium',
                    'performance.missing_eager_loading',
                    'Missing Eager Loading',
                    'Consider using eager loading to prevent N+1 queries.',
                    'Use ->with([\'relation\']) to eagerly load related models.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for inefficient loops
     */
    protected function checkInefficientLoops(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for count() in loop conditions
            if (preg_match('/for\\s*\\([^;]*;[^;]*count\\s*\\(/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'performance',
                    'medium',
                    'performance.count_in_loop',
                    'count() in Loop Condition',
                    'Using count() in loop conditions is inefficient as it executes every iteration.',
                    'Store count() result in a variable before the loop.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for nested loops
            if (preg_match('/foreach\\s*\\(/', $line)) {
                // Check for another loop in the next 15 lines
                for ($i = $lineNumber; $i < min($lineNumber + 15, count($lines)); $i++) {
                    if (preg_match('/(foreach|for|while)\\s*\\(/', $lines[$i]) && $i != $lineNumber - 1) {
                        $this->addIssue($this->createIssue(
                            $filePath,
                            $lineNumber,
                            'performance',
                            'medium',
                            'performance.nested_loops',
                            'Nested Loops Detected',
                            'Nested loops can have O(n²) complexity. Consider optimization.',
                            'Use array functions, hash maps, or refactor algorithm for better performance.',
                            $this->getCodeContext($content, $lineNumber)
                        ));
                        break;
                    }
                }
            }
        }
    }

    /**
     * Check for memory usage issues
     */
    protected function checkMemoryUsage(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for ->all() without chunking
            if (preg_match('/->all\\(\\)/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'performance',
                    'high',
                    'performance.load_all_records',
                    'Loading All Records',
                    'Using ->all() loads all records into memory which can cause memory issues.',
                    'Consider using ->chunk() or pagination for large datasets.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for large array operations
            if (preg_match('/array_merge\\s*\\(.*\\$\\w+.*\\)/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'performance',
                    'medium',
                    'performance.array_merge_in_loop',
                    'Array Merge with Variables',
                    'array_merge() with large arrays or in loops can be memory intensive.',
                    'Consider using array_push() or the spread operator (...) for better performance.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for file_get_contents on large files
            if (preg_match('/file_get_contents\\s*\\(/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'performance',
                    'medium',
                    'performance.file_get_contents',
                    'Potential Memory Issue with file_get_contents',
                    'file_get_contents() loads entire file into memory.',
                    'For large files, consider using fopen() and fread() in chunks.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for database performance issues
     */
    protected function checkDatabasePerformance(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for missing indexes
            if (preg_match('/->where\\s*\\(\\s*[\'"]\\w+[\'"]/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'performance',
                    'info',
                    'performance.potential_missing_index',
                    'Potential Missing Database Index',
                    'WHERE clauses on unindexed columns can be slow.',
                    'Ensure database indexes exist for frequently queried columns.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for select *
            if (preg_match('/SELECT\\s+\\*\\s+FROM/i', $line) || 
                preg_match('/DB::select\\s*\\(\\s*[\'"]SELECT\\s+\\*/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'performance',
                    'medium',
                    'performance.select_star',
                    'SELECT * Usage',
                    'SELECT * retrieves all columns which may be inefficient.',
                    'Specify only the columns you need: SELECT column1, column2 FROM table',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for queries without limits
            if (preg_match('/->get\\(\\)/', $line) && 
                !preg_match('/(->limit\\(|->take\\(|->first\\(|->paginate\\()/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'performance',
                    'medium',
                    'performance.unlimited_query',
                    'Query Without Limit',
                    'Queries without limits can return large result sets.',
                    'Consider adding ->limit(), ->take(), or use pagination.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for caching opportunities
     */
    protected function checkCachingOpportunities(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for expensive operations that could be cached
            if (preg_match('/(file_get_contents|curl_exec|json_decode.*file_get_contents)/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'performance',
                    'info',
                    'performance.caching_opportunity',
                    'Caching Opportunity',
                    'This operation appears expensive and could benefit from caching.',
                    'Consider using Laravel Cache::remember() to cache the result.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for repeated database queries
            if (preg_match('/->find\\s*\\(\\s*\\$\\w+\\s*\\)/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'performance',
                    'info',
                    'performance.repeated_find',
                    'Potential Repeated Database Query',
                    'find() queries with variables could be repeated calls.',
                    'Consider caching the result or using whereIn() for multiple IDs.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }
}
