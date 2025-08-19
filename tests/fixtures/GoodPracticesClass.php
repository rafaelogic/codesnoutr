<?php

class GoodPracticesClass 
{
    /**
     * This is a well-documented method with proper practices.
     *
     * @param string $input The input parameter
     * @return array The processed result
     */
    public function secureMethod(string $input): array
    {
        // Proper parameter binding
        $results = DB::select('SELECT * FROM users WHERE email = ?', [$input]);
        
        // Proper output escaping
        $safeOutput = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        return [
            'data' => $results,
            'safe_output' => $safeOutput
        ];
    }
    
    /**
     * Efficient database querying with eager loading.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function efficientQuery()
    {
        // Eager loading to prevent N+1 queries
        return User::with('posts')->get();
    }
    
    /**
     * Simple method with good readability.
     *
     * @param int $value
     * @return bool
     */
    public function simpleMethod(int $value): bool
    {
        return $value > 0;
    }
}
