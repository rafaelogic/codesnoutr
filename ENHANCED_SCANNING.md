# Enhanced Scanning Exceptions

This document outlines the enhanced scanning logic that intelligently handles Laravel framework patterns and inheritance structures to reduce false positives.

## Overview

The enhanced scanner now includes context-aware exception handling that recognizes legitimate Laravel framework patterns, inheritance implementations, and interface contracts. This significantly reduces false positive reports for unused variables and other quality issues.

## Key Enhancements

### 1. Console Command Exceptions

**Framework Properties Recognized:**
- `$signature` - Command signature definition
- `$description` - Command description
- `$hidden` - Command visibility in Artisan list
- `$name` - Alternative command name

**Example:**
```php
class ExampleCommand extends Command
{
    protected $signature = 'example:process'; // ✅ NOT flagged as unused
    protected $description = 'Process data';  // ✅ NOT flagged as unused
    
    public function handle() { /* ... */ }
}
```

### 2. Eloquent Model Exceptions

**Framework Properties Recognized:**
- `$table` - Table name
- `$primaryKey` - Primary key column
- `$timestamps` - Timestamp handling
- `$fillable` - Mass assignable attributes
- `$guarded` - Mass assignment protection
- `$hidden` - Hidden attributes in serialization
- `$visible` - Visible attributes in serialization
- `$casts` - Attribute casting
- `$dates` - Date attributes
- `$with` - Eager loading relationships
- And many more...

**Example:**
```php
class User extends Model
{
    protected $table = 'users';        // ✅ NOT flagged as unused
    protected $fillable = ['name'];    // ✅ NOT flagged as unused
    protected $hidden = ['password'];  // ✅ NOT flagged as unused
    protected $casts = ['active' => 'boolean']; // ✅ NOT flagged as unused
}
```

### 3. Job/Queue Exceptions

**Framework Properties Recognized:**
- `$tries` - Number of retry attempts
- `$timeout` - Job timeout duration
- `$retryAfter` - Retry delay
- `$maxExceptions` - Maximum exceptions before failure
- `$connection` - Queue connection
- `$queue` - Queue name
- `$backoff` - Backoff strategy

**Example:**
```php
class ProcessPayment implements ShouldQueue
{
    public $tries = 3;           // ✅ NOT flagged as unused
    public $timeout = 120;       // ✅ NOT flagged as unused
    public $retryAfter = 60;     // ✅ NOT flagged as unused
    
    public function handle() { /* ... */ }
}
```

### 4. Controller Exceptions

**Framework Properties Recognized:**
- `$middleware` - Controller middleware

**Example:**
```php
class UserController extends Controller
{
    protected $middleware = ['auth']; // ✅ NOT flagged as unused
    
    public function index() { /* ... */ }
}
```

### 5. Form Request Exceptions

**Framework Properties Recognized:**
- `$redirectRoute` - Redirect route on failure
- `$redirect` - Redirect URL on failure
- `$errorBag` - Error bag name

### 6. Event/Listener Exceptions

**Framework Properties Recognized:**
- `$broadcastOn` - Broadcast channels
- `$broadcastAs` - Broadcast event name
- `$broadcastWith` - Broadcast data

### 7. Service Provider Exceptions

**Framework Properties Recognized:**
- `$defer` - Deferred service provider
- `$provides` - Provided services

### 8. Special File Type Exceptions

**Configuration Files:**
- Files in `/config/` directory are exempt from unused variable checks
- Files ending with `.config.php` are exempt

**Migration Files:**
- Files in `/migrations/` directory with timestamp pattern are exempt
- Variables like `$table` and `$schema` are recognized

**Test Files:**
- Files extending `TestCase` or containing "Test" in path/name
- Test-specific properties are recognized

## Interface and Inheritance Validation

### 1. Interface Implementation Checking

The scanner now validates that classes properly implement required interface methods:

**Supported Interfaces:**
- `Arrayable` - requires `toArray()`
- `Jsonable` - requires `toJson()`
- `Responsable` - requires `toResponse()`
- `ShouldQueue` - validates structure
- `ShouldBroadcast` - requires `broadcastOn()`

**Example:**
```php
class User implements Arrayable
{
    public function toArray() // ✅ Properly implements interface
    {
        return ['id' => $this->id];
    }
}

class BadUser implements Arrayable
{
    // ❌ Missing toArray() method - will be flagged
}
```

### 2. Abstract Class Implementation

Validates that concrete classes implement required abstract methods:

**Example:**
```php
class MyCommand extends Command
{
    public function handle() // ✅ Required for Command classes
    {
        return 0;
    }
}
```

### 3. Trait Conflict Detection

Detects potential method conflicts when using multiple traits:

**Example:**
```php
class MyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable; // Checks for conflicts
}
```

## Enhanced Raw SQL Detection

The scanner now allows legitimate raw SQL usage in specific contexts:

### Allowed Raw SQL Patterns:

1. **Complex Aggregations:**
   ```php
   DB::select("SELECT COUNT(*), SUM(amount) FROM orders"); // ✅ Allowed
   ```

2. **Database-Specific Functions:**
   ```php
   DB::select("SELECT JSON_EXTRACT(data, '$.name') FROM users"); // ✅ Allowed
   ```

3. **Migration Files:**
   ```php
   // In migration files, raw SQL is allowed for schema operations
   ```

4. **Performance-Critical Operations:**
   ```php
   // Raw SQL with performance comment is allowed
   DB::select("/* performance optimization */ SELECT ...");
   ```

## Enhanced Snake Case Variable Detection

The scanner now includes intelligent exceptions for legitimate snake_case variable usage:

### Snake Case Exceptions:

1. **PHP Constants Assignment:**
   ```php
   $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); // ✅ NOT flagged
   $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);     // ✅ NOT flagged
   ```

2. **PHP Function Assignments:**
   ```php
   $file_size = filesize($filename);        // ✅ NOT flagged
   $mime_type = mime_content_type($file);   // ✅ NOT flagged
   $is_readable = is_readable($file);       // ✅ NOT flagged
   ```

3. **Database Column Names:**
   ```php
   $first_name = $user->first_name;         // ✅ NOT flagged
   $created_at = $model->created_at;        // ✅ NOT flagged
   $email_verified_at = $user->email_verified_at; // ✅ NOT flagged
   ```

4. **Environment Variables:**
   ```php
   $database_url = env('DATABASE_URL');     // ✅ NOT flagged
   $api_key = getenv('API_KEY');           // ✅ NOT flagged
   $server_name = $_SERVER['SERVER_NAME']; // ✅ NOT flagged
   ```

5. **API Response Data:**
   ```php
   $decoded_data = json_decode($response);  // ✅ NOT flagged
   $access_token = $data['access_token'];   // ✅ NOT flagged
   ```

6. **Migration/Schema Context:**
   ```php
   // In migration files, snake_case is expected
   Schema::create('users', function (Blueprint $table) {
       $user_name = $table->string('name'); // ✅ NOT flagged
   });
   ```

7. **Test Files:**
   ```php
   // In test files with test-related prefixes
   $test_data = ['name' => 'John'];         // ✅ NOT flagged
   $expected_result = 'success';            // ✅ NOT flagged
   $mock_service = $this->createMock();     // ✅ NOT flagged
   ```

### Still Flagged (Legitimate Violations):
```php
$user_name = 'John Doe';    // ❌ Should be $userName
$some_data = 'test';        // ❌ Should be $someData
```

## Configuration

The enhanced scanning can be configured through the `codesnoutr.php` config file:

```php
'enhanced_scanning' => [
    'enable_framework_exceptions' => true,
    'enable_inheritance_validation' => true,
    'enable_interface_checking' => true,
    'enable_trait_conflict_detection' => true,
    'strict_mode' => false, // When true, fewer exceptions are made
],
```

## Usage Examples

### Running Enhanced Scans

```php
// Include inheritance checking in your scan
$scanner->scan($path, ['quality', 'laravel', 'inheritance']);

// Quality rules now include enhanced exception handling
$scanner->scan($path, ['quality']); // Automatically applies exceptions
```

### Test Coverage

The enhancements include comprehensive test coverage:

```bash
# Run enhanced scanning tests
php artisan test tests/Unit/Scanners/Rules/EnhancedRulesTest.php
```

## Benefits

1. **Reduced False Positives:** Framework properties no longer flagged as unused
2. **Better Laravel Integration:** Understands Laravel conventions and patterns
3. **Inheritance Validation:** Ensures proper interface and abstract implementations
4. **Smarter SQL Detection:** Allows legitimate complex queries while flagging simple cases
5. **Context Awareness:** File type and class context influence scanning behavior

## Migration Guide

Existing scans will automatically benefit from these enhancements. No configuration changes are required, but you may want to:

1. Re-run existing scans to see reduced false positives
2. Enable inheritance checking by including 'inheritance' in scan categories
3. Review and adjust any custom rules that might conflict with new exceptions

## Future Enhancements

- Support for custom framework patterns
- More sophisticated trait conflict resolution
- Enhanced interface requirement detection
- Support for custom inheritance patterns
