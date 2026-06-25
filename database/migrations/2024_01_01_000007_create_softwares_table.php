<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('softwares', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 20)->unique()->nullable();
            $table->string('description')->nullable();
            $table->string('version')->nullable();
            $table->string('vendor')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('softwares');
    }
};
