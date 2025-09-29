<?php

namespace Monzer\FilamentWorkflows;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Monzer\FilamentWorkflows\Jobs\ExecuteScheduledWorkflow;
use Monzer\FilamentWorkflows\Listeners\WorkflowEventSubscriber;
use Monzer\FilamentWorkflows\Models\Workflow;
use Monzer\FilamentWorkflows\Utils\Utils;
use Monzer\FilamentWorkflows\Commands\CleanupWorkflowLogs;

class FilamentWorkflowsServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-workflows');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../config/workflows.php' => config_path('workflows.php'),
        ], 'config');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-workflows');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/workflows.php',
            'workflows'
        );
    }

    public function boot()
    {
        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupWorkflowLogs::class,
            ]);
        }
        
        $this->app->booted(function () {

            if (!Schema::hasTable('workflows'))
                return;

            $schedule = $this->app->make(Schedule::class);

            $workflows = Workflow::scheduled()->get();

            foreach ($workflows as $workflow) {

                if ($workflow->run_once and $workflow->executions->count() > 0) {
                    Utils::log($workflow, Utils::getFormattedDate() . ", Workflow evaluator: workflow already ran, skipping.");
                    continue;
                }

                $params = explode(",", $workflow->schedule_params);

                $schedule->call(function () use ($workflow, $params) {
                    dispatch(new ExecuteScheduledWorkflow($workflow));
                })
                    ->description($workflow->description)
                    ->name($workflow->description)
                    ->{$workflow->schedule_frequency}(...$params);
            }

            $schedule->call(function () {
            })->everyMinute();
        });

        Event::subscribe(WorkflowEventSubscriber::class);
    }
}
