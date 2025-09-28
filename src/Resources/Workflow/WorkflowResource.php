<?php

namespace Monzer\FilamentWorkflows\Resources\Workflow;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Monzer\FilamentWorkflows\Jobs\ExecuteModelEventWorkflow;
use Monzer\FilamentWorkflows\Models\Workflow;
use Monzer\FilamentWorkflows\Models\WorkflowGroup;
use Monzer\FilamentWorkflows\Utils\Utils;
use Throwable;
use UnitEnum;

class WorkflowResource extends Resource
{
    protected static ?string                $model                = Workflow::class;
    protected static string|null|BackedEnum $navigationIcon       = 'heroicon-o-cog';
    protected static string|null|UnitEnum   $navigationGroup      = 'Workflows';
    protected static ?string                $recordTitleAttribute = 'description';

    public static function getModelLabel(): string
    {
        return __('filament-workflows::workflows.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-workflows::workflows.plural_label');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationSort(): ?int
    {
        return filament('filament-workflows')->getNavigationSort();
    }

//    public static function getSlug(?Panel $panel = null): string
//    {
//        return filament('filament-workflows')->getSlug();
//    }

    public static function shouldRegisterNavigation(): bool
    {
        return filament('filament-workflows')->getShouldRegisterNavigation();
    }

    public static function getNavigationGroup(): ?string
    {
        return filament('filament-workflows')->getNavigationGroup();
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['group', 'conditions', 'actions.executions', 'executions'])->latest();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                     ->schema([
                         Section::make(__('filament-workflows::workflows.sections.description.label'))
                                ->collapsed(fn($context) => $context === "edit" or $context === "view")
                                ->description(__('filament-workflows::workflows.sections.description.description'))
                                ->collapsible()
                                ->schema([
                                    Textarea::make('description')
                                            ->hiddenLabel()
                                            ->placeholder(__('filament-workflows::workflows.sections.description.placeholder'))
                                            ->required(),
                                ]),

                         Section::make(__('filament-workflows::workflows.sections.type.label.workflow_type'))
                                ->description(__('filament-workflows::workflows.sections.type.description'))
                                ->collapsed(fn($context) => $context === "edit" or $context === "view")
                                ->collapsible()
                                ->schema([
                                    ToggleButtons::make('type')
                                                 ->hiddenLabel()
                                                 ->required()
                                                 ->inline()
                                                 ->live()
                                                 ->options([
                                                     'scheduled'    => __('filament-workflows::workflows.workflow.types.scheduled'),
                                                     'model_event'  => __('filament-workflows::workflows.workflow.types.model_event'),
                                                     'custom_event' => __('filament-workflows::workflows.workflow.types.custom_event'),
                                                 ]),

                                    Toggle::make('run_once')
                                          ->label(__('filament-workflows::workflows.form.run_once')),

                                    Section::make()
                                           ->visible(fn(Get $get) => $get('type') === "scheduled")
                                           ->columnSpanFull()
                                           ->schema([
                                               Select::make('schedule_frequency')
                                                     ->label(__('filament-workflows::workflows.schedule.frequency.label'))
                                                     ->required()
                                                     ->live()
                                                     ->options([
                                                         'everySecond'         => __('filament-workflows::workflows.schedule.frequency.options.every_second'),
                                                         'everyTwoSeconds'     => __('filament-workflows::workflows.schedule.frequency.options.every_two_seconds'),
                                                         'everyFiveSeconds'    => __('filament-workflows::workflows.schedule.frequency.options.every_five_seconds'),
                                                         'everyTenSeconds'     => __('filament-workflows::workflows.schedule.frequency.options.every_ten_seconds'),
                                                         'everyFifteenSeconds' => __('filament-workflows::workflows.schedule.frequency.options.every_fifteen_seconds'),
                                                         'everyTwentySeconds'  => __('filament-workflows::workflows.schedule.frequency.options.every_twenty_seconds'),
                                                         'everyThirtySeconds'  => __('filament-workflows::workflows.schedule.frequency.options.every_thirty_seconds'),
                                                         'everyMinute'         => __('filament-workflows::workflows.schedule.frequency.options.every_minute'),
                                                         'everyTwoMinutes'     => __('filament-workflows::workflows.schedule.frequency.options.every_two_minutes'),
                                                         'everyThreeMinutes'   => __('filament-workflows::workflows.schedule.frequency.options.every_three_minutes'),
                                                         'everyFourMinutes'    => __('filament-workflows::workflows.schedule.frequency.options.every_four_minutes'),
                                                         'everyFiveMinutes'    => __('filament-workflows::workflows.schedule.frequency.options.every_five_minutes'),
                                                         'everyTenMinutes'     => __('filament-workflows::workflows.schedule.frequency.options.every_ten_minutes'),
                                                         'everyFifteenMinutes' => __('filament-workflows::workflows.schedule.frequency.options.every_fifteen_minutes'),
                                                         'everyThirtyMinutes'  => __('filament-workflows::workflows.schedule.frequency.options.every_thirty_minutes'),
                                                         'hourly'              => __('filament-workflows::workflows.schedule.frequency.options.hourly'),
                                                         'everyTwoHours'       => __('filament-workflows::workflows.schedule.frequency.options.every_two_hours'),
                                                         'everyThreeHours'     => __('filament-workflows::workflows.schedule.frequency.options.every_three_hours'),
                                                         'everyFourHours'      => __('filament-workflows::workflows.schedule.frequency.options.every_four_hours'),
                                                         'everySixHours'       => __('filament-workflows::workflows.schedule.frequency.options.every_six_hours'),
                                                         'daily'               => __('filament-workflows::workflows.schedule.frequency.options.daily'),
                                                         'dailyAt'             => __('filament-workflows::workflows.schedule.frequency.options.daily_at'),
                                                         'twiceDaily'          => __('filament-workflows::workflows.schedule.frequency.options.twice_daily'),
                                                         'twiceDailyAt'        => __('filament-workflows::workflows.schedule.frequency.options.twice_daily_at'),
                                                         'weekly'              => __('filament-workflows::workflows.schedule.frequency.options.weekly'),
                                                         'weeklyOn'            => __('filament-workflows::workflows.schedule.frequency.options.weekly_on'),
                                                         'monthly'             => __('filament-workflows::workflows.schedule.frequency.options.monthly'),
                                                         'monthlyOn'           => __('filament-workflows::workflows.schedule.frequency.options.monthly_on'),
                                                         'twiceMonthly'        => __('filament-workflows::workflows.schedule.frequency.options.twice_monthly'),
                                                         'lastDayOfMonth'      => __('filament-workflows::workflows.schedule.frequency.options.last_day_of_month'),
                                                         'quarterly'           => __('filament-workflows::workflows.schedule.frequency.options.quarterly'),
                                                         'quarterlyOn'         => __('filament-workflows::workflows.schedule.frequency.options.quarterly_on'),
                                                         'yearly'              => __('filament-workflows::workflows.schedule.frequency.options.yearly'),
                                                         'yearlyOn'            => __('filament-workflows::workflows.schedule.frequency.options.yearly_on'),
                                                     ]),
                                               TimePicker::make('schedule_daily_at')
                                                         ->visible(fn(Get $get
                                                         ) => $get('schedule_frequency') === "daily")
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

                                    Section::make(__('filament-workflows::workflows.sections.workflow_custom_event'))
                                           ->visible(fn(Get $get) => $get('type') === "custom_event")
                                           ->columnSpanFull()
                                           ->schema([
                                               Select::make('custom_event')
                                                     ->label(__('filament-workflows::workflows.custom_event.label'))
                                                     ->required()
                                                     ->options(Utils::listEvents()),
                                           ])->columns(2),

                                    Fieldset::make()
                                            ->visible(fn(Get $get) => $get('type') === "model_event")
                                            ->columnSpanFull()
                                            ->schema([
                                                Grid::make(3)->columnSpanFull()
                                                    ->schema([
                                                        Select::make('model_type')
                                                              ->label(__('filament-workflows::workflows.model.attributes.label'))
                                                              ->live()
                                                              ->required()
                                                              ->options(Utils::listTriggers()),

                                                        Select::make('model_event')
                                                              ->label(__('filament-workflows::workflows.event_type'))
                                                              ->required()
                                                              ->live()
                                                              ->options([
                                                                  'created' => __('filament-workflows::workflows.model.events.created'),
                                                                  'updated' => __('filament-workflows::workflows.model.events.updated'),
                                                                  'deleted' => __('filament-workflows::workflows.model.events.deleted'),
                                                              ]),

                                                        Hidden::make('model_comparison')->default('any-attribute'),

                                                        Select::make('model_attribute')
                                                              ->label(__('filament-workflows::workflows.model.attributes.updated'))
                                                              ->visible(fn(Get $get
                                                              ) => $get('model_event') === "updated")
                                                              ->default('any-attribute')
                                                              ->afterStateUpdated(function (
                                                                  $state,
                                                                  Set $set
                                                              ) {
                                                                  if ($state and $state === "any-attribute") {
                                                                      $set('model_comparison',
                                                                          'any-attribute');
                                                                  }

                                                                  if ($state and $state !== "any-attribute") {
                                                                      $set('model_comparison',
                                                                          "specified");
                                                                  }
                                                              })
                                                              ->options(function (Get $get) {
                                                                  $model_class = $get('model_type');
                                                                  if ($model_class) {
                                                                      return array_merge(
                                                                          [
                                                                              'any-attribute' => '* ' . __('filament-workflows::workflows.model.attributes.any')
                                                                          ],
                                                                          Utils::getTriggerAttributes($model_class,
                                                                              true, true)
                                                                      );
                                                                  }
                                                              }),

                                                        Select::make('condition_type')
                                                              ->label(__('filament-workflows::workflows.condition_type'))
                                                              ->live()
                                                              ->required()
                                                              ->afterStateUpdated(function (
                                                                  $state,
                                                                  Set $set
                                                              ) {
                                                                  if ($state === "no-condition-is-required") {
                                                                      $set('conditions', []);
                                                                  }
                                                              })
                                                              ->options([
                                                                  'no-condition-is-required' => __('filament-workflows::workflows.conditions.types.none'),
                                                                  'all-conditions-are-true'  => __('filament-workflows::workflows.conditions.types.all'),
                                                                  'any-condition-is-true'    => __('filament-workflows::workflows.conditions.types.any'),
                                                              ]),
                                                    ]),

                                                Repeater::make('conditions')
                                                        ->label(__('filament-workflows::workflows.conditions.label'))
                                                        ->relationship('conditions')
                                                        ->visible(fn(Get $get
                                                        ) => $get('condition_type') === "all-conditions-are-true" or $get('condition_type') === "any-condition-is-true")
                                                        ->schema([
                                                            Select::make('model_attribute')
                                                                  ->label(__('filament-workflows::workflows.model.attributes.label'))
                                                                  ->live()
                                                                  ->required()
                                                                  ->options(function (
                                                                      Get $get
                                                                  ) {
                                                                      $model_class = $get('../../model_type');
                                                                      if ($model_class) {
                                                                          return Utils::getTriggerAttributes($model_class,
                                                                              true,
                                                                              true);
                                                                      }
                                                                      return [];
                                                                  }),
                                                            Select::make('operator')
                                                                  ->label(__('filament-workflows::workflows.form.operator'))
                                                                  ->required()
                                                                  ->options([
                                                                      'is-equal-to'            => __('filament-workflows::workflows.conditions.operators.equals'),
                                                                      'is-not-equal-to'        => __('filament-workflows::workflows.conditions.operators.not_equals'),
                                                                      'equals-or-greater-than' => __('filament-workflows::workflows.conditions.operators.greater_equals'),
                                                                      'equals-or-less-than'    => __('filament-workflows::workflows.conditions.operators.less_equals'),
                                                                      'greater-than'           => __('filament-workflows::workflows.conditions.operators.greater'),
                                                                      'less-than'              => __('filament-workflows::workflows.conditions.operators.less'),
                                                                  ]),
                                                            TextInput::make('compare_value')
                                                                     ->label(__('filament-workflows::workflows.form.compare_value'))
                                                                     ->required()
                                                                     ->hint(function (
                                                                         Get $get
                                                                     ) {
                                                                         $model_class     = $get('../../model_type');
                                                                         $model_attribute = $get('model_attribute');
                                                                         if ($model_class and $model_attribute) {
                                                                             return Utils::getTableColumnType($model_class,
                                                                                 $model_attribute);
                                                                         }
                                                                     }),
                                                        ])->columnSpan(2)->columns(3),
                                            ]),
                                ]),

                         Section::make(__('filament-workflows::workflows.sections.actions.label'))
                                ->id('workflow-actions-section')
                                ->collapsed(fn($context) => $context === "edit" or $context === "view")
                                ->description(__('filament-workflows::workflows.sections.actions.description'))
                                ->headerActions([
                                    Action::make('record_attributes')
                                          ->visible(fn(Get $get
                                          ) => in_array($get('type'),
                                              ['model_event', 'custom_event']))
                                          ->icon('heroicon-o-question-mark-circle')
                                          ->color('gray')
                                          ->label(__('filament-workflows::workflows.actions.magic_attributes.label'))
                                          ->fillForm(function (Get $get) {
                                              $trigger_type = $get('type');
                                              if ($trigger_type == "model_event") {
                                                  return [
                                                      'attributes' => Utils::getModelAttributesSuggestions($get('model_type'))
                                                  ];
                                              }
                                              if ($trigger_type == "custom_event") {
                                                  return [
                                                      'attributes' => Utils::getCustomEventVarsSuggestions([
                                                          'user_id', 'user_email',
                                                          'order_id'
                                                      ])
                                                  ];
                                              }
                                              return [];
                                          })
                                          ->modalSubmitAction(false)
                                          ->modalCancelAction(false)
                                          ->schema([
                                              Repeater::make('attributes')
                                                      ->addable(false)
                                                      ->deletable(false)
                                                      ->reorderable(false)
                                                      ->grid(4)
                                                      ->simple(
                                                          TextInput::make('attribute')
                                                                   ->readOnly()
                                                                   ->required(),
                                                      )
                                          ]),
                                ])
                                ->collapsible()
                                ->schema([
                                    Repeater::make('actions')
                                            ->hiddenLabel()
                                            ->relationship('actions')
                                            ->itemLabel(fn(array $state
                                            ): ?string => $state['action'] ?? null)
                                            ->schema([
                                                Select::make('action')
                                                      ->hiddenLabel()
                                                      ->required()
                                                      ->live()
                                                      ->disableOptionWhen(function (
                                                          Get $get,
                                                          $value
                                                      ) {
                                                          return match ($get('../../type')) {
                                                              'scheduled'    => !Utils::getAction($value)->canBeUsedWithScheduledWorkflows(),
                                                              'model_event'  => !Utils::getAction($value)->canBeUsedWithRecordEventWorkflows(),
                                                              'custom_event' => !Utils::getAction($value)->canBeUsedWithCustomEventWorkflows(),
                                                              default        => false,
                                                          };
                                                      })
                                                      ->suffixAction(
                                                          Action::make('config_action')
                                                                ->disabled(fn($context) => $context === "view")
                                                                ->label(fn(Get $get) => $get('action'))
                                                                ->icon('heroicon-o-wrench-screwdriver')
                                                                ->visible(fn(Get $get) => filled($get('action')))
                                                                ->stickyModalHeader()
                                                                ->stickyModalFooter()
                                                                ->modalWidth(Width::SevenExtraLarge)
                                                                ->mountUsing(function (
                                                                    Schema $form,
                                                                    Get $get,
                                                                    $state
                                                                ) {
                                                                    $form->fill();
                                                                    foreach (Utils::extractComponents($form->getComponents(withHidden: true)) as $component) {
                                                                        if (method_exists($component,
                                                                            'getName')) {
                                                                            $component->state($get($component->getName()));
                                                                        }
                                                                    }
                                                                })
                                                                ->schema(function (
                                                                    Get $get
                                                                ) {
                                                                    $action = Utils::getAction($get('action'));
                                                                    if ($action) {
                                                                        $required_packages = $action->requireInstalledPackages();
                                                                        $not_installed     = [];
                                                                        foreach ($required_packages as $required_package) {
                                                                            if (!Utils::isPackageInstalled($required_package)) {
                                                                                $not_installed[] = $required_package;
                                                                            }
                                                                        }
                                                                        if (count($not_installed) > 0) {
                                                                            return [
                                                                                TextEntry::make('note')
                                                                                         ->hiddenLabel()
                                                                                         ->columnSpanFull()
                                                                                         ->state(new HtmlString("<p>The following packages are required to use this action:</p> " . implode(", ",
                                                                                                 $not_installed))),
                                                                            ];
                                                                        }
                                                                    }
                                                                    return $action->getFields();
                                                                })
                                                                ->action(function (
                                                                    array $data,
                                                                    Action $action,
                                                                    Set $set
                                                                ): void {
                                                                    foreach ($data['data'] ?? [] as $key => $value) {
                                                                        $set("data.$key",
                                                                            $value);
                                                                    }
                                                                })
                                                      )
                                                      ->afterStateUpdated(function (
                                                          $state,
                                                          Set $set,
                                                          Get $get
                                                      ) {

                                                          foreach (collect(Utils::getAction($state)?->getFields() ??
                                                              []) as $field) {
                                                              /*
                                                              if (method_exists($field, 'getChildComponents')) {
                                                                  foreach (Utils::extractComponents($field->getChildComponents()) as $component) {
                                                                      if (method_exists($component,
                                                                              'getName') and method_exists
                                                                          ($component, 'getDefaultState')) {
                                                                          try {
                                                                              $set($component->getName(),
                                                                                  $component->getDefaultState());
                                                                          } catch (Throwable $throwable) {
                                                                          }
                                                                      }
                                                                  }
                                                              }
                                                              */
                                                              if (method_exists($field,
                                                                      'getName') and method_exists($field,
                                                                      'getDefaultState')) {
                                                                  try {
                                                                      $set($field->getName(),
                                                                          $field->getDefaultState());
                                                                  } catch (Throwable $throwable) {
                                                                  }
                                                              }
                                                          }
                                                      })
                                                      ->options(Utils::getActionsForSelect()),

                                                Section::make()
                                                       ->columnSpan('hidden')
                                                       ->schema(function (
                                                           Get $get
                                                       ) {
                                                           return Utils::getAction($get('action'))?->getFields() ?? [];
                                                       }),
                                            ])
                                            ->minItems(1)
                                            ->reorderable()
                                            ->collapsible()
                                            ->grid(2)
                                            ->columns(1),
                                ]),

                     ])
                     ->columnSpan(['lg' => 2]),

                Group::make()->schema([
                    Section::make(__('filament-workflows::workflows.sections.grouping.label'))
                           ->schema([
                               Select::make('workflow_group_id')
                                     ->hiddenLabel()
                                     ->required()
                                     ->disabledOn('edit')
                                     ->options(WorkflowGroup::pluck('name', 'id'))
                                     ->createOptionForm(function () {
                                         return [
                                             Section::make()->schema([
                                                 TextInput::make('name')
                                                          ->required(),
                                             ])
                                         ];
                                     })
                                     ->createOptionUsing(function ($data) {
                                         $id = filament()->getTenant()?->id;
                                         if (filled($id)) {
                                             $data['team_id'] = $id;
                                         }
                                         $model = WorkflowGroup::create($data);
                                         return $model->id;
                                     })
                                     ->createOptionAction(
                                         fn(Action $action
                                         ) => $action->modalWidth(Width::ExtraSmall),
                                     )
                                     ->searchable(),
                           ]),

                    Section::make(__('filament-workflows::workflows.sections.status.label'))
                           ->description(__('filament-workflows::workflows.sections.status.description'))
                           ->schema([
                               Toggle::make('active')
                                     ->label(__('filament-workflows::workflows.form.active'))
                                     ->default(1),
                           ]),
                ])
                     ->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('description')
                          ->label(__('filament-workflows::workflows.sections.description.label'))
                          ->searchable()
                          ->description(fn($record) => $record->statement)
                          ->tooltip(fn($record) => $record->statement)
                          ->toggleable()
                          ->limit(60),

                TextColumn::make('type')
                          ->label(__('filament-workflows::workflows.sections.type.label.workflow_type'))
                          ->toggleable()
                          ->badge()
                          ->getStateUsing(fn($record) => str($record->type)->replace('_', ' ')->title())
                          ->color(fn($record) => match ($record->type) {
                              'scheduled'    => 'gray',
                              'model_event'  => 'success',
                              'custom_event' => 'warning',
                              default        => 'danger',
                          })
                          ->searchable(),

                TextColumn::make('group.name')
                          ->label(__('filament-workflows::workflows.sections.grouping.label'))
                          ->toggleable(isToggledHiddenByDefault: true)
                          ->searchable(),

                TextColumn::make('actions')
                          ->label(__('filament-workflows::workflows.sections.actions.label'))
                          ->tooltip(fn($record) => $record->actions_statement)
                          ->toggleable()
                          ->getStateUsing(fn($record) => $record->actions_statement),

                TextColumn::make('executions_count')
                          ->label(__('filament-workflows::workflows.table.columns.executions_count'))
                          ->toggleable()
                          ->counts('executions'),

                TextColumn::make('last_execution')
                          ->label(__('filament-workflows::workflows.table.columns.last_execution'))
                          ->toggleable()
                          ->getStateUsing(fn($record) => $record->executions->last()?->created_at->diffForHumans()),

                ToggleColumn::make('active')
                            ->label(__('filament-workflows::workflows.sections.status.label'))
                            ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('test')
                          ->visible(fn() => true)
                          ->icon('heroicon-o-exclamation-triangle')
                          ->color('warning')
                          ->label(__('filament-workflows::workflows.table.test.title'))
                          ->schema(function (Workflow $workflow) {
                              return [
                                  Section::make()
                                         ->schema([
                                             TextEntry::make('note')
                                                      ->columnSpanFull()
                                                      ->state(__('filament-workflows::workflows.table.test.note')),

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

                                             KeyValue::make('simulate_attributes_changes')
                                                     ->label(__('filament-workflows::workflows.table.test.fields.simulate_attributes.label'))
                                                     ->visible($workflow->model_event == "updated")
                                                     ->helperText(__('filament-workflows::workflows.table.test.fields.simulate_attributes.help'))
                                                     ->columnSpanFull()
                                                     ->required(),

                                             Checkbox::make('execute_actions')
                                                     ->label(__('filament-workflows::workflows.table.test.fields.execute_actions'))
                                                     ->columnSpanFull(),
                                         ])->columns(2),

                              ];
                          })
                          ->action(function (
                              Workflow $workflow,
                              array $data,
                              Action $action
                          ) {
                              try {
                                  $model = ($workflow->model_type)::findOrFail($data['record_id']);
                                  $met   = Utils::testWorkflowConditionsMet($workflow, $model,
                                      $data['simulate_attributes_changes'] ?? []);

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
                              } catch (Throwable $throwable) {
                                  Notification::make()
                                              ->title(__('filament-workflows::workflows.table.test.title'))
                                              ->body($throwable->getMessage())
                                              ->danger()
                                              ->persistent()
                                              ->send();
                              }

                              $action->halt();
                          }),

                    DeleteAction::make()
                                ->action(function (Workflow $record) {
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
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'    => Pages\ListWorkflows::route('/'),
            'create'   => Pages\CreateWorkflow::route('/create'),
            'edit'     => Pages\EditWorkflow::route('/{record}/edit'),
            'view'     => Pages\ViewWorkflow::route('/{record}'),
            'viewLogs' => Pages\ViewLogs::route('/{record}/view-logs'),
        ];
    }

    public static function canAccess(): bool
    {
        return true;
    }
}
