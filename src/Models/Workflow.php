<?php

namespace Monzer\FilamentWorkflows\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Monzer\FilamentWorkflows\Utils\Utils;

class Workflow extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'logs' => 'array',
    ];

    const CONDITION_TYPE_NO_CONDITION_IS_REQUIRED = "no-condition-is-required";
    const CONDITION_TYPE_ALL_CONDITIONS_ARE_TRUE = "all-conditions-are-true";
    const CONDITION_TYPE_ANY_CONDITION_IS_TRUE = "any-condition-is-true";

    public function scopeScheduled(Builder $q): Builder
    {
        return $q->where('type', 'scheduled');
    }

    public function scopeCustomEvent(Builder $q): Builder
    {
        return $q->where('type', 'custom_event');
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkflowGroup::class, 'workflow_group_id');
    }

    public function conditions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkflowCondition::class);
    }

    public function actions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkflowAction::class)->orderBy('sort');
    }

    public function executions(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(WorkflowActionExecution::class, WorkflowAction::class);
    }

    public function getActionsStatementAttribute(): string
    {
        $this->loadMissing('actions');
        return implode(', ', str_replace('-', ' ', $this->actions->pluck('action')->toArray()));
    }

    public function getStatementAttribute(): string
    {
        $this->loadMissing('conditions');

        if ($this->conditions->isEmpty())
            return "";

        $description = "When " . class_basename($this->model_type) . " " . $this->model_attribute . " " . $this->model_event;

        if ($this->conditions->isNotEmpty()) {
            $description = $description . ", with extra conditions";
        } else {
            $description = $description . ", no condition is required";
        }

        return $description;
    }

    public function log(string $log): bool
    {
        return Utils::log($this, $log);
    }

    public function clearLogs(): bool
    {
        return $this->update(['logs' => []]);
    }

    public function deactivate(): bool
    {
        return $this->update(['active' => false]);
    }
}
