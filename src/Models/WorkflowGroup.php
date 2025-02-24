<?php

namespace Monzer\FilamentWorkflows\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowGroup extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class);
    }
}
