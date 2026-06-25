<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_a4_id')->nullable()->constrained('requests_a4')->nullOnDelete();
            $table->foreignId('task_type_id')->constrained('task_types');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('pending');
            $table->string('priority', 20)->default('medium');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->integer('progress')->default(0);
            $table->boolean('is_recurring')->default(false);
            $table->decimal('weekly_hours', 5, 2)->nullable();
            $table->string('recurrence_end')->nullable();
            $table->boolean('is_milestone')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['request_a4_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depends_on_task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('dependency_type', 20)->default('finish_to_start');
            $table->unique(['task_id', 'depends_on_task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('tasks');
    }
};
