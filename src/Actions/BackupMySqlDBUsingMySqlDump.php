<?php

namespace Monzer\FilamentWorkflows\Actions;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Monzer\FilamentWorkflows\Contracts\Action;
use Monzer\FilamentWorkflows\Models\WorkflowActionExecution;
use Monzer\FilamentWorkflows\Utils\Utils;

class BackupMySqlDBUsingMySqlDump extends Action
{

    public function getName(): string
    {
        return 'Backup database via mysqldump';
    }

    public function getId(): string
    {
        return 'backup-database-via-mysqldump';
    }

    public function getFields(): array
    {
        return [
            Select::make('data.backup_to_disk')
                ->options(array_combine(array_keys(config('filesystems.disks')), array_keys(config('filesystems.disks'))))
                ->required(),
            TextInput::make('data.mysqldump_binary_directory')
                ->live(true)
                ->afterStateUpdated(function (TextInput $component, $state) {
                    if (windows_os()) {
                        if (!file_exists($state . DIRECTORY_SEPARATOR . 'mysqldump.exe')) {
                            $component->helperText(new HtmlString("<strong style='color: red;'>MySQL dump binary not found in this path.</strong>"));
                        }
                    } else {
                        if (!file_exists($state . DIRECTORY_SEPARATOR . 'mysqldump')) {
                            $component->helperText(new HtmlString("<strong style='color: red;'>MySQL dump binary not found in this path.</strong>"));
                        }
                    }
                })
                ->required(),
            Toggle::make('data.send_backup_url_to_email')
                ->live(),
            TextInput::make('data.email')
                ->visible(fn(Get $get) => $get('data.send_backup_url_to_email') == true)
                ->email()
                ->required(),
        ];
    }

    public function execute(array $data, WorkflowActionExecution $actionExecution, ?Model $model, array $custom_event_data, array &$shared_data): void
    {
        $mysqldump_binary_directory = $data['mysqldump_binary_directory'];
        $disk = $data['backup_to_disk'];
        $send_backup_url_to_email = $data['send_backup_url_to_email'];
        $email = $data['email'];

        $host = config('database.connections.mysql.host');
        $db = config('database.connections.mysql.database');
        $user_name = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');

        if (!Storage::disk($disk)->directoryExists('mysqldump')) {
            Storage::disk($disk)->makeDirectory('mysqldump');
        }

        $outputPath = 'mysqldump' . DIRECTORY_SEPARATOR . $db . '-' . now()->timestamp . '.sql';
        $output = Storage::disk($disk)->path($outputPath);

        if (windows_os()) {
            $cmd = $mysqldump_binary_directory . DIRECTORY_SEPARATOR . 'mysqldump --single-transaction --host="' . $host . '" --user="' . $user_name . '" --password="' . $pass . '" "' . $db . '" > "' . $output . '" 2>&1';
        } else {
            $cmd = $mysqldump_binary_directory . DIRECTORY_SEPARATOR . 'mysqldump --single-transaction --host="' . $host . '" --user="' . $user_name . '" --password="' . $pass . '" "' . $db . '" > "' . $output . '" 2>&1';
        }

        $rs = 'N/A';
        exec($cmd, result_code: $rs);

        $actionExecution->log(Utils::getFormattedDate() . ", Execution #{$actionExecution->id}, backup operation ended with code: $rs.");

        $file_url = url(Storage::url($outputPath));

        if($send_backup_url_to_email){
            Mail::raw("Backup file url: $file_url", function($message) use($email) {
                $message->to($email);
                $message->subject("Backup file url");
            });
            $actionExecution->log(Utils::getFormattedDate() . ", Execution #{$actionExecution->id}, file url sent to: $email");
        }

    }
    public function canBeUsedWithRecordEventWorkflows(): bool
    {
        return false;
    }

    public function canBeUsedWithCustomEventWorkflows(): bool
    {
        return false;
    }
}
