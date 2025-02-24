<?php

namespace Monzer\FilamentWorkflows\Actions;

use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Monzer\FilamentWorkflows\Contracts\Action;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;

class PushFirebaseNotification extends Action
{
    public function getName(): string
    {
        return "Push firebase Notification";
    }

    public function getId(): string
    {
        return "push-firebase-notification";
    }

    public function getFields(): array
    {
        return [
            Section::make()->schema([
                TextInput::make('data.server_key')
                    ->default(config('workflows.services.firebase.server_key'))
                    ->required(),

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

                TextInput::make('data.notifiable_token_attribute_name')
                    ->default(config('workflows.services.firebase.model_token_attribute_name'))
                    ->placeholder('fcm_token')
                    ->helperText('$user->fcm_token')
                    ->required(),

                TextInput::make('data.icon')
                    ->default(config('workflows.services.firebase.icon'))
                    ->url()
                    ->required(),

                TextInput::make('data.title')
                    ->live(true)
                    ->helperText(fn(Get $get) => $get('data.translated_title'))
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        if ($get('data.use_title_as_translation_key'))
                            $set('data.translated_title', __($state));
                        else
                            $set('data.translated_title', null);
                    })
                    ->helperText("Supports magic attributes")
                    ->required(),

                Hidden::make('data.translated_title'),

                Toggle::make('data.use_title_as_translation_key')
                    ->columnSpanFull()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        if ($state)
                            $set('data.translated_title', __($get('data.title')));
                        else
                            $set('data.translated_title', null);
                    }),

                Textarea::make('data.body')
                    ->live()
                    ->helperText(fn(Get $get) => $get('data.translated_body'))
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        if ($get('data.use_body_as_translation_key'))
                            $set('data.translated_body', __($state));
                        else
                            $set('data.translated_body', null);
                    })
                    ->rows(5)
                    ->helperText("Supports magic attributes")
                    ->columnSpanFull()
                    ->required(),

                Hidden::make('data.translated_body'),

                Toggle::make('data.use_body_as_translation_key')
                    ->columnSpanFull()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        if ($state)
                            $set('data.translated_body', __($get('data.body')));
                        else
                            $set('data.translated_body', null);
                    }),

                KeyValue::make('data.data')
                    ->columnSpanFull()
                    ->nullable(),

                Toggle::make('data.to_database')
                    ->required()
                    ->columnSpanFull(),
            ])->columns(2)
        ];
    }

    public function getMagicAttributeFields(): array
    {
        return [
            'title',
            'body',
            'data',
        ];
    }

    public function execute(array $data, WorkflowActionExecution $actionExecution, ?Model $model, array $custom_event_data, array &$shared_data): void
    {
        $serverKey = $data['server_key'];;
        $url = "https://fcm.googleapis.com/fcm/send";
        $title = $data['use_title_as_translation_key'] ? __($data['title']) : $data['title'];
        $body = $data['use_body_as_translation_key'] ? __($data['body']) : $data['body'];
        $image = $data['icon'];
        $users = User::findMany($data['notifiable_users'] ?? []);
        $tokens = $users->pluck($data['notifiable_token_attribute_name'])->toArray();
        $notification_data = $data['data'] ?? [];

        $notification = array('title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1', 'image' => $image);

        $arrayToSend = array("registration_ids" => $tokens, 'notification' => $notification, 'priority' => 'high', 'data' => $notification_data);
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key=' . $serverKey;
        $ch = \curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Send the request
        $response = curl_exec($ch);
        curl_close($ch);

        if ($data['to_database'] ?? null) {
            $notification_data['title'] = $title;
            $notification_data['body'] = $body;
            foreach ($users as $user) {
                 DatabaseNotification::create([
                    'id' => Str::random(36),
                    'type' => 'database',
                    'notifiable_id' => $user->id,
                    'notifiable_type' => get_class($user),
                    'data' => $notification_data,
                ]);
            }
        }
        $response = json_decode($response);

        if ($response and $response->success == 1) {
            $actionExecution->log("Push notification succeeded");
        } else {
            $actionExecution->log($response);
        }
    }
}
