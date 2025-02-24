<?php

namespace Monzer\FilamentWorkflows\Actions;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Monzer\FilamentWorkflows\Contracts\Action;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;
use Monzer\FilamentWorkflows\Utils\Utils;

class CreateRecord extends Action
{

    public function getName(): string
    {
        return 'Create record';
    }

    public function getId(): string
    {
        return 'create-record';
    }

    public function getFields(): array
    {
        return [
            Select::make('data.model_type')
                ->label("Record")
                ->live()
                ->required()
                ->options(Utils::listTriggers())
                ->afterStateUpdated(function ($state, Set $set) {
                    if($state){
                        $attributes = array_keys(Utils::getTriggerAttributes($state));
                        $attributes = array_fill_keys($attributes, null);
                        $set('data.attributes', $attributes);
                    }else{
                        $set('data.attributes', null);
                    }
                }),

            KeyValue::make('data.attributes')
                ->helperText("Supports magic attributes")
                ->required(),
        ];
    }

    public function getMagicAttributeFields(): array
    {
        return [
            'attributes',
        ];
    }
    public function execute(array $data, WorkflowActionExecution $actionExecution, ?Model $model, array $custom_event_data, array &$shared_data): void
    {
         ($data['model_type'])::create($data['attributes']);
         $actionExecution->log("Record created");
    }
}
