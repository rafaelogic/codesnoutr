<?php

namespace Rafaelogic\CodeSnoutr\Scanners\Rules;

class InheritanceRules extends AbstractRuleEngine
{
    /**
     * Analyze code for inheritance-related issues and exceptions
     */
    public function analyze(string $filePath, array $ast, string $content): array
    {
        $this->clearIssues();
        
        // Check interface implementations
        $this->checkInterfaceImplementations($filePath, $content);
        
        // Check abstract class implementations
        $this->checkAbstractImplementations($filePath, $content);
        
        // Check trait usage
        $this->checkTraitUsage($filePath, $content);
        
        // Check method overrides
        $this->checkMethodOverrides($filePath, $content);
        
        return $this->getIssues();
    }

    /**
     * Check interface implementations for completeness
     */
    protected function checkInterfaceImplementations(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for interface implementations
            if (preg_match('/class\s+(\w+).*implements\s+([\w\\\\,\s]+)/', $line, $matches)) {
                $className = $matches[1];
                $interfaces = $matches[2];
                
                // Parse interfaces
                $interfaceList = array_map('trim', explode(',', $interfaces));
                
                foreach ($interfaceList as $interface) {
                    $this->validateInterfaceImplementation($filePath, $content, $className, $interface, $lineNumber);
                }
            }
            
            // Check for incomplete interface implementations
            if (preg_match('/implements\s+[\w\\\\]+/', $line) && 
                !preg_match('/abstract\s+class/', $content)) {
                
                // Look for required methods that might be missing
                $this->checkRequiredInterfaceMethods($filePath, $content, $lineNumber);
            }
        }
    }

    /**
     * Check abstract class implementations
     */
    protected function checkAbstractImplementations(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for abstract class extensions
            if (preg_match('/class\s+(\w+)\s+extends\s+([\w\\\\]+)/', $line, $matches)) {
                $className = $matches[1];
                $parentClass = $matches[2];
                
                // Check if parent might be abstract and validate implementation
                $this->validateAbstractImplementation($filePath, $content, $className, $parentClass, $lineNumber);
            }
            
            // Check for abstract methods that need implementation
            if (preg_match('/abstract\s+/', $content) && 
                !preg_match('/abstract\s+class/', $line)) {
                
                $this->checkAbstractMethodImplementation($filePath, $content, $lineNumber);
            }
        }
    }

    /**
     * Check trait usage and potential conflicts
     */
    protected function checkTraitUsage(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        $usedTraits = [];
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for trait usage within class
            if (preg_match('/^\s*use\s+([\w\\\\]+);/', $line, $matches)) {
                $traitName = $matches[1];
                $usedTraits[] = $traitName;
                
                // Check for potential trait conflicts
                $this->checkTraitConflicts($filePath, $content, $traitName, $usedTraits, $lineNumber);
            }
            
            // Check for trait method resolution
            if (preg_match('/use\s+[\w\\\\]+\s*{/', $line)) {
                $this->checkTraitMethodResolution($filePath, $content, $lineNumber);
            }
        }
        
        // Check for missing trait method implementations
        if (!empty($usedTraits)) {
            $this->checkTraitMethodImplementations($filePath, $content, $usedTraits);
        }
    }

    /**
     * Check method overrides for proper implementation
     */
    protected function checkMethodOverrides(string $filePath, string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based
            
            // Check for method overrides
            if (preg_match('/public\s+function\s+(\w+)/', $line, $matches)) {
                $methodName = $matches[1];
                
                // Check if this might be overriding a parent method
                if ($this->isPotentialOverride($methodName, $content)) {
                    $this->validateMethodOverride($filePath, $content, $methodName, $lineNumber);
                }
            }
            
            // Check for missing parent calls
            if (preg_match('/function\s+(\w+)/', $line)) {
                $this->checkMissingParentCalls($filePath, $content, $lineNumber);
            }
        }
    }

    /**
     * Validate interface implementation
     */
    protected function validateInterfaceImplementation(string $filePath, string $content, string $className, string $interface, int $lineNumber): void
    {
        // Common Laravel interfaces and their expected methods
        $knownInterfaces = [
            'Arrayable' => ['toArray'],
            'Jsonable' => ['toJson'],
            'Responsable' => ['toResponse'],
            'Renderable' => ['render'],
            'ShouldQueue' => [], // No required methods, just marking
            'ShouldBroadcast' => ['broadcastOn'],
            'Authenticatable' => ['getAuthIdentifierName', 'getAuthIdentifier', 'getAuthPassword', 'getRememberToken', 'setRememberToken', 'getRememberTokenName'],
        ];
        
        $interfaceName = basename(str_replace('\\', '/', $interface));
        
        if (isset($knownInterfaces[$interfaceName])) {
            $requiredMethods = $knownInterfaces[$interfaceName];
            
            foreach ($requiredMethods as $method) {
                if (!preg_match("/function\s+{$method}/", $content)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'inheritance',
                        'warning',
                        'inheritance.missing_interface_method',
                        'Missing Interface Method',
                        "Class {$className} implements {$interface} but missing required method: {$method}",
                        "Implement the required method: public function {$method}()",
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
        }
    }

    /**
     * Validate abstract class implementation
     */
    protected function validateAbstractImplementation(string $filePath, string $content, string $className, string $parentClass, int $lineNumber): void
    {
        // Common Laravel abstract classes and their requirements
        $knownAbstractClasses = [
            'Command' => ['handle'],
            'Job' => ['handle'],
            'Event' => [],
            'Listener' => ['handle'],
            'FormRequest' => ['authorize', 'rules'],
            'Middleware' => ['handle'],
            'ServiceProvider' => ['register'],
        ];
        
        $parentName = basename(str_replace('\\', '/', $parentClass));
        
        if (isset($knownAbstractClasses[$parentName])) {
            $requiredMethods = $knownAbstractClasses[$parentName];
            
            foreach ($requiredMethods as $method) {
                if (!preg_match("/function\s+{$method}/", $content)) {
                    $this->addIssue($this->createIssue(
                        $filePath,
                        $lineNumber,
                        'inheritance',
                        'error',
                        'inheritance.missing_abstract_method',
                        'Missing Abstract Method Implementation',
                        "Class {$className} extends {$parentClass} but missing required method: {$method}",
                        "Implement the required abstract method: public function {$method}()",
                        $this->getCodeContext($content, $lineNumber)
                    ));
                }
            }
        }
    }

    /**
     * Check for potential trait conflicts
     */
    protected function checkTraitConflicts(string $filePath, string $content, string $traitName, array $usedTraits, int $lineNumber): void
    {
        // Common trait method conflicts in Laravel
        $commonConflicts = [
            'Dispatchable' => ['dispatch'],
            'InteractsWithQueue' => ['delete', 'release', 'fail'],
            'Queueable' => ['onQueue', 'onConnection'],
            'SerializesModels' => ['getSerializedPropertyValue', 'getRestoredPropertyValue'],
        ];
        
        $traitBaseName = basename(str_replace('\\', '/', $traitName));
        
        if (isset($commonConflicts[$traitBaseName])) {
            $potentialConflicts = $commonConflicts[$traitBaseName];
            
            // Check if other traits might have the same methods
            foreach ($usedTraits as $otherTrait) {
                $otherBaseName = basename(str_replace('\\', '/', $otherTrait));
                if ($otherBaseName !== $traitBaseName && isset($commonConflicts[$otherBaseName])) {
                    $conflicts = array_intersect($potentialConflicts, $commonConflicts[$otherBaseName]);
                    
                    if (!empty($conflicts)) {
                        $this->addIssue($this->createIssue(
                            $filePath,
                            $lineNumber,
                            'inheritance',
                            'warning',
                            'inheritance.trait_method_conflict',
                            'Potential Trait Method Conflict',
                            "Traits {$traitBaseName} and {$otherBaseName} may have conflicting methods: " . implode(', ', $conflicts),
                            'Use trait conflict resolution syntax to specify which methods to use.',
                            $this->getCodeContext($content, $lineNumber)
                        ));
                    }
                }
            }
        }
    }

    /**
     * Check required interface methods
     */
    protected function checkRequiredInterfaceMethods(string $filePath, string $content, int $lineNumber): void
    {
        // This is a placeholder - in a real implementation, you'd parse the actual interface
        // For now, we'll check common Laravel patterns
        
        if (preg_match('/implements.*ShouldQueue/', $content) && 
            !preg_match('/function\s+handle/', $content)) {
            
            $this->addIssue($this->createIssue(
                $filePath,
                $lineNumber,
                'inheritance',
                'info',
                'inheritance.queueable_missing_handle',
                'Queueable Class Missing Handle Method',
                'Classes implementing ShouldQueue should typically have a handle method.',
                'Add a handle() method to define what happens when the job is processed.',
                $this->getCodeContext($content, $lineNumber)
            ));
        }
    }

    /**
     * Check abstract method implementation
     */
    protected function checkAbstractMethodImplementation(string $filePath, string $content, int $lineNumber): void
    {
        // Look for abstract method declarations that should be implemented
        if (preg_match('/abstract\s+function\s+(\w+)/', $content, $matches)) {
            $abstractMethod = $matches[1];
            
            // Check if there's a concrete implementation somewhere
            if (!preg_match("/public\s+function\s+{$abstractMethod}|protected\s+function\s+{$abstractMethod}/", $content)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'inheritance',
                    'error',
                    'inheritance.abstract_method_not_implemented',
                    'Abstract Method Not Implemented',
                    "Abstract method {$abstractMethod} is declared but not implemented.",
                    "Provide a concrete implementation for the abstract method {$abstractMethod}.",
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check trait method resolution
     */
    protected function checkTraitMethodResolution(string $filePath, string $content, int $lineNumber): void
    {
        // Check for proper trait conflict resolution syntax
        if (preg_match('/use\s+[\w\\\\]+\s*{([^}]+)}/', $content, $matches)) {
            $resolutionBlock = $matches[1];
            
            // Look for insteadof or as keywords
            if (!preg_match('/(insteadof|as)/', $resolutionBlock)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'inheritance',
                    'info',
                    'inheritance.empty_trait_resolution',
                    'Empty Trait Resolution Block',
                    'Trait usage block is empty. Consider removing it if no conflicts need resolution.',
                    'Either add conflict resolution syntax or remove the empty block.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check trait method implementations
     */
    protected function checkTraitMethodImplementations(string $filePath, string $content, array $traits): void
    {
        // This would check if trait methods are properly used
        // For now, just a basic check for common Laravel trait patterns
        
        foreach ($traits as $trait) {
            $traitName = basename(str_replace('\\', '/', $trait));
            
            // Check if SoftDeletes trait is used properly
            if ($traitName === 'SoftDeletes' && !preg_match('/\$dates.*deleted_at/', $content)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    1,
                    'inheritance',
                    'info',
                    'inheritance.softdeletes_missing_dates',
                    'SoftDeletes Trait Missing Dates Configuration',
                    'When using SoftDeletes trait, consider adding deleted_at to $dates array.',
                    'Add protected $dates = [\'deleted_at\'] to ensure proper date casting.',
                    ['context' => 'SoftDeletes trait usage']
                ));
            }
        }
    }

    /**
     * Check if method is potentially overriding a parent method
     */
    protected function isPotentialOverride(string $methodName, string $content): bool
    {
        // Common Laravel methods that are often overridden
        $commonOverrides = [
            'handle', 'authorize', 'rules', 'boot', 'register', 'render', 'toArray', 'toJson',
            'broadcastOn', 'broadcastAs', 'broadcastWith', 'via', 'toMail', 'toDatabase'
        ];
        
        return in_array($methodName, $commonOverrides) && 
               preg_match('/(extends|implements)/', $content);
    }

    /**
     * Validate method override
     */
    protected function validateMethodOverride(string $filePath, string $content, string $methodName, int $lineNumber): void
    {
        // Check for common override issues
        
        // 1. Missing parent call for certain methods
        $methodsRequiringParentCall = ['boot', 'register'];
        
        if (in_array($methodName, $methodsRequiringParentCall) && 
            !preg_match("/parent::{$methodName}\(/", $content)) {
            
            $this->addIssue($this->createIssue(
                $filePath,
                $lineNumber,
                'inheritance',
                'warning',
                'inheritance.missing_parent_call',
                'Missing Parent Method Call',
                "Method {$methodName} typically requires calling parent::{$methodName}().",
                "Consider adding parent::{$methodName}() if the parent implementation is needed.",
                $this->getCodeContext($content, $lineNumber)
            ));
        }
        
        // 2. Check method signature compatibility (basic check)
        if ($methodName === 'handle' && preg_match('/function\s+handle\s*\(\s*\)/', $content)) {
            // Commands and Jobs often need parameters
            if (preg_match('/extends\s+(Command|Job)/', $content)) {
                $this->addIssue($this->createIssue(
                    $filePath,
                    $lineNumber,
                    'inheritance',
                    'info',
                    'inheritance.handle_method_signature',
                    'Handle Method Signature',
                    'Handle method in Commands and Jobs might need specific parameters.',
                    'Ensure the handle method signature matches the expected interface.',
                    $this->getCodeContext($content, $lineNumber)
                ));
            }
        }
    }

    /**
     * Check for missing parent calls
     */
    protected function checkMissingParentCalls(string $filePath, string $content, int $lineNumber): void
    {
        // This is handled in validateMethodOverride for specific cases
        // Could be expanded for more comprehensive checking
    }
}
