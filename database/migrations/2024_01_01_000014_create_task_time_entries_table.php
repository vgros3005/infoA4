<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->date('entry_date');
            $table->decimal('hours', 5, 2);
            $table->text('comment')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->timestamps();

            $table->index(['task_id', 'entry_date']);
            $table->index(['user_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_time_entries');
    }
};
