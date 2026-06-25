<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('disk', 20)->default('local');
            $table->string('path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_pdf_version')->default(false);
            $table->integer('pdf_version_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
