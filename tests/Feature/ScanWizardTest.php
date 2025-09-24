<?php

namespace Rafaelogic\CodeSnoutr\Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use Rafaelogic\CodeSnoutr\Models\Scan;
use Rafaelogic\CodeSnoutr\Models\Issue;
use Rafaelogic\CodeSnoutr\Livewire\ScanWizard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScanWizardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_start_new_scan_wizard()
    {
        $response = $this->get('/codesnoutr/scan');
        
        $response->assertStatus(200);
        $response->assertViewIs('codesnoutr::pages.scan-wizard.scan-wizard');
        $response->assertSee('New Scan');
    }

    /** @test */
    public function wizard_initializes_with_correct_default_values()
    {
        Livewire::test(ScanWizard::class)
            ->assertSet('currentStep', 1)
            ->assertSet('scanType', 'codebase')
            ->assertSet('targetPath', base_path())
            ->assertCount('selectedCategories', 0)
            ->assertSee('Scan Type');
    }

    /** @test */
    public function can_navigate_between_wizard_steps()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Start at step 1
        $component->assertSet('currentStep', 1);
        
        // Go to step 2
        $component->call('nextStep')
            ->assertSet('currentStep', 2);
            
        // Go back to step 1
        $component->call('previousStep')
            ->assertSet('currentStep', 1);
    }

    /** @test */
    public function can_select_different_scan_types()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Test file scan
        $component->call('setScanType', 'file')
            ->assertSet('scanType', 'file');
            
        // Test directory scan
        $component->call('setScanType', 'directory')
            ->assertSet('scanType', 'directory');
            
        // Test codebase scan
        $component->call('setScanType', 'codebase')
            ->assertSet('scanType', 'codebase');
    }

    /** @test */
    public function can_set_target_path_for_different_scan_types()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Test setting target path
        $testPath = '/path/to/test';
        $component->call('setTargetPath', $testPath)
            ->assertSet('targetPath', $testPath);
    }

    /** @test */
    public function can_browse_and_select_files()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Set to file scan type
        $component->call('setScanType', 'file');
        
        // Browse to a directory
        $appPath = base_path('app');
        if (is_dir($appPath)) {
            $component->call('browseDirectory', $appPath);
            
            // Check that directory contents are loaded
            $this->assertNotEmpty($component->get('directoryContents'));
        }
    }

    /** @test */
    public function can_select_scan_categories()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Go to categories step
        $component->call('goToStep', 2);
        
        // Select security category
        $component->call('toggleCategory', 'security')
            ->assertContains('selectedCategories', 'security');
            
        // Deselect security category
        $component->call('toggleCategory', 'security')
            ->assertNotContains('selectedCategories', 'security');
    }

    /** @test */
    public function can_configure_scan_options()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Go to options step
        $component->call('goToStep', 3);
        
        // Test setting max file size
        $component->set('maxFileSize', 5)
            ->assertSet('maxFileSize', 5);
            
        // Test setting timeout
        $component->set('scanTimeout', 600)
            ->assertSet('scanTimeout', 600);
    }

    /** @test */
    public function validates_required_fields_before_starting_scan()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Try to start scan without selecting categories
        $component->call('startScan')
            ->assertHasErrors(['selectedCategories']);
    }

    /** @test */
    public function can_start_scan_with_valid_configuration()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Configure scan
        $component->set('scanType', 'codebase')
            ->set('targetPath', base_path())
            ->set('selectedCategories', ['security', 'performance'])
            ->set('maxFileSize', 10)
            ->set('scanTimeout', 300);
            
        // Start scan
        $component->call('startScan')
            ->assertHasNoErrors()
            ->assertDispatched('scan-started');
            
        // Verify scan was created
        $this->assertDatabaseHas('codesnoutr_scans', [
            'type' => 'codebase',
            'target' => base_path(),
            'status' => 'running',
        ]);
    }

    /** @test */
    public function shows_scan_progress_after_starting()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Configure and start scan
        $component->set('scanType', 'codebase')
            ->set('targetPath', base_path())
            ->set('selectedCategories', ['security'])
            ->call('startScan');
            
        // Should show progress
        $component->assertSet('showProgress', true)
            ->assertSee('Scan in Progress');
    }

    /** @test */
    public function can_cancel_running_scan()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Start a scan
        $component->set('scanType', 'codebase')
            ->set('targetPath', base_path())
            ->set('selectedCategories', ['security'])
            ->call('startScan');
            
        // Cancel the scan
        $component->call('cancelScan')
            ->assertSet('showProgress', false)
            ->assertDispatched('scan-cancelled');
    }

    /** @test */
    public function can_reset_wizard_to_start_new_scan()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Configure wizard
        $component->set('currentStep', 3)
            ->set('scanType', 'file')
            ->set('selectedCategories', ['security', 'performance']);
            
        // Reset wizard
        $component->call('resetWizard')
            ->assertSet('currentStep', 1)
            ->assertSet('scanType', 'codebase')
            ->assertCount('selectedCategories', 0);
    }

    /** @test */
    public function wizard_step_validation_prevents_invalid_navigation()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Try to go to step 4 from step 1
        $component->call('goToStep', 4)
            ->assertSet('currentStep', 1); // Should stay at step 1
    }

    /** @test */
    public function shows_scan_suggestions_based_on_project_type()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Should show suggestions
        $component->assertSee('Suggested Scans');
        
        // Check that suggestions are loaded
        $suggestions = $component->get('scanSuggestions');
        $this->assertIsArray($suggestions);
    }

    /** @test */
    public function can_apply_scan_suggestion()
    {
        $component = Livewire::test(ScanWizard::class);
        
        // Apply a security scan suggestion
        $component->call('applySuggestion', [
            'type' => 'directory',
            'path' => 'app',
            'categories' => ['security']
        ]);
        
        $component->assertSet('scanType', 'directory')
            ->assertContains('selectedCategories', 'security');
    }
}