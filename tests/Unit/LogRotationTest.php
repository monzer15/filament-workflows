<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Monzer\FilamentWorkflows\Models\Workflow;
use Monzer\FilamentWorkflows\Utils\Utils;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Config;

class LogRotationTest extends TestCase
{
    use RefreshDatabase;
    
    protected function getPackageProviders($app)
    {
        return [
            \Monzer\FilamentWorkflows\FilamentWorkflowsServiceProvider::class,
        ];
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
    
    public function test_log_rotation_keeps_configured_number_of_entries()
    {
        // Set max log entries to 10 for testing
        Config::set('workflows.max_log_entries', 10);
        
        // Create a workflow
        $workflow = Workflow::create([
            'description' => 'Test workflow',
            'type' => 'scheduled',
            'condition_type' => 'no-condition-is-required',
            'active' => true,
        ]);
        
        // Add 15 log entries
        for ($i = 1; $i <= 15; $i++) {
            Utils::log($workflow, "Log entry #{$i}");
        }
        
        // Refresh the workflow
        $workflow->refresh();
        
        // Assert only 10 logs are kept
        $this->assertCount(10, $workflow->logs);
        
        // Assert we have the last 10 entries (6-15)
        $this->assertStringContainsString('#6', $workflow->logs[0]);
        $this->assertStringContainsString('#15', $workflow->logs[9]);
    }
    
    public function test_log_rotation_respects_null_config()
    {
        // Set max log entries to null (disable rotation)
        Config::set('workflows.max_log_entries', null);
        
        // Create a workflow
        $workflow = Workflow::create([
            'description' => 'Test workflow',
            'type' => 'scheduled',
            'condition_type' => 'no-condition-is-required',
            'active' => true,
        ]);
        
        // Add 150 log entries
        for ($i = 1; $i <= 150; $i++) {
            Utils::log($workflow, "Log entry #{$i}");
        }
        
        // Refresh the workflow
        $workflow->refresh();
        
        // Assert all 150 logs are kept when rotation is disabled
        $this->assertCount(150, $workflow->logs);
    }
    
    public function test_log_rotation_default_is_100()
    {
        // Don't set config, use default
        Config::offsetUnset('workflows.max_log_entries');
        
        // Create a workflow
        $workflow = Workflow::create([
            'description' => 'Test workflow',
            'type' => 'scheduled',
            'condition_type' => 'no-condition-is-required',
            'active' => true,
        ]);
        
        // Add 120 log entries
        for ($i = 1; $i <= 120; $i++) {
            Utils::log($workflow, "Log entry #{$i}");
        }
        
        // Refresh the workflow
        $workflow->refresh();
        
        // Assert default 100 logs are kept
        $this->assertCount(100, $workflow->logs);
        
        // Assert we have entries 21-120
        $this->assertStringContainsString('#21', $workflow->logs[0]);
        $this->assertStringContainsString('#120', $workflow->logs[99]);
    }
    
    public function test_logs_not_rotated_when_under_limit()
    {
        // Set max log entries to 50
        Config::set('workflows.max_log_entries', 50);
        
        // Create a workflow
        $workflow = Workflow::create([
            'description' => 'Test workflow',
            'type' => 'scheduled',
            'condition_type' => 'no-condition-is-required',
            'active' => true,
        ]);
        
        // Add only 30 log entries
        for ($i = 1; $i <= 30; $i++) {
            Utils::log($workflow, "Log entry #{$i}");
        }
        
        // Refresh the workflow
        $workflow->refresh();
        
        // Assert all 30 logs are kept
        $this->assertCount(30, $workflow->logs);
        
        // Assert we have all entries 1-30
        $this->assertStringContainsString('#1', $workflow->logs[0]);
        $this->assertStringContainsString('#30', $workflow->logs[29]);
    }
}