<?php

return [
    'actions' => [
        \Monzer\FilamentWorkflows\Actions\SendFilamentNotification::class,
        \Monzer\FilamentWorkflows\Actions\SendEmail::class,
        \Monzer\FilamentWorkflows\Actions\SendSmsViaTwilio::class,
        \Monzer\FilamentWorkflows\Actions\CreateRecord::class,
        \Monzer\FilamentWorkflows\Actions\UpdateRecord::class,
        \Monzer\FilamentWorkflows\Actions\SendWebhook::class,
        \Monzer\FilamentWorkflows\Actions\PushFirebaseNotification::class,
        \Monzer\FilamentWorkflows\Actions\BackupMySqlDBUsingMySqlDump::class,
        \Monzer\FilamentWorkflows\Actions\SendWhatsAppMessageViaWassenger::class,
        \Monzer\FilamentWorkflows\Actions\SendTelegramMessage::class
    ],
    //scan the following directories for models
    'models_directory' => [
        'App\\Models',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Maximum Log Entries
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum number of log entries to keep for
    | each workflow. When this limit is exceeded, older entries will be
    | automatically removed to prevent database overflow. Set to null to
    | disable log rotation (not recommended for production).
    |
    */
    'max_log_entries' => env('WORKFLOWS_MAX_LOG_ENTRIES', 100),
    
    'services' => [
        'firebase' => [
            'server_key' => env('FIREBASE_SERVER_KEY'),
            'model_token_attribute_name' => env('FIREBASE_MODEL_TOKEN_ATTRIBUTE_NAME', 'fcm_token'),
            'icon' => env('FIREBASE_ICON'),
        ],
        'telegram' => [
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        ],
        'wassenger' => [
            'api_key' => env('WASSENGER_API_KEY'),
        ],
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],
    ],
];
