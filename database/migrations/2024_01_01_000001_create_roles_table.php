<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->string('color', 20)->default('secondary');
            $table->boolean('can_create_request')->default(false);
            $table->boolean('can_validate_request')->default(false);
            $table->boolean('can_change_status')->default(false);
            $table->boolean('can_assign_task')->default(false);
            $table->boolean('can_export_pdf')->default(false);
            $table->boolean('can_admin')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
