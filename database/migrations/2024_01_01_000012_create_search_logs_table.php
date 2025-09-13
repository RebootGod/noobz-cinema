<?php
// ========================================
// CREATE SEARCH LOGS TABLE
// ========================================
// File: database/migrations/2024_01_01_000012_create_search_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('results_count')->default(0);
            $table->string('ip_address', 45);
            $table->json('filters')->nullable(); // Store applied filters
            $table->timestamps();
            
            $table->index('query');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('search_logs');
    }
};