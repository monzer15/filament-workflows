<?php

namespace Monzer\FilamentWorkflows\Actions;

use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Monzer\FilamentWorkflows\Contracts\Action;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;
use Monzer\FilamentWorkflows\Utils\Utils;

class SendFilamentNotification extends Action
{

    public function getName(): string
    {
        return 'Send filament notification';
    }

    public function getId(): string
    {
        return 'send-filament-notification';
    }

    public function getFields(): array
    {
        return [
            Section::make()->schema([
                Select::make('data.notifiable_users')
                    ->multiple()
                    ->nullable()
                    ->options(function () {
                        $users = User::all();
                        $data = [];
                        foreach ($users as $user) {
                            $data[$user->id] = $user->getFilamentName();
                        }
                        return $data;
                    }),

                Select::make('data.notifiable_relations')
                    ->live()
                    ->multiple()
                    ->nullable()
                    ->preload()
                    ->options(function (Get $get, $livewire) {
                        $model_class = $livewire->data['model_type'];
                        if ($model_class) {
                            return collect(Utils::getNotifiableRelations($model_class));
                        }
                    }),

                TextInput::make('data.title')
                    ->required()
                    ->hint("Supports magic attributes")
                    ->helperText("e.g. Task @title@ updated."),

                Textarea::make('data.body')
                    ->required()
                    ->hint("Supports magic attributes")
                    ->helperText("e.g. Task @title@  status updated to @status->title@.")
                    ->rows(5)
                    ->columnSpanFull(),

                ToggleButtons::make('data.status')
                    ->required()
                    ->inline()
                    ->default('success')
                    ->options([
                        'info' => 'Info',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'danger' => 'Danger',
                    ])
                    ->colors([
                        'danger' => 'danger',
                        'warning' => 'warning',
                        'success' => 'success',
                    ]),

                TextInput::make('data.icon')
                    ->required()
                    ->placeholder('heroicon-o-information-circle')
                    ->helperText('heroicon-o-information-circle')
                    ->default('heroicon-o-information-circle'),

                Checkbox::make('data.broadcast')
                ->columnSpanFull(),

            ])->columns(3),
        ];
    }

    public function getMagicAttributeFields(): array
    {
        return [
            'title',
            'body'
        ];
    }

    public function execute(array $data, WorkflowActionExecution $actionExecution, ?Model $model, array $custom_event_data, array &$shared_data): void
    {
        $users = User::findMany($data['notifiable_users'] ?? []);

        foreach ($data['notifiable_relations'] ?? [] as $relation) {
            if ($model->{$relation} instanceof Collection)
                $users = $users->merge($model->{$relation});
            else
                $users->add($model->{$relation});
        }

        Notification::make()
            ->title($data['title'])
            ->body($data['body'])
            ->status($data['status'])
            ->icon($data['icon'])
            ->broadcast($data['broadcast'] ? $users : collect())
            ->sendToDatabase($users);

        $actionExecution->log("Filament notification sent");
    }
}
