<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->string('color', 20)->default('secondary');
            $table->string('icon', 50)->nullable();
            $table->boolean('is_initial')->default(false);
            $table->boolean('is_final')->default(false);
            $table->boolean('freezes_request')->default(false);
            $table->boolean('generates_pdf')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
