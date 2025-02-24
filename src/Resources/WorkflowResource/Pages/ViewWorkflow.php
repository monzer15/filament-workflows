<?php

namespace Monzer\FilamentWorkflows\Resources\WorkflowResource\Pages;

use Monzer\FilamentWorkflows\Resources\WorkflowResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ViewWorkflow extends ViewRecord
{
    protected static string $resource = WorkflowResource::class;


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

    protected function getActions(): array
    {
        return [
            EditAction::make(),
            Action::make()::make('view_logs')
                ->label(__('filament-workflows::workflows.view_logs'))
                ->color('danger')
                ->url(fn() => WorkflowResource::getUrl('viewLogs', ['record' => $this->record->id])),
        ];
    }
}
