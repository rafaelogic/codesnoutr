<?php

class VulnerableClass 
{
    public function sqlInjectionExample($userInput)
    {
        // This should be detected as a security vulnerability
        $sql = "SELECT * FROM users WHERE id = " . $userInput;
        return $sql;
    }
    
    public function xssExample($userInput)
    {
        // This should be detected as XSS vulnerability
        echo $userInput;
    }
    
    public function hardcodedCredentials()
    {
        // This should be detected as hardcoded credentials
        $apiKey = "sk-1234567890abcdef";
        $password = "admin123";
        return [$apiKey, $password];
    }
    
    public function performanceIssue()
    {
        // This should be detected as N+1 query issue
        $users = User::all();
        foreach ($users as $user) {
            $user->posts; // N+1 query
        }
    }
    
    public function codeQualityIssue($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k)
    {
        // This should be detected as too many parameters
        if ($a > 0) {
            if ($b > 0) {
                if ($c > 0) {
                    if ($d > 0) {
                        if ($e > 0) {
                            // This should be detected as deep nesting
                            echo "Deep nesting detected";
                        }
                    }
                }
            }
        }
    }
}
