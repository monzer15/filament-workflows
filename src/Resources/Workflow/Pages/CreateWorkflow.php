<?php

namespace Monzer\FilamentWorkflows\Resources\Workflow\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use Monzer\FilamentWorkflows\Resources\Workflow\WorkflowResource;

class CreateWorkflow extends CreateRecord
{
    protected static string $resource = WorkflowResource::class;

    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
                    ->title($exception->getMessage())
                    ->danger()
                    ->send();
    }
}
