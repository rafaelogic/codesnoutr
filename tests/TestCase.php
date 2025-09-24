<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Livewire\LivewireServiceProvider::class,
            CodeSnoutrServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set app key for encryption
        config()->set('app.key', 'base64:'.base64_encode(
            \Illuminate\Encryption\Encrypter::generateKey('AES-256-CBC')
        ));

        // Configure CodeSnoutr for testing
        config()->set('codesnoutr.enabled', true);
        config()->set('codesnoutr.ai.enabled', false);
        config()->set('codesnoutr.scanning.excluded_paths', [
            'vendor',
            'node_modules',
            'tests',
        ]);
    }

    /**
     * Create a test file with the given content.
     */
    protected function createTestFile(string $content, string $filename = 'test.php'): string
    {
        $path = $this->getTestFilePath($filename);
        file_put_contents($path, $content);
        return $path;
    }

    /**
     * Get the path for a test file.
     */
    protected function getTestFilePath(string $filename): string
    {
        $directory = __DIR__ . '/fixtures/temp';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        return $directory . '/' . $filename;
    }

    /**
     * Clean up test files.
     */
    protected function tearDown(): void
    {
        $this->cleanupTestFiles();
        parent::tearDown();
    }

    /**
     * Remove temporary test files.
     */
    protected function cleanupTestFiles(): void
    {
        $directory = __DIR__ . '/fixtures/temp';
        if (is_dir($directory)) {
            $files = glob($directory . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
