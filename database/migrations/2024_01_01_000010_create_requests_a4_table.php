<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests_a4', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('title', 50);
            $table->longText('description')->nullable();
            $table->longText('content')->nullable();
            $table->foreignId('request_type_id')->constrained();
            $table->foreignId('priority_id')->constrained('priorities');
            $table->text('priority_justification')->nullable();
            $table->foreignId('status_id')->constrained('statuses');
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('assigned_team_id')->nullable()->constrained('teams');
            $table->date('requested_date');
            $table->date('desired_date')->nullable();
            $table->date('planned_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->boolean('is_frozen')->default(false);
            $table->integer('pdf_version')->default(0);
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('request_a4_company', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_a4_id')->constrained('requests_a4')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unique(['request_a4_id', 'company_id']);
        });

        Schema::create('request_a4_software', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_a4_id')->constrained('requests_a4')->cascadeOnDelete();
            $table->foreignId('software_id')->constrained('softwares')->cascadeOnDelete();
            $table->unique(['request_a4_id', 'software_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_a4_software');
        Schema::dropIfExists('request_a4_company');
        Schema::dropIfExists('requests_a4');
    }
};
