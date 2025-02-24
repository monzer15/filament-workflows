<?php

namespace Monzer\FilamentWorkflows\Actions;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Database\Eloquent\Model;
use Monzer\FilamentWorkflows\Contracts\Action;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;

class SendWebhook extends Action
{

    public function getName(): string
    {
        return 'Send webhook';
    }

    public function getId(): string
    {
        return 'send-webhook';
    }

    public function getFields(): array
    {
        return [
            TextInput::make('data.endpoint')
                ->helperText("Supports magic attributes")
                ->required(),

            ToggleButtons::make('data.method')
                ->inline()
                ->default('post')
                ->options([
                    'post' => 'POST',
                    'get' => 'GET',
                ])
                ->required(),

            KeyValue::make('data.data')
                ->helperText("Supports magic attributes")
                ->required(),
        ];
    }

    public function getMagicAttributeFields(): array
    {
        return [
            'endpoint',
            'data'
        ];
    }

    public function execute(array $data, WorkflowActionExecution $actionExecution, ?Model $model, array $custom_event_data, array &$shared_data): void
    {
        $endpoint_data = $data['data'] ?? [];

        try {

            if ($data['method'] === 'post') {
                $response = \Http::post($data['endpoint'], $endpoint_data);
            } else {
                $response = \Http::get($data['endpoint'], $endpoint_data);
            }

            if ($response->successful()) {
                $actionExecution->log("Execution #{$actionExecution->id}, send webhook success with code: " . $response->getStatusCode());
                $actionExecution->log("Execution #{$actionExecution->id}, body contents: " . $response->getBody()->getContents());
            } else {
                $actionExecution->log("Execution #{$actionExecution->id}, send webhook failed with reason: {$response->getBody()->getContents()}");
            }

        } catch (\Throwable $exception) {
            $actionExecution->bulkLog(["Execution #{$actionExecution->id}, Exception:" . $exception->getMessage(), "Execution #{$actionExecution->id}, Workflow deactivated due to error."]);
            $actionExecution->workflowAction->workflow->deactivate();
        }
    }
}
