<?php

namespace RafaelOgic\CodeSnoutr\Scanners\Rules;

/**
 * Blade Template Rules Engine
 * 
 * Comprehensive scanning rules for Laravel Blade templates including:
 * - Security vulnerabilities (XSS, CSRF protection)
 * - Performance optimizations 
 * - Code quality and best practices
 * - Accessibility compliance
 * - SEO optimization
 * - Maintainability issues
 */
class BladeRules extends AbstractRuleEngine
{
    protected array $validBladeDirectives = [
        // Control Structures
        '@if', '@elseif', '@else', '@endif',
        '@unless', '@endunless',
        '@for', '@endfor',
        '@foreach', '@endforeach',
        '@forelse', '@empty', '@endforelse',
        '@while', '@endwhile',
        '@switch', '@case', '@break', '@default', '@endswitch',
        
        // Template Inheritance
        '@extends', '@section', '@endsection', '@show',
        '@yield', '@parent', '@overwrite',
        '@include', '@includeIf', '@includeWhen', '@includeUnless',
        '@component', '@endcomponent', '@slot', '@endslot',
        
        // Authentication & Authorization
        '@auth', '@endauth', '@guest', '@endguest',
        '@can', '@cannot', '@endcan', '@endcannot',
        
        // Laravel Specific
        '@csrf', '@method', 
        '@error', '@enderror',
        '@env', '@endenv',
        '@production', '@endproduction',
        '@php', '@endphp',
        '@json', '@verbatim', '@endverbatim',
        
        // Custom & Third Party
        '@push', '@prepend', '@endpush', '@endprepend',
        '@stack', '@once', '@endonce',
        '@livewire', '@livewireScripts', '@livewireStyles',
    ];

    protected array $xssVulnerableHelpers = [
        'html_entity_decode',
        'htmlspecialchars_decode',
        'strip_tags',
        'urldecode',
        'base64_decode',
        'unserialize',
    ];

    public function analyze(string $filePath, array $ast, string $content): array
    {
        // Only analyze Blade templates
        if (!str_ends_with($filePath, '.blade.php')) {
            return [];
        }

        // Security checks
        $this->checkXSSVulnerabilities($filePath, $content);
        $this->checkCSRFProtection($filePath, $content);
        $this->checkInputSanitization($filePath, $content);
        
        // Performance checks
        $this->checkN1Queries($filePath, $content);
        $this->checkInlineStyles($filePath, $content);
        $this->checkLargeLoops($filePath, $content);
        
        // Code quality checks
        $this->checkTemplateComplexity($filePath, $content);
        $this->checkPhpInTemplate($filePath, $content);
        $this->checkDeprecatedSyntax($filePath, $content);
        $this->checkHardcodedValues($filePath, $content);
        
        // Best practices
        $this->checkSectionStructure($filePath, $content);
        $this->checkIncludeUsage($filePath, $content);
        $this->checkComponentUsage($filePath, $content);
        
        // Accessibility checks
        $this->checkAccessibility($filePath, $content);
        
        // SEO checks
        $this->checkSEOElements($filePath, $content);
        
        // Maintainability
        $this->checkDuplicatedCode($filePath, $content);
        $this->checkUnusedVariables($filePath, $content);
        
        return $this->getIssues();
    }

    /**
     * Check for XSS vulnerabilities in Blade templates
     */
    protected function checkXSSVulnerabilities(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for unescaped output {!! !!}
            if (preg_match('/\{!!\s*\$([^}]+)\s*!!\}/', $line, $matches)) {
                $variable = trim($matches[1]);
                
                // Allow certain safe variables (like rendered HTML from markdown, etc.)
                if (!$this->isSafeUnescapedVariable($variable, $content)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'security',
                        'high',
                        'blade.xss_unescaped',
                        'Potential XSS Vulnerability',
                        'Unescaped output {!! !!} can lead to XSS attacks if user input is displayed.',
                        'Use escaped output {{ }} instead, or ensure the variable contains safe HTML.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for dangerous functions in Blade output
            foreach ($this->xssVulnerableHelpers as $helper) {
                if (preg_match('/\{\{\s*' . preg_quote($helper) . '\s*\(/', $line)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'security',
                        'medium',
                        'blade.dangerous_function',
                        'Dangerous Function in Template',
                        "Function {$helper}() can be dangerous when used with user input.",
                        'Validate and sanitize input before using this function, or move logic to controller.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for HTML in variables that should be escaped
            if (preg_match('/\{\{\s*\$([^}]+)\s*\}\}/', $line, $matches)) {
                $variable = trim($matches[1]);
                if (preg_match('/html|content|description|message/i', $variable) && 
                    !str_contains($line, '|escape') && !str_contains($line, '|e')) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'security',
                        'medium',
                        'blade.html_variable_escaping',
                        'HTML Variable May Need Escaping',
                        'Variables containing HTML content should be carefully handled to prevent XSS.',
                        'Consider using {!! !!} for safe HTML or add |escape filter for user content.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
        }
    }

    /**
     * Check for proper CSRF protection in forms
     */
    protected function checkCSRFProtection(string $filePath, string $content): void
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
                        'critical',
                        'blade.missing_csrf',
                        'Missing CSRF Protection',
                        'Forms with state-changing methods must include CSRF protection.',
                        'Add @csrf directive inside your form tag.',
                        $this->getCodeContext($content, $formLineNumber)
                    ));
                }
                $inForm = false;
            }
        }
    }

    /**
     * Check for input sanitization issues
     */
    protected function checkInputSanitization(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for direct request input display
            if (preg_match('/\{\{\s*request\(\)\s*->\s*(input|get|post)\s*\([^}]+\}\}/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'high',
                    'blade.direct_request_input',
                    'Direct Request Input Display',
                    'Displaying request input directly can lead to XSS vulnerabilities.',
                    'Validate and sanitize input in controller before passing to view.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for $_GET, $_POST usage in templates
            if (preg_match('/\$_(GET|POST|REQUEST|COOKIE|SESSION)\[/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'security',
                    'high',
                    'blade.superglobal_usage',
                    'Superglobal Usage in Template',
                    'Using superglobals in templates bypasses Laravel\'s request handling.',
                    'Use controller to handle request data and pass to view as variables.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for potential N+1 query issues in templates
     */
    protected function checkN1Queries(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for relationship access in loops
            if (preg_match('/@foreach\s*\(\s*\$(\w+)\s+as\s+\$(\w+)\s*\)/', $line, $matches)) {
                $collection = $matches[1];
                $item = $matches[2];
                
                // Look ahead for relationship access
                $nextLines = array_slice($lines, $lineNumber, 10);
                foreach ($nextLines as $nextLine) {
                    if (preg_match('/\$' . preg_quote($item) . '\s*->\s*(\w+)(?!\s*\()/', $nextLine, $relationMatches)) {
                        $relation = $relationMatches[1];
                        
                        // Common relationship names that might indicate N+1
                        if (preg_match('/(user|author|category|tags|comments|posts|orders|items)/', $relation)) {
                            $this->addIssue($this->createIssue(
                                $filePath,
                                $lineNumber,
                                'performance',
                                'medium',
                                'blade.potential_n1_query',
                                'Potential N+1 Query in Loop',
                                "Accessing relationship '{$relation}' in loop may cause N+1 query problem.",
                                "Eager load the relationship in controller: \$collection = \${$collection}->with('{$relation}')->get();",
                                $this->getCodeContext($content, $lineNumber)
                            ));
                            break;
                        }
                    }
                    
                    if (preg_match('/@endforeach/', $nextLine)) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * Check for inline styles that should be moved to CSS
     */
    protected function checkInlineStyles(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for inline styles
            if (preg_match('/style\s*=\s*[\'\"]/i', $line)) {
                // Allow simple utility styles (tailwind-like)
                if (!preg_match('/style\s*=\s*[\'\"](margin|padding|display|color):\s*[^;]+;?\s*[\'\"]/', $line)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'blade.inline_styles',
                        'Inline Styles Usage',
                        'Inline styles make styling harder to maintain and reuse.',
                        'Move styles to CSS classes or use utility classes (Tailwind CSS).',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for style tags in templates
            if (preg_match('/<style[^>]*>/i', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'medium',
                    'blade.style_tags',
                    'Style Tags in Template',
                    'Style tags in templates make CSS harder to manage and cache.',
                    'Move styles to external CSS files or use @push(\'styles\') for page-specific styles.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for large loops that might impact performance
     */
    protected function checkLargeLoops(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for nested loops
            if (preg_match('/@foreach/', $line)) {
                $nestedLevel = 1;
                $nextLines = array_slice($lines, $lineNumber, 50);
                
                foreach ($nextLines as $nextLine) {
                    if (preg_match('/@foreach/', $nextLine)) {
                        $nestedLevel++;
                        
                        if ($nestedLevel >= 3) {
                            $this->addIssue($this->createIssue(
                                $filePath,
                                $lineNumber,
                                'performance',
                                'medium',
                                'blade.deeply_nested_loops',
                                'Deeply Nested Loops',
                                'Deeply nested loops can impact template rendering performance.',
                                'Consider restructuring data in controller or using components.',
                                $this->getCodeContext($content, $lineNumber)
                            ));
                            break;
                        }
                    }
                    
                    if (preg_match('/@endforeach/', $nextLine)) {
                        $nestedLevel--;
                        if ($nestedLevel <= 0) break;
                    }
                }
            }
        }
    }

    /**
     * Check template complexity
     */
    protected function checkTemplateComplexity(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        $complexity = 0;
        $nestingLevel = 0;
        $maxNesting = 0;
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Count complexity factors
            if (preg_match('/@(if|elseif|unless|for|foreach|while|switch)/', $line)) {
                $complexity++;
                $nestingLevel++;
                $maxNesting = max($maxNesting, $nestingLevel);
            }
            
            if (preg_match('/@(endif|endunless|endfor|endforeach|endwhile|endswitch)/', $line)) {
                $nestingLevel--;
            }
        }
        
        // Check for high complexity
        if ($complexity > 15) {
            $this->addIssue($this->createIssue(
                $filePath,
                1,
                'quality',
                'medium',
                'blade.high_complexity',
                'High Template Complexity',
                "Template has high complexity score ({$complexity}). Complex templates are harder to maintain.",
                'Consider breaking template into smaller components or moving logic to controllers.',
                $this->getCodeContext($content, 1)
            ));
        }
        
        // Check for deep nesting
        if ($maxNesting > 5) {
            $this->addIssue($this->createIssue(
                $filePath,
                1,
                'quality',
                'medium',
                'blade.deep_nesting',
                'Deep Template Nesting',
                "Template has deep nesting ({$maxNesting} levels). Deep nesting reduces readability.",
                'Consider using template inheritance, components, or includes to reduce nesting.',
                $this->getCodeContext($content, 1)
            ));
        }
    }

    /**
     * Check for PHP code in templates
     */
    protected function checkPhpInTemplate(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for @php blocks
            if (preg_match('/@php/', $line)) {
                // Allow simple variable assignments
                if (!preg_match('/@php\s*\$\w+\s*=\s*[^;]+;\s*@endphp/', $line)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'medium',
                        'blade.php_in_template',
                        'PHP Code in Template',
                        'Complex PHP logic in templates reduces separation of concerns.',
                        'Move logic to controllers, view composers, or custom Blade directives.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for PHP opening tags (should not exist in Blade)
            if (preg_match('/<\?php|<\?=/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'high',
                    'blade.php_tags',
                    'PHP Tags in Blade Template',
                    'PHP tags should not be used in Blade templates.',
                    'Use Blade syntax instead: {{ }} for output, @php for PHP blocks.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for deprecated Blade syntax
     */
    protected function checkDeprecatedSyntax(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for deprecated syntax patterns
            $deprecatedPatterns = [
                '/\{\{\{\s*(.+?)\s*\}\}\}/' => 'Use {!! $1 !!} instead of {{{ }}}',
                '/\{\?\s*(.+?)\s*\?\}/' => 'Use {{ $1 }} instead of {? ?}',
                '/@section\([^,]+\)\s*,\s*true\)/' => 'Use @section and @show instead of @section with true parameter',
            ];
            
            foreach ($deprecatedPatterns as $pattern => $suggestion) {
                if (preg_match($pattern, $line)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'medium',
                        'blade.deprecated_syntax',
                        'Deprecated Blade Syntax',
                        'Using deprecated syntax that may be removed in future Laravel versions.',
                        $suggestion,
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
        }
    }

    /**
     * Check for hardcoded values that should be configurable
     */
    protected function checkHardcodedValues(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for hardcoded URLs
            if (preg_match('/href\s*=\s*[\'\"](https?:\/\/[^\'\"]+)[\'\"]/', $line, $matches)) {
                $url = $matches[1];
                if (!str_contains($url, config('app.url'))) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'blade.hardcoded_url',
                        'Hardcoded External URL',
                        'Hardcoded URLs make configuration changes difficult.',
                        'Use config() helper or environment variables for external URLs.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
            
            // Check for hardcoded email addresses
            if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $line) && 
                !str_contains($line, '@example.com')) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'info',
                    'blade.hardcoded_email',
                    'Hardcoded Email Address',
                    'Hardcoded email addresses should be configurable.',
                    'Use config() helper to store email addresses in configuration.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check section structure and usage
     */
    protected function checkSectionStructure(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        $sections = [];
        $yields = [];
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Track sections
            if (preg_match('/@section\s*\(\s*[\'\"]([\w-]+)[\'\"]\s*\)/', $line, $matches)) {
                $sectionName = $matches[1];
                $sections[$sectionName] = $lineNumber;
            }
            
            // Track yields
            if (preg_match('/@yield\s*\(\s*[\'\"]([\w-]+)[\'\"]\s*\)/', $line, $matches)) {
                $yieldName = $matches[1];
                $yields[$yieldName] = $lineNumber;
            }
            
            // Check for unclosed sections
            if (preg_match('/@section\s*\([^)]+\)\s*$/', $line) && 
                !preg_match('/@endsection|@show/', $content, $matches, PREG_OFFSET_CAPTURE, strpos($content, $line))) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'medium',
                    'blade.unclosed_section',
                    'Unclosed Section',
                    'Section is opened but never closed.',
                    'Add @endsection or @show to close the section.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
        
        // Check for unused sections (sections defined but no corresponding yield)
        foreach ($sections as $sectionName => $lineNumber) {
            if (!isset($yields[$sectionName]) && !str_contains($content, "@parent") && 
                !in_array($sectionName, ['content', 'styles', 'scripts'])) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'info',
                    'blade.unused_section',
                    'Unused Section',
                    "Section '{$sectionName}' is defined but never yielded.",
                    'Remove unused section or add @yield in parent template.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check include usage patterns
     */
    protected function checkIncludeUsage(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for includes in loops
            if (preg_match('/@include\s*\(/', $line)) {
                // Look backward for loop
                $prevLines = array_slice($lines, max(0, $lineNumber - 10), 10);
                foreach (array_reverse($prevLines) as $prevLine) {
                    if (preg_match('/@foreach|@for|@while/', $prevLine)) {
                        $this->addIssue($this->createIssue(
                            $filePath,
                            $lineNumber,
                            'performance',
                            'medium',
                            'blade.include_in_loop',
                            'Include in Loop',
                            'Including templates in loops can impact performance.',
                            'Consider using @each directive or move to component.',
                            $this->getCodeContext($content, $lineNumber)
                        ));
                        break;
                    }
                }
            }
        }
    }

    /**
     * Check component usage best practices
     */
    protected function checkComponentUsage(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for component without closing tag
            if (preg_match('/<x-[\w.-]+[^>]*>/', $line) && 
                !preg_match('/<x-[\w.-]+[^>]*\/>/', $line) && 
                !str_contains($content, '</x-')) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'medium',
                    'blade.unclosed_component',
                    'Unclosed Component',
                    'Component is opened but never closed.',
                    'Add closing tag or use self-closing syntax.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check basic accessibility requirements
     */
    protected function checkAccessibility(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for images without alt text
            if (preg_match('/<img[^>]+>/', $line) && !preg_match('/alt\s*=/', $line)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'quality',
                    'medium',
                    'blade.missing_alt_text',
                    'Missing Alt Text',
                    'Images should have alt text for accessibility.',
                    'Add alt attribute to img tag.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
            
            // Check for form inputs without labels
            if (preg_match('/<input[^>]+>/', $line) && 
                !preg_match('/type\s*=\s*[\'\"](hidden|submit|button)[\'\"]/i', $line)) {
                // Look for associated label
                if (!preg_match('/aria-label|aria-labelledby/', $line) && 
                    !str_contains($content, 'for=')) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'medium',
                        'blade.missing_form_label',
                        'Missing Form Label',
                        'Form inputs should have associated labels for accessibility.',
                        'Add label element or aria-label attribute.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
        }
    }

    /**
     * Check basic SEO elements
     */
    protected function checkSEOElements(string $filePath, string $content): void
    {
        // Only check layout files
        if (!str_contains($filePath, 'layout') && !str_contains($filePath, 'app.blade.php')) {
            return;
        }
        
        // Check for missing SEO elements
        if (!preg_match('/<title[^>]*>/', $content)) {
            $this->addIssue($this->createIssue(
                $filePath,
                1,
                'quality',
                'medium',
                'blade.missing_title',
                'Missing Page Title',
                'Pages should have title tags for SEO.',
                'Add <title> tag in head section.',
                $this->getCodeContext($content, 1)
            ));
        }
        
        if (!preg_match('/<meta[^>]+name\s*=\s*[\'\"](description|keywords)[\'\"]/i', $content)) {
            $this->addIssue($this->createIssue(
                $filePath,
                1,
                'quality',
                'info',
                'blade.missing_meta_description',
                'Missing Meta Description',
                'Pages should have meta description for SEO.',
                'Add meta description tag in head section.',
                $this->getCodeContext($content, 1)
            ));
        }
    }

    /**
     * Check for duplicated code blocks
     */
    protected function checkDuplicatedCode(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        $blocks = [];
        
        // Look for repeated blocks of 3+ lines
        for ($i = 0; $i < count($lines) - 2; $i++) {
            $block = trim($lines[$i] . $lines[$i + 1] . $lines[$i + 2]);
            if (strlen($block) > 50) { // Only check substantial blocks
                if (isset($blocks[$block])) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $i + 1,
                        'quality',
                        'info',
                        'blade.duplicated_code',
                        'Duplicated Code Block',
                        'Code block appears to be duplicated.',
                        'Consider extracting common code into partial or component.',
                        $this->getCodeContext($content, $i + 1)
                    ));
                } else {
                    $blocks[$block] = $i + 1;
                }
            }
        }
    }

    /**
     * Check for unused variables passed to template
     */
    protected function checkUnusedVariables(string $filePath, string $content): void
    {
        // Extract all Blade variable usage
        preg_match_all('/\$(\w+)/', $content, $matches);
        $usedVars = array_unique($matches[1]);
        
        // This is a basic check - in a real implementation, you'd want to
        // analyze the controller or view composer to see what variables are passed
        
        // For now, just check for common unused variable patterns
        $lines = explode("\n", $content);
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Look for variable assignments that aren't used later
            if (preg_match('/@php\s*\$(\w+)\s*=/', $line, $matches)) {
                $varName = $matches[1];
                $remainingContent = implode("\n", array_slice($lines, $lineNumber));
                
                if (!preg_match('/\$' . preg_quote($varName) . '(?!\s*=)/', $remainingContent)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'quality',
                        'info',
                        'blade.unused_variable',
                        'Unused Variable',
                        "Variable \${$varName} is assigned but never used.",
                        'Remove unused variable assignment.',
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
        }
    }

    /**
     * Check if unescaped variable is safe to use
     */
    protected function isSafeUnescapedVariable(string $variable, string $content): bool
    {
        // Allow certain patterns that are typically safe
        $safePatterns = [
            'html',           // Rendered HTML content
            'content',        // Markdown or HTML content
            'markdown',       // Converted markdown
            'svg',           // SVG icons
            'json',          // JSON data for JavaScript
            'script',        // JavaScript code
            'style',         // CSS styles
        ];
        
        foreach ($safePatterns as $pattern) {
            if (str_contains(strtolower($variable), $pattern)) {
                return true;
            }
        }
        
        // Check if variable is being filtered or processed safely
        if (preg_match('/\|escape|\|e\b|\|nl2br/', $variable)) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if a numeric value is legitimate in HTML/CSS context
     */
    protected function isLegitimateHtmlCssNumber(string $line, string $number): bool
    {
        // HTML attributes that commonly use numbers
        $htmlAttributes = [
            'width', 'height', 'size', 'maxlength', 'min', 'max', 'step', 
            'rows', 'cols', 'tabindex', 'colspan', 'rowspan', 'scale',
            'data-\w+', 'aria-\w+', 'role', 'span'
        ];
        
        // CSS properties that commonly use numbers
        $cssProperties = [
            'z-index', 'font-size', 'line-height', 'width', 'height', 
            'margin', 'padding', 'top', 'right', 'bottom', 'left', 
            'opacity', 'order', 'flex', 'grid', 'border-radius', 
            'font-weight', 'letter-spacing', 'word-spacing'
        ];
        
        // Check HTML attributes
        foreach ($htmlAttributes as $attr) {
            if (preg_match('/\b' . $attr . '=[\'"]\d*' . preg_quote($number) . '\d*[\'"]/', $line)) {
                return true;
            }
        }
        
        // Check CSS properties
        foreach ($cssProperties as $prop) {
            if (preg_match('/\b' . $prop . ':\s*\d*' . preg_quote($number) . '\d*/', $line)) {
                return true;
            }
        }
        
        // Check CSS units
        if (preg_match('/' . preg_quote($number) . '(px|em|rem|%|vh|vw|pt|pc|in|cm|mm|ex|ch|vmin|vmax|deg|rad|turn|s|ms)/', $line)) {
            return true;
        }
        
        // Check color values
        if (preg_match('/#[0-9a-fA-F]*' . preg_quote($number) . '|rgb\([^)]*' . preg_quote($number) . '|rgba\([^)]*' . preg_quote($number) . '/', $line)) {
            return true;
        }
        
        // Check viewport and media query values
        if (preg_match('/@media[^{]*' . preg_quote($number) . '|viewport[^;]*' . preg_quote($number) . '/', $line)) {
            return true;
        }
        
        return false;
    }
}
