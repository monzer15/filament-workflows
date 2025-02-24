<?php

namespace Monzer\FilamentWorkflows\Actions;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Monzer\FilamentWorkflows\Contracts\Action;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;

class SendEmail extends Action
{

    public function getName(): string
    {
        return 'Send email';
    }

    public function getId(): string
    {
        return 'send-email';
    }

    public function getFields(): array
    {
        return [
            TextInput::make('data.email')
                ->helperText("Supports magic attributes")
                ->required(),

            TextInput::make('data.subject')
                ->helperText("Supports magic attributes")
                ->required(),

            Textarea::make('data.message')
                ->helperText("Supports magic attributes")
                ->required()
                ->rows(5),

        ];
    }

    public function getMagicAttributeFields(): array
    {
        return [
            'email',
            'subject',
            'message'
        ];
    }

    public function execute(array $data, WorkflowActionExecution $actionExecution, ?Model $model, array $custom_event_data, array &$shared_data): void
    {
        Mail::raw($data['message'], function ($message) use ($data) {
            $message
                ->to($data['email'])
                ->subject($data['subject']);
        });

        $actionExecution->log("Email successfully sent to: {$data['email']} regarding: {$data['subject']}");
    }
}
