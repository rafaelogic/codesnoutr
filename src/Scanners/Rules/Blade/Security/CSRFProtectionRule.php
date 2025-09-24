<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Blade\Security;

use Rafaelogic\CodeSnoutr\Scanners\Rules\Blade\AbstractBladeRule;

class CSRFProtectionRule extends AbstractBladeRule
{
    /**
     * Analyze Blade content for CSRF protection issues
     */
    protected function analyzeBladeContent(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        $inForm = false;
        $formLineNumber = 0;
        $hasCSRF = false;
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for form opening
            if (preg_match('/<form[^>]*>/i', $line)) {
                $inForm = true;
                $formLineNumber = $lineNumber;
                $hasCSRF = false;
                
                // Check if method is POST, PUT, PATCH, or DELETE
                if (preg_match('/method\s*=\s*[\'\"](post|put|patch|delete)/i', $line)) {
                    // Continue checking for CSRF token
                } else {
                    $inForm = false; // GET forms don't need CSRF
                }
            }
            
            // Check for CSRF token in form
            if ($inForm && preg_match('/@csrf|csrf_field\(\)|csrf_token\(\)/', $line)) {
                $hasCSRF = true;
            }
            
            // Check for form closing
            if ($inForm && preg_match('/<\/form>/i', $line)) {
                if (!$hasCSRF) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $formLineNumber,
                        'security',
                        'high',
                        'blade.missing_csrf',
                        'Missing CSRF Protection',
                        'Form with POST/PUT/PATCH/DELETE method is missing CSRF protection.',
                        'Add @csrf directive inside the form or use csrf_field() helper.',
                        $this->getCodeContext($content, $formLineNumber)
                    ));
                }
                $inForm = false;
            }
        }
        
        // Handle unclosed forms
        if ($inForm && !$hasCSRF) {
            $this->addIssue($this->createIssue(
                $filePath,
                $formLineNumber,
                'security',
                'high',
                'blade.missing_csrf',
                'Missing CSRF Protection',
                'Form with POST/PUT/PATCH/DELETE method is missing CSRF protection.',
                'Add @csrf directive inside the form or use csrf_field() helper.',
                $this->getCodeContext($content, $formLineNumber)
            ));
        }
    }
}