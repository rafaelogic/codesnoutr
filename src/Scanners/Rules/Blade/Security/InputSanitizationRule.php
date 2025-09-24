<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Blade\Security;

use Rafaelogic\CodeSnoutr\Scanners\Rules\Blade\AbstractBladeRule;

class InputSanitizationRule extends AbstractBladeRule
{
    protected array $requiresValidationInputs = [
        'email', 'phone', 'url', 'date', 'number', 
        'password', 'file', 'upload'
    ];

    /**
     * Analyze Blade content for input sanitization issues
     */
    protected function analyzeBladeContent(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            $this->checkInputFields($filePath, $line, $lineNumber);
            $this->checkFileUploads($filePath, $line, $lineNumber);
            $this->checkUserInputDisplay($filePath, $line, $lineNumber);
        }
    }

    /**
     * Check for input fields that may require validation
     */
    protected function checkInputFields(string $filePath, string $line, int $lineNumber): void
    {
        if (preg_match('/<input[^>]*>/i', $line)) {
            foreach ($this->requiresValidationInputs as $inputType) {
                if (preg_match('/type\s*=\s*[\'\"]{$inputType}[\'\"]/i', $line) ||
                    preg_match('/name\s*=\s*[\'"][^\'\"]*{$inputType}[^\'\"]*[\'\"]/i', $line)) {
                    
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'security',
                        'medium',
                        'blade.input_validation_required',
                        'Input Field May Require Validation',
                        "Input field of type '{$inputType}' should have proper validation.",
                        'Add validation rules in the controller or use Laravel form validation.',
                        []
                    ));
                }
            }
        }
    }

    /**
     * Check for file upload fields
     */
    protected function checkFileUploads(string $filePath, string $line, int $lineNumber): void
    {
        if (preg_match('/<input[^>]*type\s*=\s*[\'\""]file[\'\""][^>]*>/i', $line)) {
            $this->addIssue($this->createIssue(
                $filePath,
                $lineNumber,
                'security',
                'high',
                'blade.file_upload_security',
                'File Upload Security Concern',
                'File uploads require proper validation and security measures.',
                'Validate file type, size, and scan for malware. Store uploads outside web root.',
                []
            ));
        }
    }

    /**
     * Check for user input being displayed
     */
    protected function checkUserInputDisplay(string $filePath, string $line, int $lineNumber): void
    {
        if (preg_match('/\{\{\s*\$([^}]+)\s*\}\}/', $line, $matches)) {
            $variable = trim($matches[1]);
            
            if ($this->isUserInputVariable($variable)) {
                // This is actually good - user input should be escaped by default with {{ }}
                // But we can suggest additional validation
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'info',
                    'blade.user_input_display',
                    'User Input Display',
                    'User input is being displayed. Good that it\'s escaped, but consider additional sanitization.',
                    'Ensure input was validated and sanitized in the controller before displaying.',
                    []
                ));
            }
        }
    }
}