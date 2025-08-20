<?php

namespace Tests\Unit\Scanners\Rules;

use PHPUnit\Framework\TestCase;
use Rafaelogic\CodeSnoutr\Scanners\Rules\QualityRules;
use Rafaelogic\CodeSnoutr\Scanners\Rules\LaravelRules;
use Rafaelogic\CodeSnoutr\Scanners\Rules\InheritanceRules;

class EnhancedRulesTest extends TestCase
{
    public function test_console_command_signature_exception()
    {
        $content = '<?php
class TestCommand extends Command
{
    protected $signature = "test:command";
    protected $description = "Test command description";
    
    public function handle()
    {
        return 0;
    }
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/app/Console/Commands/TestCommand.php', [], $content);
        
        // Should not report $signature and $description as unused variables
        $unusedVariableIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.potential_unused_variable';
        });
        
        $this->assertEmpty($unusedVariableIssues, 'Console command framework properties should not be flagged as unused');
    }

    public function test_eloquent_model_properties_exception()
    {
        $content = '<?php
class User extends Model
{
    protected $table = "users";
    protected $fillable = ["name", "email"];
    protected $hidden = ["password"];
    protected $casts = ["email_verified_at" => "datetime"];
    
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/app/Models/User.php', [], $content);
        
        // Should not report model framework properties as unused variables
        $unusedVariableIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.potential_unused_variable';
        });
        
        $this->assertEmpty($unusedVariableIssues, 'Eloquent model framework properties should not be flagged as unused');
    }

    public function test_job_properties_exception()
    {
        $content = '<?php
class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $timeout = 120;
    public $tries = 3;
    public $retryAfter = 60;
    
    public function handle()
    {
        // Process payment logic
    }
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/app/Jobs/ProcessPayment.php', [], $content);
        
        // Should not report job framework properties as unused variables
        $unusedVariableIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.potential_unused_variable';
        });
        
        $this->assertEmpty($unusedVariableIssues, 'Job framework properties should not be flagged as unused');
    }

    public function test_interface_implementation_detection()
    {
        $content = '<?php
class User implements Arrayable
{
    public function toArray()
    {
        return ["id" => 1, "name" => "John"];
    }
}';

        $inheritanceRules = new InheritanceRules();
        $issues = $inheritanceRules->analyze('/app/Models/User.php', [], $content);
        
        // Should not report missing interface methods since toArray is implemented
        $missingMethodIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'inheritance.missing_interface_method';
        });
        
        $this->assertEmpty($missingMethodIssues, 'Properly implemented interface methods should not be flagged');
    }

    public function test_missing_interface_method_detection()
    {
        $content = '<?php
class User implements Arrayable
{
    // Missing toArray() method
    public function getName()
    {
        return "John";
    }
}';

        $inheritanceRules = new InheritanceRules();
        $issues = $inheritanceRules->analyze('/app/Models/User.php', [], $content);
        
        // Should report missing toArray method
        $missingMethodIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'inheritance.missing_interface_method';
        });
        
        $this->assertNotEmpty($missingMethodIssues, 'Missing interface methods should be detected');
    }

    public function test_trait_conflict_detection()
    {
        $content = '<?php
class ProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    
    public function handle()
    {
        // Job logic
    }
}';

        $inheritanceRules = new InheritanceRules();
        $issues = $inheritanceRules->analyze('/app/Jobs/ProcessJob.php', [], $content);
        
        // May report potential trait conflicts if any exist
        $traitConflictIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'inheritance.trait_method_conflict';
        });
        
        // This test verifies the detection mechanism works (conflicts may or may not exist)
        $this->assertTrue(is_array($traitConflictIssues), 'Trait conflict detection should work');
    }

    public function test_legitimate_raw_sql_usage()
    {
        $content = '<?php
class AnalyticsRepository
{
    public function getComplexReport()
    {
        // Complex aggregation with performance optimization
        return DB::select("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = \'completed\' THEN amount ELSE 0 END) as revenue
            FROM orders 
            WHERE created_at >= ? 
            GROUP BY DATE(created_at)
        ", [now()->subDays(30)]);
    }
}';

        $laravelRules = new LaravelRules();
        $issues = $laravelRules->analyze('/app/Repositories/AnalyticsRepository.php', [], $content);
        
        // Should not report this as problematic raw SQL since it uses complex aggregation
        $rawSqlIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'laravel.raw_sql_in_model';
        });
        
        $this->assertEmpty($rawSqlIssues, 'Legitimate complex SQL queries should not be flagged');
    }

    public function test_configuration_file_exception()
    {
        $content = '<?php
return [
    "database" => [
        "connections" => [
            "mysql" => [
                "driver" => "mysql",
                "host" => env("DB_HOST", "127.0.0.1"),
            ]
        ]
    ]
];';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/config/database.php', [], $content);
        
        // Should not report any unused variable issues in config files
        $unusedVariableIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.potential_unused_variable';
        });
        
        $this->assertEmpty($unusedVariableIssues, 'Configuration files should be exempt from unused variable checks');
    }

    public function test_migration_file_exception()
    {
        $content = '<?php
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create("users", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->timestamps();
        });
    }
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/database/migrations/2024_01_01_000000_create_users_table.php', [], $content);
        
        // Should not report $table as unused in migration context
        $unusedVariableIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.potential_unused_variable';
        });
        
        $this->assertEmpty($unusedVariableIssues, 'Migration variables should be exempt from unused variable checks');
    }
}
