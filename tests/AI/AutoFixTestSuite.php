<?php

namespace Tests\AI;

use Tests\TestCase;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Services\AI\AutoFixService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Comprehensive AI Auto Fix Test Suite
 * 
 * This test suite validates AI fixes across different issue categories with
 * real-world scenarios and training data to ensure accuracy.
 */
class AutoFixTestSuite extends TestCase
{
    use RefreshDatabase;

    protected AutoFixService $autoFixService;
    protected string $testFilesPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->autoFixService = app(AutoFixService::class);
        $this->testFilesPath = storage_path('codesnoutr_test_files');
        
        // Create test directory
        if (!File::exists($this->testFilesPath)) {
            File::makeDirectory($this->testFilesPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (File::exists($this->testFilesPath)) {
            File::deleteDirectory($this->testFilesPath);
        }
        
        parent::tearDown();
    }

    /**
     * Test AI fix for long line (Quality - Coding Standards)
     */
    public function test_ai_fix_long_line_quality_issue()
    {
        $testCode = <<<'PHP'
<?php

class SubscriberFilter
{
    protected $builder;
    protected $request;

    public function getNearbyPlacesByLocale()
    {
        $locale = app()->getLocale() ?? 'en';
        return $this->nearbyPlaces()->where('locale', $locale)->orderBy('distance', 'asc')->with(['category', 'reviews'])->get();
    }
}
PHP;

        $expectedFix = <<<'PHP'
public function getNearbyPlacesByLocale()
{
    $locale = app()->getLocale() ?? 'en';
    return $this->nearbyPlaces()
        ->where('locale', $locale)
        ->orderBy('distance', 'asc')
        ->with(['category', 'reviews'])
        ->get();
}
PHP;

        $issue = $this->createTestIssue([
            'category' => 'quality',
            'severity' => 'info',
            'rule_name' => 'quality.long_line',
            'title' => 'Line Too Long',
            'description' => 'Line exceeds 120 characters which can hurt readability.',
            'suggestion' => 'Consider breaking the line into multiple shorter lines.',
            'line_number' => 11,
            'file_path' => $this->createTestPhpFile('long_line_test.php', $testCode)
        ]);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->assertNotNull($fix, 'AI should generate a fix for long line issue');
        $this->assertArrayHasKey('code', $fix);
        $this->assertArrayHasKey('explanation', $fix);
        $this->assertGreaterThan(0.7, $fix['confidence']);
        
        // Validate that the fix preserves functionality
        $this->assertStringContainsString('return', $fix['code']);
        $this->assertStringContainsString('->where(\'locale\', $locale)', $fix['code']);
        $this->assertStringContainsString('->get()', $fix['code']);
        
        // Validate that line breaking is applied
        $fixLines = explode("\n", $fix['code']);
        foreach ($fixLines as $line) {
            $this->assertLessThanOrEqual(120, strlen($line), 'Fixed lines should not exceed 120 characters');
        }
    }

    /**
     * Test AI fix for missing method docblock (Documentation)
     */
    public function test_ai_fix_missing_docblock_documentation_issue()
    {
        $testCode = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];

    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->email . ')';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }
}
PHP;

        $issue = $this->createTestIssue([
            'category' => 'quality',
            'severity' => 'info',
            'rule_name' => 'quality.missing_method_docblock',
            'title' => 'Missing Method Documentation',
            'description' => 'Method getFullNameAttribute lacks proper documentation.',
            'suggestion' => 'Add docblock with description and return type.',
            'line_number' => 11,
            'file_path' => $this->createTestPhpFile('missing_docblock_test.php', $testCode)
        ]);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->assertNotNull($fix, 'AI should generate a fix for missing docblock');
        $this->assertArrayHasKey('code', $fix);
        $this->assertEquals('insert', $fix['type']);
        
        // Validate docblock content
        $this->assertStringContainsString('/**', $fix['code']);
        $this->assertStringContainsString('*/', $fix['code']);
        $this->assertStringContainsString('@return', $fix['code']);
        $this->assertStringContainsString('string', $fix['code']);
    }

    /**
     * Test AI fix for Laravel validation issue
     */
    public function test_ai_fix_laravel_validation_issue()
    {
        $testCode = <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        return response()->json($user);
    }
}
PHP;

        $issue = $this->createTestIssue([
            'category' => 'laravel',
            'severity' => 'warning',
            'rule_name' => 'laravel.missing_validation',
            'title' => 'Missing Request Validation',
            'description' => 'User input is not validated before being stored.',
            'suggestion' => 'Add request validation using $request->validate() or Form Requests.',
            'line_number' => 11,
            'file_path' => $this->createTestPhpFile('validation_test.php', $testCode)
        ]);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->assertNotNull($fix, 'AI should generate a fix for validation issue');
        $this->assertStringContainsString('validate', $fix['code']);
        $this->assertStringContainsString('required', $fix['code']);
        $this->assertStringContainsString('email', $fix['code']);
    }

    /**
     * Test AI fix for security SQL injection issue
     */
    public function test_ai_fix_security_sql_injection_issue()
    {
        $testCode = <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $results = DB::select("SELECT * FROM posts WHERE title LIKE '%{$query}%'");
        
        return response()->json($results);
    }
}
PHP;

        $issue = $this->createTestIssue([
            'category' => 'security',
            'severity' => 'critical',
            'rule_name' => 'security.sql_injection',
            'title' => 'Potential SQL Injection',
            'description' => 'Raw SQL query with user input concatenation detected.',
            'suggestion' => 'Use parameter binding or query builder methods.',
            'line_number' => 12,
            'file_path' => $this->createTestPhpFile('sql_injection_test.php', $testCode)
        ]);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->assertNotNull($fix, 'AI should generate a fix for SQL injection');
        $this->assertStringContainsString('?', $fix['code'], 'Should use parameter binding');
        $this->assertStringNotContainsString('$query}%', $fix['code'], 'Should not concatenate user input');
    }

    /**
     * Test AI fix for performance N+1 query issue
     */
    public function test_ai_fix_performance_n_plus_one_issue()
    {
        $testCode = <<<'PHP'
<?php

namespace App\Http\Controllers;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        
        foreach ($posts as $post) {
            echo $post->user->name; // N+1 query issue
            echo $post->category->title;
        }
        
        return view('posts.index', compact('posts'));
    }
}
PHP;

        $issue = $this->createTestIssue([
            'category' => 'performance',
            'severity' => 'warning',
            'rule_name' => 'performance.n_plus_one',
            'title' => 'N+1 Query Problem',
            'description' => 'Potential N+1 query detected in loop accessing relationships.',
            'suggestion' => 'Use eager loading with with() method.',
            'line_number' => 9,
            'file_path' => $this->createTestPhpFile('n_plus_one_test.php', $testCode)
        ]);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->assertNotNull($fix, 'AI should generate a fix for N+1 query');
        $this->assertStringContainsString('with(', $fix['code']);
        $this->assertStringContainsString('user', $fix['code']);
        $this->assertStringContainsString('category', $fix['code']);
    }

    /**
     * Test AI fix for trailing whitespace issue
     */
    public function test_ai_fix_trailing_whitespace_issue()
    {
        $testCode = "<?php\n\nclass TestClass\n{\n    public function test()   \n    {\n        return 'hello';     \n    }\n}";

        $issue = $this->createTestIssue([
            'category' => 'quality',
            'severity' => 'info',
            'rule_name' => 'quality.trailing_whitespace',
            'title' => 'Trailing Whitespace',
            'description' => 'Line has trailing whitespace characters.',
            'suggestion' => 'Remove trailing whitespace for cleaner code.',
            'line_number' => 5,
            'file_path' => $this->createTestPhpFile('trailing_whitespace_test.php', $testCode)
        ]);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->assertNotNull($fix, 'AI should generate a fix for trailing whitespace');
        $this->assertStringNotContainsString('    }', $fix['code'] . ' ');
        $this->assertStringContainsString('}', $fix['code']);
    }

    /**
     * Test that AI preserves complex query functionality
     */
    public function test_ai_preserves_complex_query_functionality()
    {
        $testCode = <<<'PHP'
<?php

class ReportController
{
    public function getMonthlyReport()
    {
        return $this->orders()->whereHas('items', function($q) { $q->where('status', 'completed'); })->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->with(['customer', 'items.product'])->orderBy('total', 'desc')->get();
    }
}
PHP;

        $issue = $this->createTestIssue([
            'category' => 'quality',
            'severity' => 'info', 
            'rule_name' => 'quality.long_line',
            'title' => 'Line Too Long',
            'description' => 'Line exceeds 120 characters which can hurt readability.',
            'line_number' => 7,
            'file_path' => $this->createTestPhpFile('complex_query_test.php', $testCode)
        ]);

        $fix = $this->autoFixService->generateFix($issue);
        
        $this->assertNotNull($fix, 'AI should generate a fix for complex query');
        
        // Ensure all original method calls are preserved
        $this->assertStringContainsString('return', $fix['code']);
        $this->assertStringContainsString('->orders()', $fix['code']);
        $this->assertStringContainsString('->whereHas(', $fix['code']);
        $this->assertStringContainsString('->whereBetween(', $fix['code']);
        $this->assertStringContainsString('->with([', $fix['code']);
        $this->assertStringContainsString('->orderBy(', $fix['code']);
        $this->assertStringContainsString('->get()', $fix['code']);
        
        // Ensure no incorrect transformations
        $this->assertStringNotContainsString('->has(', $fix['code']); // Should not change whereHas to has
        $this->assertStringNotContainsString('->load(', $fix['code']); // Should not change with to load
    }

    /**
     * Helper method to create test issues
     */
    protected function createTestIssue(array $attributes): Issue
    {
        return Issue::factory()->create(array_merge([
            'scan_id' => 1,
            'file_path' => $attributes['file_path'] ?? $this->createTestPhpFile('test.php', '<?php echo "test";'),
            'line_number' => 1,
            'column_number' => 0,
            'category' => 'quality',
            'severity' => 'info',
            'rule_name' => 'test.rule',
            'title' => 'Test Issue',
            'description' => 'Test description',
            'suggestion' => 'Test suggestion',
            'context' => ['code' => ['<?php echo "test";']],
            'fixed' => false,
        ], $attributes));
    }

    /**
     * Helper method to create test files
     */
    protected function createTestPhpFile(string $filename, string $content): string
    {
        $filePath = $this->testFilesPath . '/' . $filename;
        File::put($filePath, $content);
        return $filePath;
    }

    /**
     * Test AI fix validation prevents incorrect changes
     */
    public function test_ai_validation_prevents_incorrect_changes()
    {
        $testCode = <<<'PHP'
<?php

class LocationService
{
    public function getNearbyPlacesByLocale()
    {
        $locale = app()->getLocale() ?? 'en';
        return $this->nearbyPlaces()->where('locale', $locale)->get();
    }
}
PHP;

        $issue = $this->createTestIssue([
            'category' => 'quality',
            'severity' => 'info',
            'rule_name' => 'quality.long_line', 
            'line_number' => 8,
            'file_path' => $this->createTestPhpFile('validation_prevention_test.php', $testCode)
        ]);

        // Mock an incorrect AI response (like the one you experienced)
        $incorrectFix = [
            'code' => "public function getNearbyPlacesByLocale()\n{\n    \$locale = app()->getLocale() ?? 'en';\n    \$this->nearbyPlaces()->with('locale')->get();\n}",
            'explanation' => 'Fixed line length',
            'confidence' => 0.8,
            'type' => 'replace'
        ];

        // The validation should catch this incorrect fix
        $reflectionMethod = new \ReflectionMethod($this->autoFixService, 'validateAiFixData');
        $reflectionMethod->setAccessible(true);
        $isValid = $reflectionMethod->invoke($this->autoFixService, $incorrectFix, $issue);

        $this->assertFalse($isValid, 'AI validation should reject fixes that remove return statements or change where() to with()');
    }
}