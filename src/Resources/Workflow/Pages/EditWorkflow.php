<?php

namespace Monzer\FilamentWorkflows\Resources\Workflow\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Monzer\FilamentWorkflows\Resources\Workflow\WorkflowResource;

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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_logs')
                  ->label(__('filament-workflows::workflows.view_logs'))
                  ->color('danger')
                  ->url(fn() => WorkflowResource::getUrl('viewLogs', ['record' => $this->record->id])),
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return "Workflow #" . $this->record->id;
    }

    public function getSubheading(): string|Htmlable|null
    {
        $sub_heading = "#" . $this->record->description;
        $actions     = implode(', ', str_replace('-', ' ', $this->record->actions->pluck('action')->toArray()));
        return new HtmlString($sub_heading . "<br> <strong>$actions</strong>");
    }
}
