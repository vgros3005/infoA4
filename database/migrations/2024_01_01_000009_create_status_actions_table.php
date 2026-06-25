<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_status_id')->constrained('statuses')->cascadeOnDelete();
            $table->string('action_label');
            $table->string('action_name');
            $table->string('button_color', 20)->default('primary');
            $table->string('icon', 50)->nullable();
            $table->boolean('requires_comment')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('status_action_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_action_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->unique(['status_action_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_action_roles');
        Schema::dropIfExists('status_actions');
    }
};
