<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // database/migrations/2024_01_01_create_ai_query_jobs_table.php
        Schema::create('ai_query_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('user_input');
            $table->text('generated_sql')->nullable();
            $table->json('query_result')->nullable();
            $table->text('ai_response')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_query_jobs');
    }
};
