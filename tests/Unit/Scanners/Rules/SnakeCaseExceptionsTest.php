<?php

namespace Tests\Unit\Scanners\Rules;

use PHPUnit\Framework\TestCase;
use Rafaelogic\CodeSnoutr\Scanners\Rules\QualityRules;

class SnakeCaseExceptionsTest extends TestCase
{
    public function test_php_constant_assignment_exception()
    {
        $content = '<?php
function processCurl()
{
    $ch = curl_init();
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    
    return compact("header_size", "http_code", "content_type");
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/app/Http/Services/CurlService.php', [], $content);
        
        // Should not report snake_case variables when assigned from PHP constants
        $snakeCaseIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.snake_case_variable';
        });
        
        $this->assertEmpty($snakeCaseIssues, 'Variables assigned from PHP constants should not be flagged as snake_case violations');
    }

    public function test_php_function_assignment_exception()
    {
        $content = '<?php
function processData()
    $file_size = filesize($filename);
    $mime_type = mime_content_type($filename);
    $is_readable = is_readable($filename);
    $array_keys = array_keys($data);
    
    return [$file_size, $mime_type, $is_readable, $array_keys];
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/app/Utils/FileHelper.php', [], $content);
        
        // Should not report snake_case variables when assigned from PHP functions with snake_case names
        $snakeCaseIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.snake_case_variable';
        });
        
        $this->assertEmpty($snakeCaseIssues, 'Variables assigned from PHP functions should not be flagged as snake_case violations');
    }

    public function test_database_column_names_exception()
    {
        $content = '<?php
class User extends Model
{
    public function getFullName()
    {
        $first_name = $this->first_name;
        $last_name = $this->last_name;
        $created_at = $this->created_at;
        $updated_at = $this->updated_at;
        
        return $first_name . " " . $last_name;
    }
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/app/Models/User.php', [], $content);
        
        // Should not report snake_case variables for common database column names
        $snakeCaseIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.snake_case_variable';
        });
        
        $this->assertEmpty($snakeCaseIssues, 'Variables matching database column names should not be flagged as snake_case violations');
    }

    public function test_migration_context_exception()
    {
        $content = '<?php
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create("users", function (Blueprint $table) {
            $table->id();
            $user_name = $table->string("name");
            $email_verified = $table->timestamp("email_verified_at");
            $remember_token = $table->string("remember_token");
        });
    }
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/database/migrations/2024_01_01_000000_create_users_table.php', [], $content);
        
        // Should not report snake_case variables in migration context
        $snakeCaseIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.snake_case_variable';
        });
        
        $this->assertEmpty($snakeCaseIssues, 'Variables in migration files should not be flagged as snake_case violations');
    }

    public function test_environment_variable_exception()
    {
        $content = '<?php
function getConfig()
    $database_url = env("DATABASE_URL");
    $api_key = getenv("API_KEY");
    $server_name = $_SERVER["SERVER_NAME"];
    $user_agent = $_ENV["HTTP_USER_AGENT"];
    
    return compact("database_url", "api_key", "server_name", "user_agent");
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/config/helpers.php', [], $content);
        
        // Should not report snake_case variables when assigned from environment variables
        $snakeCaseIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.snake_case_variable';
        });
        
        $this->assertEmpty($snakeCaseIssues, 'Variables assigned from environment variables should not be flagged as snake_case violations');
    }

    public function test_api_response_exception()
    {
        $content = '<?php
function processApiResponse($json)
{
    $decoded_data = json_decode($json, true);
    $user_id = $decoded_data["user_id"];
    $access_token = $decoded_data["access_token"];
    $refresh_token = $decoded_data["refresh_token"];
    
    return [$user_id, $access_token, $refresh_token];
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/app/Services/ApiService.php', [], $content);
        
        // Should not report snake_case variables when working with API responses
        $snakeCaseIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.snake_case_variable';
        });
        
        $this->assertEmpty($snakeCaseIssues, 'Variables assigned from API responses should not be flagged as snake_case violations');
    }

    public function test_legitimate_snake_case_violation()
    {
        $content = '<?php
function badNaming()
{
    $user_name = "John Doe";  // This should be flagged - no legitimate reason
    $some_variable = 123;     // This should be flagged - no legitimate reason
    
    return $user_name . $some_variable;
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/app/Services/BadService.php', [], $content);
        
        // Should report snake_case variables when there's no legitimate reason
        $snakeCaseIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.snake_case_variable';
        });
        
        $this->assertCount(2, $snakeCaseIssues, 'Illegitimate snake_case variables should still be flagged');
    }

    public function test_test_file_exception()
    {
        $content = '<?php
class UserTest extends TestCase
{
    public function test_user_creation()
    {
        $test_data = ["name" => "John"];
        $expected_result = "success";
        $mock_service = $this->createMock(UserService::class);
        
        $this->assertEquals($expected_result, $actual_result);
    }
}';

        $qualityRules = new QualityRules();
        $issues = $qualityRules->analyze('/tests/Unit/UserTest.php', [], $content);
        
        // Should not report snake_case variables in test files with test-related prefixes
        $snakeCaseIssues = array_filter($issues, function($issue) {
            return $issue['rule_id'] === 'quality.snake_case_variable';
        });
        
        $this->assertEmpty($snakeCaseIssues, 'Test variables with descriptive snake_case names should not be flagged');
    }
}
