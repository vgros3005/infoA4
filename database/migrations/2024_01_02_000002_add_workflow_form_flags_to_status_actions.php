<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_actions', function (Blueprint $table) {
            $table->boolean('requires_assignment')->default(false)->after('requires_comment');
            $table->boolean('requires_estimation')->default(false)->after('requires_assignment');
        });
    }

    public function down(): void
    {
        Schema::table('status_actions', function (Blueprint $table) {
            $table->dropColumn(['requires_assignment', 'requires_estimation']);
        });
    }
};
