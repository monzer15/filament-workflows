<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            if (Schema::hasTable('teams')) {
                $table->foreignId('team_id')->nullable()->index()->references('id')->on('teams');
                $table->unique(['team_id', 'name']);
            } else {
                $table->unique(['name']);
            }

            $table->timestamps();
        });

        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            if (Schema::hasTable('teams')) {
                $table->foreignId('team_id')->nullable()->index()->references('id')->on('teams');
            }
            $table->foreignId('workflow_group_id')->nullable()->index()->references('id')->on('workflow_groups');
            $table->text('description');
            $table->enum('type', ['scheduled', 'model_event', 'custom_event']);
            $table->string('schedule_frequency')->nullable();
            $table->string('schedule_daily_at')->nullable();
            $table->string('schedule_params')->nullable();
            $table->string('custom_event')->nullable();
            $table->string('model_type')->nullable()->index();// when trigger is model, NOTE: model_id should be in executions because every execution happens on a different model
            $table->enum('model_event', ['created', 'updated', 'deleted'])->nullable();
            $table->enum('model_comparison', ['any-attribute', 'specified'])->default('any-attribute')->nullable(); // e.g. in case of record updated run only when status is updated
            $table->string('model_attribute')->index()->nullable(); //when model comparison is specified
            $table->enum('condition_type', ['no-condition-is-required', 'all-conditions-are-true', 'any-condition-is-true'])->default('no-condition-is-required');
            $table->boolean('run_once')->default(0);
            $table->boolean('active')->default(1);
            $table->text('logs')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->index()->references('id')->on('workflows');
            $table->enum('operator', ['is-equal-to', 'is-not-equal-to', 'equals-or-greater-than', 'equals-or-less-than', 'greater-than', 'less-than']);
            $table->string('model_attribute'); //column
            $table->string('compare_value'); // the value to compare
            $table->timestamps();
        });

        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->index()->references('id')->on('workflows');
            $table->unsignedInteger('sort')->default(1);
            $table->string('action');
            $table->json('data');
            $table->timestamps();
        });

        Schema::create('workflow_action_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_action_id')->index()->references('id')->on('workflow_actions')->cascadeOnDelete();
            $table->bigInteger('model_id')->unsigned()->nullable();
            $table->string('execution_time')->nullable();
            $table->text('logs')->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_action_executions');
        Schema::dropIfExists('workflow_actions');
        Schema::dropIfExists('workflow_conditions');
        Schema::dropIfExists('workflows');
        Schema::dropIfExists('workflow_groups');
    }
};
