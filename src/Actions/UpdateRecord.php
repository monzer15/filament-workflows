<?php

namespace Monzer\FilamentWorkflows\Actions;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Monzer\FilamentWorkflows\Contracts\Action;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;
use Monzer\FilamentWorkflows\Utils\Utils;

class UpdateRecord extends Action
{

    public function getName(): string
    {
        return 'Update record attribute';
    }

    public function getId(): string
    {
        return 'update-record-attributes';
    }

    public function getFields(): array
    {
        return [
            Select::make('data.attribute')
                ->required()
                ->options(function (Get $get, $livewire) {
                    $model_type = $livewire->data['model_type'];
                    if ($model_type) {
                        return Utils::getTriggerAttributes($model_type, true, true);
                    }
                    return [];
                }),

            TextInput::make('data.value')
                ->helperText("Supports magic attributes")
                ->required(),
        ];
    }

    public function getMagicAttributeFields(): array
    {
        return [
            'value'
        ];
    }

    public function execute(array $data, WorkflowActionExecution $actionExecution, ?Model $model, array $custom_event_data, array &$shared_data): void
    {
        $model->update([$data['attribute'] => $data['value']]);
        $actionExecution->log("Record updated");
    }

    public function canBeUsedWithScheduledWorkflows(): bool
    {
        return false;
    }
    public function canBeUsedWithCustomEventWorkflows(): bool
    {
        return false;
    }
}
