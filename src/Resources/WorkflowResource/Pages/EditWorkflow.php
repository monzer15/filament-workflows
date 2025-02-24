<?php

namespace Monzer\FilamentWorkflows\Resources\WorkflowResource\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Monzer\FilamentWorkflows\Resources\WorkflowResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class EditWorkflow extends EditRecord
{
    protected static string $resource = WorkflowResource::class;

    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }
    protected function getActions(): array
    {
        return array_merge([
           Action::make('view_logs')
               ->label(__('filament-workflows::workflows.view_logs'))
               ->color('danger')
                ->url(fn() => WorkflowResource::getUrl('viewLogs', ['record' => $this->record->id])),
        ]);
    }

    public function getHeading(): string|Htmlable
    {
        return "Workflow #" . $this->record->id;
    }

    public function getSubheading(): string|Htmlable|null
    {
        $sub_heading = "#" . $this->record->description;
        $actions = implode(', ', str_replace('-', ' ', $this->record->actions->pluck('action')->toArray()));;
        return new HtmlString($sub_heading . "<br> <strong>$actions</strong");
    }
}
