<?php

// Test script to verify complex response processing
require_once 'vendor/autoload.php';

// Sample complex response like the one you shared
$sampleResponse = [
    "response" => [
        "explanation" => "Here are some Laravel security best practices with practical examples:",
        "code_examples" => [
            [
                "comment" => "Input Validation with Form Requests",
                "code" => "<?php\n\nnamespace App\\Http\\Requests;\n\nuse Illuminate\\Foundation\\Http\\FormRequest;\n\nclass CreateUserRequest extends FormRequest\n{\n    public function authorize()\n    {\n        return auth()->check();\n    }\n\n    public function rules()\n    {\n        return [\n            'name' => 'required|string|max:255',\n            'email' => 'required|email|unique:users',\n            'password' => 'required|min:8|confirmed',\n        ];\n    }\n}"
            ],
            [
                "comment" => "CSRF Protection in Forms",
                "code" => "<!-- In your Blade template -->\n<form method=\"POST\" action=\"{{ route('users.store') }}\">\n    @csrf\n    <input type=\"text\" name=\"name\" value=\"{{ old('name') }}\">\n    <input type=\"email\" name=\"email\" value=\"{{ old('email') }}\">\n    <input type=\"password\" name=\"password\">\n    <button type=\"submit\">Create User</button>\n</form>"
            ]
        ],
        "best_practices" => [
            "Always use Form Request classes for input validation",
            "Enable CSRF protection on all state-changing routes",
            "Use bcrypt or argon2 for password hashing",
            "Implement proper authorization policies",
            "Sanitize output to prevent XSS attacks"
        ]
    ]
];

// Test the processing
class TestProcessor {
    public function processComplexResponse($response): string
    {
        // Handle string response directly
        if (is_string($response)) {
            return $response;
        }
        
        // Start recursive processing
        $formattedResponse = $this->formatResponseRecursively($response);
        
        // Fallback to JSON if no structure matches
        if (empty($formattedResponse)) {
            $formattedResponse = "```json\n" . json_encode($response, JSON_PRETTY_PRINT) . "\n```";
        }
        
        return $formattedResponse;
    }
    
    protected function formatResponseRecursively($data, $level = 0)
    {
        $result = '';
        
        if (is_string($data)) {
            return $data . "\n\n";
        }
        
        if (!is_array($data)) {
            return '';
        }
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Handle common keys with special formatting
                switch (strtolower($key)) {
                    case 'explanation':
                    case 'response':
                    case 'message':
                    case 'text':
                    case 'content':
                        $result .= $value . "\n\n";
                        break;
                    case 'code':
                    case 'code_example':
                        $cleanCode = str_replace(['```php', '```', 'php\n'], '', $value);
                        $result .= "```php\n" . trim($cleanCode) . "\n```\n\n";
                        break;
                    case 'description':
                    case 'comment':
                    case 'title':
                        $result .= "**" . $value . "**\n\n";
                        break;
                    default:
                        // Auto-detect code blocks
                        if (strpos($value, '<?php') !== false || 
                            strpos($value, 'function') !== false ||
                            strpos($value, 'class ') !== false ||
                            strpos($value, '$') !== false && strlen($value) > 20) {
                            $cleanCode = str_replace(['```php', '```', 'php\n'], '', $value);
                            $result .= "```php\n" . trim($cleanCode) . "\n```\n\n";
                        } else {
                            $result .= $value . "\n\n";
                        }
                }
            } elseif (is_array($value)) {
                // Handle arrays with special formatting based on key names
                $keyLower = strtolower($key);
                
                if (in_array($keyLower, ['code_examples', 'examples'])) {
                    $result .= "## Code Examples\n\n";
                    foreach ($value as $example) {
                        $result .= $this->formatCodeExample($example);
                    }
                } elseif (in_array($keyLower, ['best_practices', 'practices', 'tips', 'recommendations'])) {
                    $result .= "## " . ucfirst(str_replace('_', ' ', $key)) . "\n\n";
                    foreach ($value as $item) {
                        if (is_string($item)) {
                            $result .= "• " . $item . "\n";
                        } elseif (is_array($item)) {
                            $nested = $this->formatResponseRecursively($item, $level + 1);
                            $result .= "• " . trim($nested) . "\n";
                        }
                    }
                    $result .= "\n";
                } else {
                    // For other arrays, recursively process
                    $nested = $this->formatResponseRecursively($value, $level + 1);
                    if (!empty($nested)) {
                        if ($level === 0 && is_string($key) && !is_numeric($key)) {
                            $result .= "## " . ucfirst(str_replace('_', ' ', $key)) . "\n\n";
                        }
                        $result .= $nested;
                    }
                }
            }
        }
        
        return $result;
    }
    
    protected function formatCodeExample($example)
    {
        $result = '';
        
        if (is_string($example)) {
            if (strpos($example, '<?php') !== false || strpos($example, 'function') !== false) {
                $cleanCode = str_replace(['```php', '```', 'php\n'], '', $example);
                $result .= "```php\n" . trim($cleanCode) . "\n```\n\n";
            } else {
                $result .= $example . "\n\n";
            }
        } elseif (is_array($example)) {
            // Handle structured code examples
            if (isset($example['comment']) || isset($example['description'])) {
                $desc = $example['comment'] ?? $example['description'] ?? '';
                $result .= "**" . $desc . "**\n\n";
            }
            
            if (isset($example['code'])) {
                $cleanCode = str_replace(['```php', '```', 'php\n'], '', $example['code']);
                $result .= "```php\n" . trim($cleanCode) . "\n```\n\n";
            }
            
            // Handle any other fields in the example
            foreach ($example as $subKey => $subValue) {
                if (!in_array($subKey, ['comment', 'description', 'code']) && is_string($subValue)) {
                    if (strpos($subValue, '<?php') !== false || strpos($subValue, 'function') !== false) {
                        $cleanCode = str_replace(['```php', '```', 'php\n'], '', $subValue);
                        $result .= "```php\n" . trim($cleanCode) . "\n```\n\n";
                    } else {
                        $result .= $subValue . "\n\n";
                    }
                }
            }
        }
        
        return $result;
    }
}

$processor = new TestProcessor();
$result = $processor->processComplexResponse($sampleResponse);

echo "=== PROCESSED RESPONSE ===\n";
echo $result;
echo "\n=== END ===\n";
