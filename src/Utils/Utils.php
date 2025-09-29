<?php

namespace Monzer\FilamentWorkflows\Utils;

use Composer\InstalledVersions;
use Filament\Forms\Components\Component;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Monzer\FilamentWorkflows\Contracts\Action;
use Monzer\FilamentWorkflows\Models\Workflow;
use Monzer\FilamentWorkflows\Traits\TrackWorkflowModelEvents;

class Utils
{

    public static function getActions(): array
    {
        $runtime_actions = filament('filament-workflows')->getActions();
        $default_actions = config('workflows.actions');
        return array_unique(array_merge($default_actions, $runtime_actions));
    }

    public static function getActionsForSelect(): array
    {
        $data = [];
        foreach (self::getActions() as $act) {
            $action = app()->make($act);
            $data[$action->getId()] = $action->getName();
        }
        return $data;
    }

    public static function getAction($id): ?Action
    {
        $actions = self::getActions();
        foreach ($actions as $action) {
            $obj = app()->make($action);
            if ($obj->getId() === $id) {
                return $obj;
            }
        }
        return null;
    }

    public static function log(Workflow $workflow, $log): bool
    {
        $logs = $workflow->logs ?? [];
        $logs[] = $log;
        
        // Implement log rotation to prevent database overflow
        $maxLogEntries = config('workflows.max_log_entries', 100);
        if ($maxLogEntries !== null && count($logs) > $maxLogEntries) {
            // Keep only the most recent entries
            $logs = array_slice($logs, -$maxLogEntries);
        }
        
        return $workflow->update(['logs' => $logs]);
    }

    public static function getFormattedDate(): string
    {
        return now()->format('Y, j F, g:i a');
    }

    public static function classUses($trait, $class): bool
    {
        return in_array($trait, class_uses_recursive($class));
    }

    public static function getNotifiableRelations(string $modelClass): array
    {
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException("Class {$modelClass} must be a Laravel Model");
        }

        $notifiableRelations = [];
        $model = new $modelClass();

        // Get all public methods
        $reflector = new \ReflectionClass($modelClass);
        $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            // Skip methods with parameters
            if ($method->getNumberOfParameters() > 0) {
                continue;
            }

            try {
                // Get the return type
                $returnType = $method->getReturnType();
                if (!$returnType) {
                    continue;
                }


                $returnTypeName = $returnType->getName();

                // Check if it's a relation
                if (!is_subclass_of($returnTypeName, Relation::class)) {
                    continue;
                }

                // Create a fresh instance and get the related model
                $relation = $method->invoke($model);
                if (!$relation instanceof Relation) {
                    continue;
                }

                $relatedModel = $relation->getRelated();
                $relatedClass = get_class($relatedModel);

                // Check for Notifiable trait in the related model
                if (in_array(Notifiable::class, class_uses_recursive($relatedClass))) {
                    $notifiableRelations[$method->getName()] = class_basename($relatedClass) . "->" . $method->getName();
                }
            } catch (\Exception $e) {
                // Skip any errors and continue with next method
                continue;
            }
        }
        return $notifiableRelations;
    }

    protected static function getModelRelations(Model $model): array
    {
        $relations = [];
        $reflector = new \ReflectionClass($model);

        foreach ($reflector->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // Skip methods that have parameters
            if ($method->getNumberOfParameters() > 0) {
                continue;
            }

            try {
                $returnType = $method->getReturnType();
                if ($returnType) {
                    $returnTypeName = $returnType->getName();

                    // Check if the return type is a Relation
                    if (is_subclass_of($returnTypeName, Relation::class)) {
                        $relations[$method->getName()] = $returnTypeName;
                    }
                } else {
                    // For methods without return type hints, try to call them
                    $result = $method->invoke($model);
                    if ($result instanceof Relation) {
                        $relations[$method->getName()] = get_class($result);
                    }
                }
            } catch (\Exception $e) {
                continue; // Skip if there's an error
            }
        }

        return $relations;
    }

    public static function listTriggers($asSelect = true): array
    {
        $data = [];
        $models_that_uses_track_workflow_model_events_trait = self::listModelsThatUses(TrackWorkflowModelEvents::class);

        if ($asSelect) {
            foreach ($models_that_uses_track_workflow_model_events_trait as $model_class) {
                $data[$model_class] = str(class_basename($model_class))->kebab()->replace('-', ' ')->title()->value();
            }
        } else {
            $data = $models_that_uses_track_workflow_model_events_trait;
        }
        return $data;
    }

    public static function listEvents($forSelect = true): array
    {
        if (!file_exists(app_path() . "/Events"))
            return [];

        $data = [];
        $classes = [];
        foreach (scandir(app_path() . "/Events") as $file) {
            if (Str::endsWith($file, '.php')) {
                $class = "App/Events/$file";
                $class = Str::replace(['../', '.php'], '', $class);
                $class = Str::replace(['/'], "\\", $class);
                $classes[] = $class;
            }
        }

        if (!$forSelect) {
            return $classes;
        }

        foreach ($classes as $class) {
            $data[$class] = str(class_basename($class))->kebab()->replace('-', ' ')->title()->value();
        }

        return $data;
    }

    public static function listModelsThatUses($trait): array
    {
        $classes = [];
        $modelDirectories = config('workflows.models_directory', ['App\\Models']);

        foreach ($modelDirectories as $directory) {
            // Convert namespace to directory path
            $directoryPath = str_replace('\\', '/', $directory);
            $directoryPath = app_path() . '/' . str_replace('App/', '', $directoryPath);

            if (!is_dir($directoryPath)) {
                continue;
            }

            $classes = array_merge($classes, self::scanDirectoryRecursively($directoryPath, $directory, $trait));
        }

        return $classes;
    }

    private static function scanDirectoryRecursively($directoryPath, $namespace, $trait): array
    {
        $classes = [];

        foreach (scandir($directoryPath) as $item) {
            // Skip dot directories
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $directoryPath . '/' . $item;

            // If it's a directory, recursively scan it
            if (is_dir($fullPath)) {
                $subNamespace = $namespace . '\\' . $item;
                $classes = array_merge($classes, self::scanDirectoryRecursively($fullPath, $subNamespace, $trait));
            }
            // If it's a PHP file, check if it uses the trait
            elseif (Str::endsWith($item, '.php')) {
                $className = $namespace . '\\' . basename($item, '.php');

                if (class_exists($className) && in_array($trait, class_uses_recursive($className))) {
                    $classes[] = $className;
                }
            }
        }

        return $classes;
    }
    public static function getTriggerAttributes($trigger_class, $asSelect = true, $withMutated = false): array
    {
        $model = new ($trigger_class);

        $attributes = Schema::getColumnListing($model->getTable());

//        $attributes = array_diff($attributes, self::$hide_trigger_attributes);

        if ($withMutated)
            $attributes = array_merge($attributes, self::getModelMutatedAttributes($trigger_class));

        if ($asSelect) {
            $attributes = array_combine(array_values($attributes), array_values($attributes));
            foreach ($attributes as $key => $value) {
                $mutated = self::isModelAttributeMutated($trigger_class, $key) ? "- " : "";
                $attributes[$key] = str($mutated . $value)->replace('_', ' ')->ucfirst()->title()->value();
            }
        }

        return $attributes;
    }

    public static function isModelAttributeMutated($model_type, $attribute): bool
    {
        return in_array($attribute, self::getModelMutatedAttributes($model_type));
    }

    public static function getModelMutatedAttributes($model_type): array
    {
        return (new ($model_type))->getMutatedAttributes();
    }

    public static function getTableColumnType($model_class, $column, $ignoreException = true): ?string
    {
        $table = (new ($model_class))->getTable();
        if ($ignoreException) {
            try {
                return Schema::getColumnType($table, $column);
            } catch (\Exception $exception) {
                return null;
            }
        }
        return Schema::getColumnType($table, $column);
    }

    public static function getModelAttributesSuggestions($model_class): array|null
    {
        if (blank($model_class))
            return null;

        $data = [];

        foreach (self::getTriggerAttributes($model_class, false, true) as $attribute) {
            $data[] = "@$attribute@";
        }

        return $data;
    }

    public static function getCustomEventVarsSuggestions($attributes): array|null
    {
        $data = [];
        foreach ($attributes as $attribute) {
            $data[$attribute] = "@event->$attribute@";
        }
        return $data;
    }

    public static function processMagicAttributes(Action $action, Model $model, array $data): array
    {
        $magicAttributeFields = $action->getMagicAttributeFields();

        foreach ($data as $key => $value) {
            if (in_array($key, $magicAttributeFields)) {
                if (is_string($value)) {
                    $data[$key] = self::processMagicAttribute($model, $value);
                }
                if (is_array($value)) {
                    $arr_data = [];
                    foreach ($value as $k => $subValue) {
                        if (is_string($subValue)) {
                            $arr_data[$k] = self::processMagicAttribute($model, $subValue);
                        }
                    }
                    $data[$key] = $arr_data;

                }
            }
        }
        return $data;
    }

    public static function processMagicAttribute(Model $model, string $data): string
    {
        $segments = explode(' ', $data);
        foreach ($segments as $segment) {
            preg_match('~@(.*?)@~', $segment, $output);
            if (array_key_exists(1, $output)) {
                //index 0 = @no@, index 1 = no, index 0 = @relation->attribute@, index 1 = relation->attribute
                if (array_key_exists(0, $output) and array_key_exists(1, $output))
                    if (str($output[1])->contains('->')) {
                        $relation_attribute_arr = explode('->', $output[1]);
                        $data = str($data)->replace($output[0], $model->{$relation_attribute_arr[0]}->{$relation_attribute_arr[1]})->value();
                    } elseif (str($output[1])->contains('()')) {
                        $method = str($output[1])->remove('()')->value();
                        $data = str($data)->replace($output[0], $model->{$method}())->value();
                    } else {
                        $data = str($data)->replace($output[0], $model->{$output[1]})->value();
                    }
            }
        }
        return $data;
    }

    public static function testWorkflowConditionsMet(Workflow $workflow, $model, $model_changes = []): bool
    {
        $needs_condition_checking = $workflow->condition_type != Workflow::CONDITION_TYPE_NO_CONDITION_IS_REQUIRED;
        //Roles and conditions are not a factor, condition passes
        if (!$needs_condition_checking and $workflow->model_comparison === "any-attribute") {
            return true;
        }

        if ($workflow->model_comparison === "specified") {

            $attributeChanged = array_key_exists($workflow->model_attribute, $model_changes);

            if (!$attributeChanged)
                return false;
        }

        if ($workflow->condition_type == Workflow::CONDITION_TYPE_NO_CONDITION_IS_REQUIRED and $workflow->conditions->isEmpty()) {
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

            if (!$conditions_results[count($conditions_results) - 1]) {
                throw new \Exception("Condition '$condition->model_attribute' did not met.");
            }
        }

        if ($workflow->condition_type == Workflow::CONDITION_TYPE_ALL_CONDITIONS_ARE_TRUE) {
            $passes = !in_array(false, $conditions_results);
            return $passes;
        }

        if ($workflow->condition_type == Workflow::CONDITION_TYPE_ANY_CONDITION_IS_TRUE) {
            $passes = in_array(true, $conditions_results);
            return $passes;
        }

        return false;
    }

    /**
     * @return array<Component>
     */
    public static function extractComponents(array $components): array
    {
        $data = [];
        foreach ($components as $component) {
            if (count($component->getChildComponents()) > 0) {
                $data = array_merge($data, self::extractComponents($component->getChildComponents()));
            } else {
                $data[] = $component;
            }
        }
        return $data;
    }

    public static function isPackageInstalled(string $package): bool
    {
        return InstalledVersions::isInstalled($package);
    }

}
