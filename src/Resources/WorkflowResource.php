<?php

namespace Monzer\FilamentWorkflows\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\HtmlString;
use Monzer\FilamentWorkflows;
use Monzer\FilamentWorkflows\Jobs\ExecuteModelEventWorkflow;
use Monzer\FilamentWorkflows\Resources\WorkflowResource\Pages;
use Monzer\FilamentWorkflows\Utils\Utils;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class WorkflowResource extends Resource
{
    protected static ?string $model = FilamentWorkflows\Models\Workflow::class;

    protected static ?string $recordTitleAttribute = "description";

    public static function getModelLabel(): string
    {
        return __('filament-workflows::workflows.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-workflows::workflows.plural_label');
    }

    public static function getNavigationIcon(): \Illuminate\Contracts\Support\Htmlable|string|null
    {
        return filament('filament-workflows')->getNavigationIcon();
    }

    public static function getNavigationSort(): ?int
    {
        return filament('filament-workflows')->getNavigationSort();
    }

    public static function getSlug(): string
    {
        return filament('filament-workflows')->getSlug();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return filament('filament-workflows')->getShouldRegisterNavigation();
    }

    public static function getNavigationGroup(): ?string
    {
        return filament('filament-workflows')->getNavigationGroup();
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make(__('filament-workflows::workflows.sections.description.label'))
                        ->collapsed(fn($context) => $context === "edit" or $context === "view")
                        ->description(__('filament-workflows::workflows.sections.description.description'))
                        ->collapsible()
                        ->schema([
                            Forms\Components\Textarea::make('description')
                                ->hiddenLabel()
                                ->placeholder(__('filament-workflows::workflows.sections.description.placeholder'))
                                ->required(),
                        ]),

                    Forms\Components\Section::make(__('filament-workflows::workflows.sections.type.label.workflow_type'))
                        ->description(__('filament-workflows::workflows.sections.type.description'))
                        ->collapsed(fn($context) => $context === "edit" or $context === "view")
                        ->collapsible()
                        ->schema([
                            Forms\Components\ToggleButtons::make('type')
                                ->hiddenLabel()
                                ->required()
                                ->inline()
                                ->live()
                                ->options([
                                    'scheduled' => __('filament-workflows::workflows.workflow.types.scheduled'),
                                    'model_event' => __('filament-workflows::workflows.workflow.types.model_event'),
                                    'custom_event' => __('filament-workflows::workflows.workflow.types.custom_event'),
                                ]),

                            Forms\Components\Toggle::make('run_once')
                                ->label(__('filament-workflows::workflows.form.run_once')),

                            Forms\Components\Section::make()
                                ->visible(fn(Forms\Get $get) => $get('type') === "scheduled")
                                ->schema([
                                    Forms\Components\Select::make('schedule_frequency')
                                        ->label(__('filament-workflows::workflows.schedule.frequency.label'))
                                        ->required()
                                        ->live()
                                        ->options([
                                            'everySecond' => __('filament-workflows::workflows.schedule.frequency.options.every_second'),
                                            'everyTwoSeconds' => __('filament-workflows::workflows.schedule.frequency.options.every_two_seconds'),
                                            'everyFiveSeconds' => __('filament-workflows::workflows.schedule.frequency.options.every_five_seconds'),
                                            'everyTenSeconds' => __('filament-workflows::workflows.schedule.frequency.options.every_ten_seconds'),
                                            'everyFifteenSeconds' => __('filament-workflows::workflows.schedule.frequency.options.every_fifteen_seconds'),
                                            'everyTwentySeconds' => __('filament-workflows::workflows.schedule.frequency.options.every_twenty_seconds'),
                                            'everyThirtySeconds' => __('filament-workflows::workflows.schedule.frequency.options.every_thirty_seconds'),
                                            'everyMinute' => __('filament-workflows::workflows.schedule.frequency.options.every_minute'),
                                            'everyTwoMinutes' => __('filament-workflows::workflows.schedule.frequency.options.every_two_minutes'),
                                            'everyThreeMinutes' => __('filament-workflows::workflows.schedule.frequency.options.every_three_minutes'),
                                            'everyFourMinutes' => __('filament-workflows::workflows.schedule.frequency.options.every_four_minutes'),
                                            'everyFiveMinutes' => __('filament-workflows::workflows.schedule.frequency.options.every_five_minutes'),
                                            'everyTenMinutes' => __('filament-workflows::workflows.schedule.frequency.options.every_ten_minutes'),
                                            'everyFifteenMinutes' => __('filament-workflows::workflows.schedule.frequency.options.every_fifteen_minutes'),
                                            'everyThirtyMinutes' => __('filament-workflows::workflows.schedule.frequency.options.every_thirty_minutes'),
                                            'hourly' => __('filament-workflows::workflows.schedule.frequency.options.hourly'),
                                            'everyTwoHours' => __('filament-workflows::workflows.schedule.frequency.options.every_two_hours'),
                                            'everyThreeHours' => __('filament-workflows::workflows.schedule.frequency.options.every_three_hours'),
                                            'everyFourHours' => __('filament-workflows::workflows.schedule.frequency.options.every_four_hours'),
                                            'everySixHours' => __('filament-workflows::workflows.schedule.frequency.options.every_six_hours'),
                                            'daily' => __('filament-workflows::workflows.schedule.frequency.options.daily'),
                                            'dailyAt' => __('filament-workflows::workflows.schedule.frequency.options.daily_at'),
                                            'twiceDaily' => __('filament-workflows::workflows.schedule.frequency.options.twice_daily'),
                                            'twiceDailyAt' => __('filament-workflows::workflows.schedule.frequency.options.twice_daily_at'),
                                            'weekly' => __('filament-workflows::workflows.schedule.frequency.options.weekly'),
                                            'weeklyOn' => __('filament-workflows::workflows.schedule.frequency.options.weekly_on'),
                                            'monthly' => __('filament-workflows::workflows.schedule.frequency.options.monthly'),
                                            'monthlyOn' => __('filament-workflows::workflows.schedule.frequency.options.monthly_on'),
                                            'twiceMonthly' => __('filament-workflows::workflows.schedule.frequency.options.twice_monthly'),
                                            'lastDayOfMonth' => __('filament-workflows::workflows.schedule.frequency.options.last_day_of_month'),
                                            'quarterly' => __('filament-workflows::workflows.schedule.frequency.options.quarterly'),
                                            'quarterlyOn' => __('filament-workflows::workflows.schedule.frequency.options.quarterly_on'),
                                            'yearly' => __('filament-workflows::workflows.schedule.frequency.options.yearly'),
                                            'yearlyOn' => __('filament-workflows::workflows.schedule.frequency.options.yearly_on'),
                                        ]),
                                    Forms\Components\TimePicker::make('schedule_daily_at')
                                        ->visible(fn(Forms\Get $get) => $get('schedule_frequency') === "daily")
                                        ->seconds(false)
                                        ->format('H:i')
                                        ->displayFormat('H:i')
                                        ->native()
                                        ->required(),

                                    TextInput::make('schedule_params')
                                        ->label(__('filament-workflows::workflows.schedule.frequency.label'))
                                        ->placeholder("12:00")
                                        ->helperText(__('filament-workflows::workflows.schedule.frequency.helper_text'))
                                        ->nullable(),
                                ])->columns(2),

                            Forms\Components\Section::make(__('filament-workflows::workflows.sections.workflow_custom_event'))
                                ->visible(fn(Forms\Get $get) => $get('type') === "custom_event")
                                ->schema([
                                    Forms\Components\Select::make('custom_event')
                                        ->label(__('filament-workflows::workflows.custom_event.label'))
                                        ->required()
                                        ->options(Utils::listEvents()),
                                ])->columns(2),

                            Forms\Components\Fieldset::make()
                                ->visible(fn(Forms\Get $get) => $get('type') === "model_event")
                                ->schema([
                                    Forms\Components\Grid::make(4)
                                        ->schema([
                                            Forms\Components\Select::make('model_type')
                                                ->label(__('filament-workflows::workflows.model.attributes.label'))
                                                ->live()
                                                ->required()
                                                ->options(Utils::listTriggers()),

                                            Forms\Components\Select::make('model_event')
                                                ->label(__('filament-workflows::workflows.event_type'))
                                                ->required()
                                                ->live()
                                                ->options([
                                                    'created' => __('filament-workflows::workflows.model.events.created'),
                                                    'updated' => __('filament-workflows::workflows.model.events.updated'),
                                                    'deleted' => __('filament-workflows::workflows.model.events.deleted'),
                                                ]),

                                            Forms\Components\Hidden::make('model_comparison')->default('any-attribute'),

                                            Forms\Components\Select::make('model_attribute')
                                                ->label(__('filament-workflows::workflows.model.attributes.updated'))
                                                ->visible(fn(Forms\Get $get) => $get('model_event') === "updated")
                                                ->default('any-attribute')
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    if ($state and $state === "any-attribute") {
                                                        $set('model_comparison', 'any-attribute');
                                                    }

                                                    if ($state and $state !== "any-attribute") {
                                                        $set('model_comparison', "specified");
                                                    }
                                                })
                                                ->options(function (Forms\Get $get) {
                                                    $model_class = $get('model_type');
                                                    if ($model_class) {
                                                        return array_merge(
                                                            [
                                                                'any-attribute' => '* ' . __('filament-workflows::workflows.model.attributes.any')
                                                            ],
                                                            Utils::getTriggerAttributes($model_class, true, true)
                                                        );
                                                    }
                                                }),

                                            Forms\Components\Select::make('condition_type')
                                                ->label(__('filament-workflows::workflows.condition_type'))
                                                ->live()
                                                ->required()
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    if ($state === "no-condition-is-required") {
                                                        $set('conditions', []);
                                                    }
                                                })
                                                ->options([
                                                    'no-condition-is-required' => __('filament-workflows::workflows.conditions.types.none'),
                                                    'all-conditions-are-true' => __('filament-workflows::workflows.conditions.types.all'),
                                                    'any-condition-is-true' => __('filament-workflows::workflows.conditions.types.any'),
                                                ]),
                                        ]),

                                    Forms\Components\Repeater::make('conditions')
                                        ->label(__('filament-workflows::workflows.conditions.label'))
                                        ->relationship('conditions')
                                        ->visible(fn(Forms\Get $get) => $get('condition_type') === "all-conditions-are-true" or $get('condition_type') === "any-condition-is-true")
                                        ->schema([
                                            Forms\Components\Select::make('model_attribute')
                                                ->label(__('filament-workflows::workflows.model.attributes.label'))
                                                ->live()
                                                ->required()
                                                ->options(function (Forms\Get $get) {
                                                    $model_class = $get('../../model_type');
                                                    if ($model_class) {
                                                        return Utils::getTriggerAttributes($model_class, true, true);
                                                    }
                                                    return [];
                                                }),
                                            Forms\Components\Select::make('operator')
                                                ->label(__('filament-workflows::workflows.form.operator'))
                                                ->required()
                                                ->options([
                                                    'is-equal-to' => __('filament-workflows::workflows.conditions.operators.equals'),
                                                    'is-not-equal-to' => __('filament-workflows::workflows.conditions.operators.not_equals'),
                                                    'equals-or-greater-than' => __('filament-workflows::workflows.conditions.operators.greater_equals'),
                                                    'equals-or-less-than' => __('filament-workflows::workflows.conditions.operators.less_equals'),
                                                    'greater-than' => __('filament-workflows::workflows.conditions.operators.greater'),
                                                    'less-than' => __('filament-workflows::workflows.conditions.operators.less'),
                                                ]),
                                            Forms\Components\TextInput::make('compare_value')
                                                ->label(__('filament-workflows::workflows.form.compare_value'))
                                                ->required()
                                                ->hint(function (Forms\Get $get) {
                                                    $model_class = $get('../../model_type');
                                                    $model_attribute = $get('model_attribute');
                                                    if ($model_class and $model_attribute) {
                                                        return Utils::getTableColumnType($model_class, $model_attribute);
                                                    }
                                                }),
                                        ])->columnSpan(2)->columns(3),
                                ]),
                        ]),

                    Forms\Components\Section::make(__('filament-workflows::workflows.sections.actions.label'))
                        ->id('workflow-actions-section')
                        ->collapsed(fn($context) => $context === "edit" or $context === "view")
                        ->description(__('filament-workflows::workflows.sections.actions.description'))
                        ->headerActions([
                            Forms\Components\Actions\Action::make('record_attributes')
                                ->visible(fn(Forms\Get $get) => in_array($get('type'), ['model_event', 'custom_event']))
                                ->icon('heroicon-o-question-mark-circle')
                                ->color('gray')
                                ->label(__('filament-workflows::workflows.actions.magic_attributes.label'))
                                ->fillForm(function (Forms\Get $get) {
                                    $trigger_type = $get('type');
                                    if ($trigger_type == "model_event") {
                                        return [
                                            'attributes' => Utils::getModelAttributesSuggestions($get('model_type'))
                                        ];
                                    }
                                    if ($trigger_type == "custom_event") {
                                        return [
                                            'attributes' => Utils::getCustomEventVarsSuggestions(['user_id', 'user_email', 'order_id'])
                                        ];
                                    }
                                    return [];
                                })
                                ->modalSubmitAction(false)
                                ->modalCancelAction(false)
                                ->form([
                                    Forms\Components\Repeater::make('attributes')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false)
                                        ->grid(4)
                                        ->simple(
                                            Forms\Components\TextInput::make('attribute')
                                                ->readOnly()
                                                ->required(),
                                        )
                                ]),
                        ])
                        ->collapsible()
                        ->schema([
                            Forms\Components\Repeater::make('actions')
                                ->hiddenLabel()
                                ->relationship('actions')
                                ->itemLabel(fn(array $state): ?string => $state['action'] ?? null)
                                ->schema([
                                    Forms\Components\Select::make('action')
                                        ->hiddenLabel()
                                        ->required()
                                        ->live()
                                        ->disableOptionWhen(function (Forms\Get $get, $value) {
                                            return match ($get('../../type')) {
                                                'scheduled' => !Utils::getAction($value)->canBeUsedWithScheduledWorkflows(),
                                                'model_event' => !Utils::getAction($value)->canBeUsedWithRecordEventWorkflows(),
                                                'custom_event' => !Utils::getAction($value)->canBeUsedWithCustomEventWorkflows(),
                                                default => false,
                                            };
                                        })
                                        ->suffixAction(
                                            Forms\Components\Actions\Action::make('config_action')
                                                ->disabled(fn($context) => $context === "view")
                                                ->label(fn(Forms\Get $get) => $get('action'))
                                                ->icon('heroicon-o-wrench-screwdriver')
                                                ->visible(fn(Forms\Get $get) => filled($get('action')))
                                                ->stickyModalHeader()
                                                ->stickyModalFooter()
                                                ->modalWidth(MaxWidth::SevenExtraLarge)
                                                ->mountUsing(function (Form $form, Forms\Get $get, $state) {
                                                    $form->fill();
                                                    foreach (Utils::extractComponents($form->getComponents(withHidden: true)) as $component) {
                                                        if (method_exists($component, 'getName'))
                                                            $component->state($get($component->getName()));
                                                    }
                                                })
                                                ->form(function (Forms\Get $get) {
                                                    $action = Utils::getAction($get('action'));
                                                    if ($action) {
                                                        $required_packages = $action->requireInstalledPackages();
                                                        $not_installed = [];
                                                        foreach ($required_packages as $required_package) {
                                                            if (!Utils::isPackageInstalled($required_package))
                                                                $not_installed[] = $required_package;
                                                        }
                                                        if (count($not_installed) > 0) {
                                                            return [
                                                                Forms\Components\Placeholder::make('note')
                                                                    ->hiddenLabel()
                                                                    ->columnSpanFull()
                                                                    ->content(new HtmlString("<p>The following packages are required to use this action:</p> " . implode(", ", $not_installed))),
                                                            ];
                                                        }
                                                    }
                                                    return $action->getFields();
                                                })
                                                ->action(function (array $data, FilamentWorkflows\Models\WorkflowAction $workflowAction): void {
                                                    $workflowAction->update($data);
                                                })
                                        )
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            foreach (collect(Utils::getAction($state)?->getFields() ?? []) as $field) {

                                                if (method_exists($field, 'getChildComponents')) {
                                                    foreach (Utils::extractComponents($field->getChildComponents()) as $component) {
                                                        if (method_exists($component, 'getName') and method_exists($component, 'getDefaultState')) {
                                                            try {
                                                                $set($component->getName(), $component->getDefaultState());
                                                            } catch (\Throwable $throwable) {
                                                            }
                                                        }
                                                    }
                                                }
                                                if (method_exists($field, 'getName') and method_exists($field, 'getDefaultState')) {
                                                    try {
                                                        $set($field->getName(), $field->getDefaultState());
                                                    } catch (\Throwable $throwable) {
                                                    }
                                                }
                                            }
                                        })
                                        ->options(Utils::getActionsForSelect()),
                                ])
                                ->minItems(1)
                                ->reorderable()
                                ->collapsible()
                                ->grid(2)
                                ->columns(1),
                        ]),

                ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()->schema([

                    Forms\Components\Section::make(__('filament-workflows::workflows.sections.grouping.label'))
                        ->schema([
                            Forms\Components\Select::make('workflow_group_id')
                                ->hiddenLabel()
                                ->required()
                                ->disabledOn('edit')
                                ->options(FilamentWorkflows\Models\WorkflowGroup::pluck('name', 'id'))
                                ->createOptionForm(function () use ($form) {
                                    return [
                                        Forms\Components\Section::make()->schema([
                                            TextInput::make('name')
                                                ->required(),
                                        ])
                                    ];
                                })
                                ->createOptionUsing(function ($data) {
                                    $id = filament()->getTenant()?->id;
                                    if(filled($id)){
                                        $data['team_id'] = $id;
                                    }
                                    $model = FilamentWorkflows\Models\WorkflowGroup::create($data);
                                    return $model->id;
                                })
                                ->createOptionAction(
                                    fn(Forms\Components\Actions\Action $action) => $action->modalWidth(MaxWidth::ExtraSmall),
                                )
                                ->searchable(),
                        ]),

                    Forms\Components\Section::make(__('filament-workflows::workflows.sections.status.label'))
                        ->description(__('filament-workflows::workflows.sections.status.description'))
                        ->schema([
                            Forms\Components\Toggle::make('active')
                                ->label(__('filament-workflows::workflows.form.active'))
                                ->default(1),
                        ]),
                ])
                    ->columnSpan(['lg' => 1]),

            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label(__('filament-workflows::workflows.sections.description.label'))
                    ->searchable()
                    ->description(fn($record) => $record->statement)
                    ->tooltip(fn($record) => $record->statement)
                    ->toggleable()
                    ->limit(60),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('filament-workflows::workflows.sections.type.label.workflow_type'))
                    ->toggleable()
                    ->badge()
                    ->getStateUsing(fn($record) => str($record->type)->replace('_', ' ')->title())
                    ->color(function ($record) {
                        return match ($record->type) {
                            'scheduled' => 'gray',
                            'model_event' => 'success',
                            'custom_event' => 'warning',
                            'default' => 'danger',
                        };
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('group.name')
                    ->label(__('filament-workflows::workflows.sections.grouping.label'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                Tables\Columns\TextColumn::make('actions')
                    ->label(__('filament-workflows::workflows.sections.actions.label'))
                    ->tooltip(fn($record) => $record->actions_statement)
                    ->toggleable()
                    ->getStateUsing(fn($record) => $record->actions_statement),

                Tables\Columns\TextColumn::make('executions_count')
                    ->label(__('filament-workflows::workflows.table.columns.executions_count'))
                    ->toggleable()
                    ->counts('executions'),

                Tables\Columns\TextColumn::make('last_execution')
                    ->label(__('filament-workflows::workflows.table.columns.last_execution'))
                    ->toggleable()
                    ->getStateUsing(function ($record) {
                        return $record->executions->last()?->created_at->diffForHumans();
                    }),

                Tables\Columns\ToggleColumn::make('active')
                    ->label(__('filament-workflows::workflows.sections.status.label'))
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('test')
                        ->visible(fn() => filament('filament-workflows')->isTestingEnabled())
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('warning')
                        ->label(__('filament-workflows::workflows.table.test.title'))
                        ->form(function (FilamentWorkflows\Models\Workflow $workflow) {
                            return [
                                Forms\Components\Section::make()->schema([
                                    Forms\Components\Placeholder::make('note')
                                        ->columnSpanFull()
                                        ->content(__('filament-workflows::workflows.table.test.note')),

                                    TextInput::make('description')
                                        ->label(__('filament-workflows::workflows.sections.description.label'))
                                        ->default($workflow->description)
                                        ->disabled(),

                                    TextInput::make('record_event')
                                        ->label(__('filament-workflows::workflows.model.events.label'))
                                        ->default($workflow->model_event)
                                        ->disabled(),

                                    TextInput::make('record_id')
                                        ->label(class_basename($workflow->model_type) . ' ' . __('filament-workflows::workflows.table.test.fields.record_id'))
                                        ->required(),

                                    Forms\Components\KeyValue::make('simulate_attributes_changes')
                                        ->label(__('filament-workflows::workflows.table.test.fields.simulate_attributes.label'))
                                        ->visible($workflow->model_event == "updated")
                                        ->helperText(__('filament-workflows::workflows.table.test.fields.simulate_attributes.help'))
                                        ->columnSpanFull()
                                        ->required(),

                                    Forms\Components\Checkbox::make('execute_actions')
                                        ->label(__('filament-workflows::workflows.table.test.fields.execute_actions'))
                                        ->columnSpanFull(),
                                ])->columns(2),
                            ];
                        })
                        ->action(function (FilamentWorkflows\Models\Workflow $workflow, array $data, Tables\Actions\Action $action) {
                            try {
                                $model = ($workflow->model_type)::findOrFail($data['record_id']);
                                $met = Utils::testWorkflowConditionsMet($workflow, $model, $data['simulate_attributes_changes'] ?? []);

                                if ($met) {
                                    Notification::make()
                                        ->title(__('filament-workflows::workflows.table.test.title'))
                                        ->body(__('filament-workflows::workflows.table.test.notifications.conditions_met'))
                                        ->success()
                                        ->persistent()
                                        ->send();

                                    if ($data['execute_actions']) {
                                        dispatch_sync(new ExecuteModelEventWorkflow($workflow, $model->id));

                                        Notification::make()
                                            ->title(__('filament-workflows::workflows.table.test.title'))
                                            ->body(__('filament-workflows::workflows.table.test.notifications.execution_complete'))
                                            ->success()
                                            ->persistent()
                                            ->send();
                                    }
                                } else {
                                    Notification::make()
                                        ->title(__('filament-workflows::workflows.table.test.title'))
                                        ->body(__('filament-workflows::workflows.table.test.notifications.conditions_not_met'))
                                        ->warning()
                                        ->persistent()
                                        ->send();
                                }
                            } catch (\Throwable $throwable) {
                                Notification::make()
                                    ->title(__('filament-workflows::workflows.table.test.title'))
                                    ->body($throwable->getMessage())
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }

                            $action->halt();
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->action(function (FilamentWorkflows\Models\Workflow $record) {
                            $record->executions()->delete();
                            $record->actions()->delete();
                            $record->conditions()->delete();
                            $record->delete();

                            Notification::make()
                                ->success()
                                ->title(__('filament-actions::delete.single.notifications.deleted.title'))
                                ->send();
                        })
                ])
            ])
            ->bulkActions([])
            ->deferLoading();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['group', 'conditions', 'Actions.executions', 'executions'])->latest();
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkflows::route('/'),
            'create' => Pages\CreateWorkflow::route('/create'),
            'edit' => Pages\EditWorkflow::route('/{record}/edit'),
            'view' => Pages\ViewWorkflow::route('/{record}'),
            'viewLogs' => Pages\ViewLogs::route('/{record}/view-logs'),
        ];
    }

    public static function canAccess(): bool
    {
        return filament(app(FilamentWorkflows\WorkflowsPlugin::class)->getId())->isAuthorized();
    }
}
