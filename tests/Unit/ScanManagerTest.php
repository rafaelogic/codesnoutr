<?php

namespace Tests\Unit;

use Tests\TestCase;
use Rafaelogic\CodeSnoutr\ScanManager;
use Rafaelogic\CodeSnoutr\Models\Scan;

class ScanManagerTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $manager = app(ScanManager::class);
        
        $this->assertInstanceOf(ScanManager::class, $manager);
    }

    /** @test */
    public function it_can_scan_a_file_with_security_issues()
    {
        $testFile = $this->createTestFile('<?php
class TestClass 
{
    public function vulnerableMethod($userInput)
    {
        // SQL injection vulnerability
        $sql = "SELECT * FROM users WHERE id = " . $userInput;
        
        // XSS vulnerability
        echo $userInput;
        
        return $sql;
    }
}');

        $manager = app(ScanManager::class);
        $result = $manager->scanFile($testFile);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('issues', $result);
        $this->assertGreaterThan(0, count($result['issues']));
        
        // Check for security issues
        $securityIssues = array_filter($result['issues'], function ($issue) {
            return $issue['category'] === 'security';
        });
        
        $this->assertGreaterThan(0, count($securityIssues));
    }

    /** @test */
    public function it_can_scan_a_directory()
    {
        // Create test directory structure
        $testDir = __DIR__ . '/../fixtures/temp/test-directory';
        mkdir($testDir, 0755, true);
        
        // Create test files
        file_put_contents($testDir . '/file1.php', '<?php echo "test";');
        file_put_contents($testDir . '/file2.php', '<?php $sql = "SELECT * FROM users WHERE id = " . $_GET["id"];');
        
        $manager = app(ScanManager::class);
        $result = $manager->scanDirectory($testDir);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('issues', $result);
        $this->assertArrayHasKey('files_scanned', $result);
        $this->assertEquals(2, $result['files_scanned']);
        
        // Cleanup
        unlink($testDir . '/file1.php');
        unlink($testDir . '/file2.php');
        rmdir($testDir);
    }

    /** @test */
    public function it_excludes_vendor_directories()
    {
        $testDir = __DIR__ . '/../fixtures/temp/test-project';
        $vendorDir = $testDir . '/vendor';
        mkdir($vendorDir, 0755, true);
        
        // Create file in vendor directory
        file_put_contents($vendorDir . '/composer.php', '<?php echo "vendor file";');
        
        $manager = app(ScanManager::class);
        $result = $manager->scanDirectory($testDir);

        $this->assertIsArray($result);
        // Should not scan vendor files
        $this->assertEquals(0, $result['files_scanned']);
        
        // Cleanup
        unlink($vendorDir . '/composer.php');
        rmdir($vendorDir);
        rmdir($testDir);
    }

    /** @test */
    public function it_can_save_scan_results_to_database()
    {
        $testFile = $this->createTestFile('<?php echo "test file";');
        
        $manager = app(ScanManager::class);
        $scan = $manager->createScan('file', $testFile, 'Test Scan');
        
        $this->assertInstanceOf(Scan::class, $scan);
        $this->assertEquals('file', $scan->type);
        $this->assertEquals($testFile, $scan->target);
        $this->assertEquals('Test Scan', $scan->name);
        $this->assertEquals('pending', $scan->status);
    }
}
