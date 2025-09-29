<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->longText('logs')->nullable()->change();
        });
        
        Schema::table('workflow_action_executions', function (Blueprint $table) {
            $table->longText('logs')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->text('logs')->nullable()->change();
        });
        
        Schema::table('workflow_action_executions', function (Blueprint $table) {
            $table->text('logs')->nullable()->change();
        });
    }
};