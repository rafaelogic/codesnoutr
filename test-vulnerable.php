<?php

// This file contains intentional security vulnerabilities for testing

class VulnerableTestClass 
{
    public function sqlInjectionTest($userInput)
    {
        // SQL injection vulnerability
        $sql = "SELECT * FROM users WHERE id = " . $userInput;
        DB::statement($sql);
        
        // XSS vulnerability
        echo $userInput;
        
        return $sql;
    }
    
    public function hardcodedCredentials()
    {
        $password = "admin123";
        $apiKey = "sk-1234567890abcdef";
        return $password;
    }
    
    public function weakCrypto($data)
    {
        return md5($data);
    }
}
