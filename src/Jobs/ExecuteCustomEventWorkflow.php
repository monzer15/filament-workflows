<?php

namespace Monzer\FilamentWorkflows\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Monzer\FilamentWorkflows\Models\Workflow;
use Monzer\FilamentWorkflows\Models\WorkflowAction;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;
use Monzer\FilamentWorkflows\Services\WorkflowService;
use Monzer\FilamentWorkflows\Utils\Utils;

class ExecuteCustomEventWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Workflow $workflow;

    protected WorkflowService $service;

    protected array $event_data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Workflow $workflow, array $event_data)
    {
        $this->workflow = $workflow;
        $this->event_data = $event_data;
        $this->service = WorkflowService::instance();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->workflow->actions->isEmpty()) {
            $this->service->log($this->workflow, Utils::getFormattedDate() . ", Workflow evaluator: no actions found, workflow #" . $this->workflow->id . " on trigger #" . $this->workflow->model_type . " #$this->model_id");
        }
        if (!$this->workflow->active) {
            Utils::log($this->workflow, Utils::getFormattedDate() . ", Workflow evaluator: skipped due to being inactive.");
            return;
        }

        $sharedData = [];

        foreach ($this->workflow->actions as $workflowAction) {
            $startTime = microtime(true);

            $action = Utils::getAction($workflowAction->action);

            $data = $workflowAction->data;

            $exec = WorkflowActionExecution::create(
                [
                    'workflow_action_id' => $workflowAction->id,
                    'model_id' => null,
                ]
            );

            $action->execute($data, $exec, null, $this->event_data, $sharedData);

            $exec->update(['execution_time' => microtime(true) - $startTime]);
        }
    }

    public function failed($exception)
    {
        $this->service->log($this->workflow, Utils::getFormattedDate() . ", Workflow Failed: workflow #" . $this->workflow->id . " on trigger #" . $this->workflow->custom_event);
    }
}
