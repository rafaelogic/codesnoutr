<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules\Blade;

use Rafaelogic\CodeSnoutr\Scanners\Rules\AbstractRuleEngine;

abstract class AbstractBladeRule extends AbstractRuleEngine
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

    /**
     * Analyze Blade template content
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        // Only analyze Blade templates
        if (!str_ends_with($filePath, '.blade.php')) {
            return [];
        }

        $this->clearIssues();
        $this->analyzeBladeContent($filePath, $content);
        
        return $this->getIssues();
    }

    /**
     * Analyze Blade content - to be implemented by concrete classes
     */
    abstract protected function analyzeBladeContent(string $filePath, string $content): void;

    /**
     * Check if this is a safe unescaped variable
     */
    protected function isSafeUnescapedVariable(string $variable, string $content): bool
    {
        $safePatterns = [
            'html',
            'content',
            'markdown',
            'rendered',
            'safe_html',
            'trusted',
        ];

        foreach ($safePatterns as $pattern) {
            if (str_contains(strtolower($variable), $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Count nested structures in content
     */
    protected function countNestedStructures(string $content): int
    {
        $depth = 0;
        $maxDepth = 0;
        
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            // Count opening structures
            if (preg_match_all('/@(if|foreach|for|while|switch|section|component|can|auth)/', $line, $matches)) {
                $depth += count($matches[0]);
            }
            
            // Count closing structures
            if (preg_match_all('/@(endif|endforeach|endfor|endwhile|endswitch|endsection|endcomponent|endcan|endauth)/', $line, $matches)) {
                $depth -= count($matches[0]);
            }
            
            $maxDepth = max($maxDepth, $depth);
        }
        
        return $maxDepth;
    }

    /**
     * Extract variables from Blade expressions
     */
    protected function extractVariables(string $content): array
    {
        $variables = [];
        
        // Find {{ $variable }} patterns
        if (preg_match_all('/\{\{\s*\$([a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]*\]|\->[a-zA-Z_][a-zA-Z0-9_]*)*)\s*\}\}/', $content, $matches)) {
            $variables = array_merge($variables, $matches[1]);
        }
        
        // Find {!! $variable !!} patterns
        if (preg_match_all('/\{!!\s*\$([a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]*\]|\->[a-zA-Z_][a-zA-Z0-9_]*)*)\s*!!\}/', $content, $matches)) {
            $variables = array_merge($variables, $matches[1]);
        }
        
        return array_unique($variables);
    }

    /**
     * Check if variable is likely to contain user input
     */
    protected function isUserInputVariable(string $variable): bool
    {
        $userInputPatterns = [
            'input', 'request', 'post', 'get', 'data',
            'comment', 'message', 'content', 'description',
            'name', 'email', 'username', 'search', 'query'
        ];

        $lowerVariable = strtolower($variable);
        
        foreach ($userInputPatterns as $pattern) {
            if (str_contains($lowerVariable, $pattern)) {
                return true;
            }
        }

        return false;
    }
}