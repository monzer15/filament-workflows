<?php

namespace Monzer\FilamentWorkflows\Contracts;

use Illuminate\Database\Eloquent\Model;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;

abstract class Action
{
    abstract public function getId(): string;
    abstract public function getName(): string;
    abstract public function getFields(): array;
    abstract public function execute(
        array $data,
        WorkflowActionExecution $actionExecution,
        ?Model $model,
        array $custom_event_data,
        array &$shared_data
    ): void;
    public function getMagicAttributeFields(): array
    {
        return [];
    }
    public function canBeUsedWithScheduledWorkflows(): bool
    {
        return true;
    }
    public function canBeUsedWithRecordEventWorkflows(): bool
    {
        return true;
    }
    public function canBeUsedWithCustomEventWorkflows(): bool
    {
        return true;
    }
    public function requireInstalledPackages(): array
    {
        return [];
    }
}
