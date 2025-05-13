<?php

namespace Monzer\FilamentWorkflows\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Monzer\FilamentWorkflows\Models\Workflow;
use Monzer\FilamentWorkflows\Utils\Utils;

class WorkflowService
{
    public static function instance(): self
    {
        return new self();
    }

    public function triggerTypeModelConditionsMet(Workflow $workflow, Model $model, $model_event, $model_changes = [], $log = true): bool
    {
        $needs_condition_checking = $workflow->condition_type != Workflow::CONDITION_TYPE_NO_CONDITION_IS_REQUIRED;
        //Roles and conditions are not a factor, condition passes
        if (!$needs_condition_checking and $workflow->model_comparison === "any-attribute") {
            $this->logIf($log, $workflow, Utils::getFormattedDate() . ", Workflow evaluator: no conditions were required, workflow #$workflow->id on trigger #$workflow->model_type #" . $model->id);
            return true;
        }

        if ($workflow->model_comparison === "specified") {

            $attributeChanged = array_key_exists($workflow->model_attribute, $model_changes);

            if ($attributeChanged) {
                $this->logIf($log, $workflow, Utils::getFormattedDate() . ", Workflow evaluator: model attribute (" . $workflow->model_attribute . ") was " . $model_event . " , workflow #$workflow->id on trigger #$workflow->model_type #" . $model->id);
            } else {
                $this->logIf($log, $workflow, Utils::getFormattedDate() . ", Workflow evaluator: model attribute (" . $workflow->model_attribute . ") was NOT " . $model_event . " , workflow #$workflow->id on trigger #$workflow->model_type #" . $model->id);
            }

            if (!$attributeChanged)
                return false;
        }

        if ($workflow->condition_type == Workflow::CONDITION_TYPE_NO_CONDITION_IS_REQUIRED and $workflow->conditions->isEmpty()) {
            $this->logIf($log, $workflow, Utils::getFormattedDate() . ", Workflow evaluator: no conditions were required, workflow #$workflow->id on trigger #$workflow->model_type #" . $model->id);
            return true;
        }

        //check conditions

        $conditions_results = [];

        foreach ($workflow->conditions as $condition) {
            $attribute = $model->{$condition->model_attribute};
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
                $this->logIf($log, $workflow, Utils::getFormattedDate() . ", Workflow evaluator: some or all conditions were NOT met, workflow #$workflow->id on trigger #$workflow->model_type #" . $model->id);
            }
            return $passes;
        }

        if ($workflow->condition_type == Workflow::CONDITION_TYPE_ANY_CONDITION_IS_TRUE) {
            $passes = in_array(true, $conditions_results);
            if (!$passes) {
                $this->logIf($log, $workflow, Utils::getFormattedDate() . ", Workflow evaluator: NONE of the conditions were met, workflow #$workflow->id on trigger #$workflow->model_type #" . $model->id);
            }
            return $passes;
        }

        return false;
    }

    public function log(Workflow $workflow, $log): void
    {
        $logs = $workflow->logs ?? [];
        $logs[] = $log;
        $workflow->update(['logs' => $logs]);
    }

    public function logIf(bool $condition, Workflow $workflow, $log): void
    {
        if ($condition) {
            $logs = $workflow->logs ?? [];
            $logs[] = $log;
            $workflow->update(['logs' => $logs]);
        }
    }
}
