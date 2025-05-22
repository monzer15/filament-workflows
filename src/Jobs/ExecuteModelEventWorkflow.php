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

class ExecuteModelEventWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Workflow $workflow;
    protected $model_id;

    protected WorkflowService $service;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Workflow $workflow, $model_id)
    {
        $this->workflow = $workflow;
        $this->model_id = $model_id;
        $this->service = WorkflowService::instance();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->runActions();
    }

    protected function runActions(): void
    {
        if ($this->workflow->actions->isEmpty()) {
            $this->service->log($this->workflow, Utils::getFormattedDate() . ", Workflow evaluator: no actions found, workflow #" . $this->workflow->id . " on trigger #" . $this->workflow->model_type . " #$this->model_id");
        }
        if (!$this->workflow->active) {
            Utils::log($this->workflow, Utils::getFormattedDate() . ", Workflow evaluator: skipped due to being inactive.");
            return;
        }

        $model = ($this->workflow->model_type)::find($this->model_id);

        $sharedData = [];

        foreach ($this->workflow->actions as $workflowAction) {
            $startTime = microtime(true);

            $action = Utils::getAction($workflowAction->action);

            $data = $this->mutateActionData($workflowAction);

            $exec = WorkflowActionExecution::create(
                [
                    'workflow_action_id' => $workflowAction->id,
                    'model_id' => $this->model_id,
                ]
            );

            $action->execute($data, $exec, $model, [], $sharedData);

            $exec->update(['execution_time' => microtime(true) - $startTime]);

        }
    }

    protected function mutateActionData(WorkflowAction $action): array
    {
        $model = ($this->workflow->model_type)::find($this->model_id);
        return Utils::processMagicAttributes(Utils::getAction($action->action), $model, $action->data);
    }

    public function failed($exception)
    {
        $this->service->log($this->workflow, Utils::getFormattedDate() . ", Workflow Failed: workflow #" . $this->workflow->id . " on trigger #" . $this->workflow->model_type . " #$this->model_id" . ",  " . $exception->getMessage());
    }
}
