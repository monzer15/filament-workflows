<?php

namespace Monzer\FilamentWorkflows\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowActionExecution extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'logs' => 'array',
        'meta' => 'array',
    ];

    public function workflowAction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkflowAction::class);
    }

    public function getModelTypeAttribute()
    {
        $this->loadMissing('workflowAction.workflow');
        return $this->workflowAction->workflow->model_type;
    }
    public function log(string $log): bool
    {
        $logs = $this->logs ?? [];
        $logs[] = $log;
        return $this->update(['logs' => $logs]);
    }

    public function bulkLog(array $logs): bool
    {
        return $this->update(['logs' => array_merge($this->logs ?? [], $logs)]);
    }
}
