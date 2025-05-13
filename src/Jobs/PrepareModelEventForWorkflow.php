<?php

namespace Monzer\FilamentWorkflows\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Monzer\FilamentWorkflows\Models\Workflow;
use Monzer\FilamentWorkflows\Utils\Utils;

class PrepareModelEventForWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Model $model;

    public string $model_event;

    public array $model_changes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model, $model_event, $model_changes = [])
    {
        $this->model = $model;
        $this->model_event = $model_event;
        $this->model_changes = $model_changes;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $workflows = Workflow::with(['conditions', 'actions'])
            ->where('model_type', get_class($this->model))
            ->where('model_event', $this->model_event)
            ->get();

        // check conditions

        foreach ($workflows as $workflow) {

            if (!$workflow->active) {
                Utils::log($workflow, Utils::getFormattedDate() . ", Workflow evaluator: skipped due to being inactive.");
                continue;
            }

            if($workflow->run_once and $workflow->executions->count() > 0){
                Utils::log($workflow, Utils::getFormattedDate() . ", Workflow evaluator: workflow already ran, skipping.");
                continue;
            }

            if ($this->conditionsMet($workflow)) {
                dispatch(new ExecuteModelEventWorkflow($workflow, $this->model->id));
            }

            Utils::log($workflow, Utils::getFormattedDate() . ", Workflow evaluator: finished, workflow #$workflow->id on trigger #$workflow->model_type #" . $this->model->id);

        }
    }

    protected function conditionsMet(Workflow $workflow): bool
    {
        $needs_condition_checking = $workflow->condition_type != Workflow::CONDITION_TYPE_NO_CONDITION_IS_REQUIRED;
        //Roles and conditions are not a factor, condition passes
        if (!$needs_condition_checking and $workflow->model_comparison === "any-attribute") {
            Utils::log($workflow, Utils::getFormattedDate() . ", Workflow evaluator: no conditions were required, workflow #$workflow->id on trigger #$workflow->model_type #" . $this->model->id);
            return true;
        }

        if ($workflow->model_comparison === "specified") {

            $attributeChanged = array_key_exists($workflow->model_attribute, $this->model_changes);//$this->model->isDirty($workflow->model_attribute);

            if ($attributeChanged) {
                Utils::log($workflow, Utils::getFormattedDate() . ", Workflow evaluator: model attribute (" . $workflow->model_attribute . ") was " . $this->model_event . " , workflow #$workflow->id on trigger #$workflow->model_type #" . $this->model->id);
            } else {
                Utils::log($workflow, Utils::getFormattedDate() . ", Workflow evaluator: model attribute (" . $workflow->model_attribute . ") was NOT " . $this->model_event . " , workflow #$workflow->id on trigger #$workflow->model_type #" . $this->model->id);
            }

            if (!$attributeChanged)
                return false;
        }

        if ($workflow->condition_type == Workflow::CONDITION_TYPE_NO_CONDITION_IS_REQUIRED and $workflow->conditions->isEmpty()) {
            Utils::log($workflow, Utils::getFormattedDate() . ", Workflow evaluator: no conditions were required, workflow #$workflow->id on trigger #$workflow->model_type #" . $this->model->id);
            return true;
        }

        //check conditions

        $conditions_results = [];

        foreach ($workflow->conditions as $condition) {
            $attribute = $this->model->{$condition->model_attribute};
            switch ($condition->operator) {
                case "is-equal-to":
                {
                    $conditions_results[] = $attribute instanceof Carbon ? $attribute->equalTo($condition->compare_value) : $attribute == $condition->compare_value;
                    break;
                }
                case "is-not-equal-to":
                {
                    $conditions_results[] = $attribute instanceof Carbon ? $attribute->notEqualTo($condition->compare_value) : $attribute != $condition->compare_value;
                    break;
                }
                case "equals-or-greater-than":
                {
                    $conditions_results[] = $attribute instanceof Carbon ? $attribute->greaterThanOrEqualTo($condition->compare_value) : $attribute >= $condition->compare_value;
                    break;
                }
                case "equals-or-less-than":
                {
                    $conditions_results[] = $attribute instanceof Carbon ? $attribute->lessThanOrEqualTo($condition->compare_value) : $attribute <= $condition->compare_value;
                    break;
                }
                case "greater-than":
                {
                    $conditions_results[] = $attribute instanceof Carbon ? $attribute->greaterThan($condition->compare_value) : $attribute > $condition->compare_value;
                    break;
                }
                case "less-than":
                {
                    $conditions_results[] = $attribute instanceof Carbon ? $attribute->lessThan($condition->compare_value) : $attribute < $condition->compare_value;
                    break;
                }
            }
        }

        if ($workflow->condition_type == Workflow::CONDITION_TYPE_ALL_CONDITIONS_ARE_TRUE) {
            $passes = !in_array(false, $conditions_results);

            if (!$passes) {
                Utils::log($workflow, Utils::getFormattedDate() . ", Workflow evaluator: some or all conditions were NOT met, workflow #$workflow->id on trigger #$workflow->model_type #" . $this->model->id);
            }
            return $passes;
        }

        if ($workflow->condition_type == Workflow::CONDITION_TYPE_ANY_CONDITION_IS_TRUE) {
            $passes = in_array(true, $conditions_results);
            if (!$passes) {
                Utils::log($workflow, Utils::getFormattedDate() . ", Workflow evaluator: NONE of the conditions were met, workflow #$workflow->id on trigger #$workflow->model_type #" . $this->model->id);
            }
            return $passes;
        }

        return false;
    }

}
