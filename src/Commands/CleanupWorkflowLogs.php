<?php

namespace Monzer\FilamentWorkflows\Commands;

use Illuminate\Console\Command;
use Monzer\FilamentWorkflows\Models\Workflow;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;

class CleanupWorkflowLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflows:cleanup-logs 
                            {--limit= : Maximum number of log entries to keep (default: from config)}
                            {--dry-run : Show what would be cleaned without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up workflow logs to prevent database overflow';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit') ?? config('workflows.max_log_entries', 100);
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in dry-run mode. No changes will be made.');
        }
        
        $this->info("Cleaning up workflow logs (keeping last {$limit} entries)...");
        
        // Clean up workflow logs
        $workflows = Workflow::whereNotNull('logs')->get();
        $workflowsToClean = 0;
        $totalLogsRemoved = 0;
        
        foreach ($workflows as $workflow) {
            $logs = $workflow->logs ?? [];
            if (count($logs) > $limit) {
                $originalCount = count($logs);
                $newLogs = array_slice($logs, -$limit);
                $logsToRemove = $originalCount - count($newLogs);
                
                if (!$dryRun) {
                    $workflow->update(['logs' => $newLogs]);
                }
                
                $workflowsToClean++;
                $totalLogsRemoved += $logsToRemove;
                
                if ($this->output->isVerbose()) {
                    $this->info("  Workflow #{$workflow->id}: Removing {$logsToRemove} log entries");
                }
            }
        }
        
        // Clean up workflow action execution logs
        $executions = WorkflowActionExecution::whereNotNull('logs')->get();
        $executionsToClean = 0;
        
        foreach ($executions as $execution) {
            $logs = json_decode($execution->logs, true) ?? [];
            if (is_array($logs) && count($logs) > $limit) {
                $originalCount = count($logs);
                $newLogs = array_slice($logs, -$limit);
                
                if (!$dryRun) {
                    $execution->update(['logs' => json_encode($newLogs)]);
                }
                
                $executionsToClean++;
                
                if ($this->output->isVerbose()) {
                    $this->info("  Execution #{$execution->id}: Removing " . ($originalCount - count($newLogs)) . " log entries");
                }
            }
        }
        
        // Show summary
        $this->newLine();
        if ($dryRun) {
            $this->info('Dry-run summary:');
            $this->info("  Would clean {$workflowsToClean} workflows");
            $this->info("  Would remove {$totalLogsRemoved} total log entries");
            $this->info("  Would clean {$executionsToClean} workflow action executions");
            $this->info('Run without --dry-run to apply these changes.');
        } else {
            $this->info('Cleanup complete:');
            $this->info("  ✓ Cleaned {$workflowsToClean} workflows");
            $this->info("  ✓ Removed {$totalLogsRemoved} total log entries");
            $this->info("  ✓ Cleaned {$executionsToClean} workflow action executions");
        }
        
        return Command::SUCCESS;
    }
}