<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('command');
            $table->string('expression')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });

        Schema::create('schedule_task_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_task_id')->constrained()->onDelete('cascade');
            $table->timestamp('executed_at');
            $table->timestamp('scheduled_for');
            $table->enum('status', ['pending', 'running', 'completed', 'failed']);
            $table->text('output')->nullable();
            $table->integer('exit_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_task_executions');
        Schema::dropIfExists('schedule_tasks');
    }
};
