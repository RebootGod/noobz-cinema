<?php
// ========================================
// CREATE MOVIE VIEWS TABLE
// ========================================
// File: database/migrations/2024_01_01_000011_create_movie_views_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('movie_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['movie_id', 'user_id']);
            $table->index('created_at');
            $table->index('ip_address');
        });
    }

    public function down()
    {
        Schema::dropIfExists('movie_views');
    }
};