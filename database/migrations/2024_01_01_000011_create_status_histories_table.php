<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_a4_id')->constrained('requests_a4')->cascadeOnDelete();
            $table->foreignId('from_status_id')->nullable()->constrained('statuses');
            $table->foreignId('to_status_id')->constrained('statuses');
            $table->foreignId('user_id')->constrained();
            $table->string('action')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['request_a4_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};
