<?php

namespace Monzer\FilamentWorkflows\Traits;

use Illuminate\Database\Eloquent\Model;
use Monzer\FilamentWorkflows\Jobs\PrepareModelEventForWorkflow;

trait TrackWorkflowModelEvents
{
    public static function bootTrackWorkflowModelEvents()
    {
        static::created(function (Model $model) {
            dispatch(new PrepareModelEventForWorkflow($model, 'created'));
        });

        static::updated(function (Model $model) {
            dispatch(new PrepareModelEventForWorkflow($model, 'updated', $model->getChanges()));
        });

        static::deleted(function (Model $model) {
            dispatch(new PrepareModelEventForWorkflow($model, 'deleted'));
        });
    }
}
