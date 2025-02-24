<?php

namespace Monzer\FilamentWorkflows\Actions;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Monzer\FilamentWorkflows\Contracts\Action;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;

class SendWhatsAppMessageViaWassenger extends Action
{

    public function getId(): string
    {
        return 'send-whatsapp-message-via-wassenger';
    }

    public function getName(): string
    {
        return 'Send WhatsApp Message Via Wassenger';
    }

    public function getFields(): array
    {
        return [
            TextInput::make('data.wassenger_api_token')
                ->label('Wassenger api token')
                ->helperText(fn() => new HtmlString("<a target='_blank' style='color: blue' href='https://wassenger.com'> Get your token</a>"))
                ->required(),

            TextInput::make('data.device_id')
                ->requiredIf('data.send_to', 'group_chats')
                ->live(true)
                ->helperText("Used to retrieve groups, provided by wassenger"),

            Radio::make("data.send_to")
                ->required()
                ->live()
                ->options([
                    'phone_numbers' => 'Phone numbers',
                    'group_chats' => 'Group chats',
                ]),

            Repeater::make('data.phone_numbers')
                ->visible(fn(Get $get) => $get('data.send_to') === "phone_numbers")
                ->required()
                ->minItems(1)
                ->schema([
                    TextInput::make('phone_number')
                        ->required()
                        ->helperText("E. 164 phone numbers only"),
                ]),

            Select::make('data.group_chats')
                ->visible(fn(Get $get) => $get('data.send_to') === "group_chats")
                ->multiple()
                ->options(function (Get $get) {
                    $api_token = $get('data.wassenger_api_token');
                    $device_id = $get('data.device_id');

                    $response = \Http::withHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => $api_token,
                    ])->get("https://api.wassenger.com/v1/devices/$device_id/groups");

                    if ($response->successful()) {
                        return collect($response->json())->pluck('name', 'wid');
                    } else {
                        Notification::make()
                            ->title("Error")
                            ->body($response->toException()->getMessage())
                            ->persistent()
                            ->send();
                    }

                    return [];
                })
                ->required(),

            Textarea::make('data.message')
                ->rows(5)
                ->helperText(fn() => new HtmlString("<a target='_blank' style='color: blue' href='https://app.wassenger.com/help/text-format'>Text format</a>"))
                ->required(),
        ];
    }

    public function getMagicAttributeFields(): array
    {
        return [
            'message',
        ];
    }

    public function execute(array $data, WorkflowActionExecution $actionExecution, ?Model $model, array $custom_event_data, array &$shared_data): void
    {
        $results = [];
        switch ($data['send_to']) {
            case 'phone_numbers':
            {
                $phones = collect($data['phone_numbers'])->pluck('phone_number')->toArray();
                foreach ($phones as $phone) {
                    $rsp = $this->sendMessageToPhone($phone, $data['message'], $data['wassenger_api_token']);
                    $results[] = $rsp->successful() ? "message sent to phone: $phone" : " message not sent to phone $phone, ".$rsp->toException()->getMessage();
                }
                break;
            }
            case 'group_chats':
            {
                foreach ($data['group_chats'] as $group) {
                    $rsp = $this->sendMessageToGroup($group, $data['message'], $data['wassenger_api_token']);
                    $results[] = $rsp->successful() ? "message sent to group: $group" : " message not sent to group: $group, ".$rsp->toException()->getMessage();
                }
                break;
            }
        }
        $actionExecution->log(implode(', ', $results));
    }


    protected function sendMessageToPhone($phone, $message, $token): \Illuminate\Http\Client\Response
    {
        return \Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => $token,
        ])->post("https://api.wassenger.com/v1/messages",
            [
                'phone' => $phone,
                'message' => $message,
            ]);
    }

    protected function sendMessageToGroup($group, $message, $token): \Illuminate\Http\Client\Response
    {
        return \Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => $token,
        ])->post("https://api.wassenger.com/v1/messages",
            [
                'group' => $group,
                'message' => $message,
            ]);
    }
}
