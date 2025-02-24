<?php

namespace Monzer\FilamentWorkflows\Actions;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Monzer\FilamentWorkflows\Contracts\Action;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;
use NotificationChannels\Telegram\TelegramMessage;

class SendTelegramMessage extends Action
{

    public function getName(): string
    {
        return "Send telegram message";
    }

    public function getId(): string
    {
        return "send-telegram-message";
    }

    public function getFields(): array
    {
        return [
            TextInput::make('data.telegram_bot_token')
                ->helperText("Supports magic attributes")
                ->required(),

            TextInput::make('data.telegram_user_id')
                ->helperText("Supports magic attributes")
                ->required(),

            Textarea::make('data.message')
                ->helperText("Supports magic attributes")
                ->rows(5)
                ->required(),
        ];
    }

    public function getMagicAttributeFields(): array
    {
        return [
            'telegram_bot_token',
            'message',
            'telegram_user_id',
        ];
    }

    public function execute(array $data, WorkflowActionExecution $actionExecution, ?Model $model, array $custom_event_data, array &$shared_data): void
    {
        config(['services.telegram-bot-api.token' => $data['telegram_bot_token']]);

         TelegramMessage::create()
            ->to($data['telegram_user_id'])
            ->content($data['message'])
            ->send();

        $actionExecution->log("Message sent");
    }

    public function requireInstalledPackages(): array
    {
        return ['laravel-notification-channels/telegram'];
    }
}
