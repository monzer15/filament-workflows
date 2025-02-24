<?php

namespace Monzer\FilamentWorkflows\Actions;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Monzer\FilamentWorkflows\Contracts\Action;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;
use Monzer\FilamentWorkflows\Utils\Utils;

class SendSmsViaTwilio extends Action
{
    public function getId(): string
    {
        return 'send-sms-via-twilio';
    }

    public function getName(): string
    {
        return 'Send SMS via Twilio';
    }

    public function getFields(): array
    {
        return [
            Section::make()->schema([
                TextInput::make('data.sid')
                    ->default(config('workflows.services.twilio.sid'))
                    ->required(),

                TextInput::make('data.token')
                    ->default(config('workflows.services.twilio.token'))
                    ->required(),

                TextInput::make('data.from')
                    ->default(config('workflows.services.twilio.from'))
                    ->required(),

                TextInput::make('data.to')
                    ->required(),

                Textarea::make('data.body')
                    ->rows(5)
                    ->columnSpanFull()
                    ->required(),
            ])->columns(2)
        ];
    }

    public function execute(array $data, WorkflowActionExecution $actionExecution, ?Model $model, array $custom_event_data, array &$shared_data): void
    {
        $client = new \Twilio\Rest\Client($data['sid'], $data['token']);
        $client->messages->create($data['to'], [
            'from' => $data['from'],
            'body' => $data['body']
        ]);

        $actionExecution->log(Utils::getFormattedDate() . ", Execution #{$actionExecution->id}, sms sent to {$data['to']}");
    }

    public function requireInstalledPackages(): array
    {
        return [
            'twilio/sdk',
        ];
    }
}

