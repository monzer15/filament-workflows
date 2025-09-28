<?php

namespace Monzer\FilamentWorkflows\Resources\Workflow\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Monzer\FilamentWorkflows\Models\Workflow;
use Monzer\FilamentWorkflows\Models\WorkflowGroup;
use Monzer\FilamentWorkflows\Resources\Workflow\WorkflowResource;

class ListWorkflows extends ListRecords
{
    protected static string $resource = WorkflowResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $groups = WorkflowGroup::with(['workflows'])->whereHas('workflows')->get();

        $data = [];

        foreach ($groups as $group) {
            $data[$group->name] = Tab::make(str($group->name)->title())
                                     ->modifyQueryUsing(fn(Builder $query) => $query->where('workflow_group_id',
                                         $group->id))
                                     ->badge(Workflow::query()->where('workflow_group_id', $group->id)->count())
                                     ->badgeColor('success');
        }
        return array_merge(
            [
                'all' => Tab::make(__('filament-workflows::workflows.sections.grouping.all'))
                            ->badge(Workflow::count())
                            ->badgeColor('success')
            ], $data);
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
