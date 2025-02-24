<?php

namespace Monzer\FilamentWorkflows\Listeners;

use Illuminate\Events\Dispatcher;
use Monzer\FilamentWorkflows\Jobs\ExecuteCustomEventWorkflow;
use Monzer\FilamentWorkflows\Models\Workflow;
use Monzer\FilamentWorkflows\Utils\Utils;

class WorkflowEventSubscriber
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen("App\Events\*", function (string $eventName, array $data) {
            foreach (Workflow::customEvent()->where('custom_event', $eventName)->get() as $workflow) {
                if ($workflow->run_once and $workflow->executions->count() > 0) {
                    Utils::log($workflow, Utils::getFormattedDate() . ", Workflow evaluator: workflow already ran, skipping.");
                    continue;
                }
                dispatch(new ExecuteCustomEventWorkflow($workflow, collect($data[0] ?? [])->toArray()));
            }
        });
    }
}
