<?php
// ========================================
// ADD SEARCH & FILTER COLUMNS TO MOVIES
// ========================================
// File: database/migrations/2024_01_01_000010_add_search_columns_to_movies_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('movies', function (Blueprint $table) {
            // Additional columns for enhanced search
            $table->string('language', 50)->nullable()->after('vote_count');
            $table->string('quality', 10)->nullable()->after('language');
            $table->boolean('has_subtitle')->default(false)->after('quality');
            $table->boolean('is_dubbed')->default(false)->after('has_subtitle');
            $table->text('cast')->nullable()->after('overview');
            $table->string('director')->nullable()->after('cast');
            $table->float('popularity')->default(0)->after('vote_count');
            $table->integer('view_count')->default(0)->after('is_active');
            
            // Indexes for better search performance
            $table->index('language');
            $table->index('quality');
            $table->index('has_subtitle');
            $table->index('is_dubbed');
            $table->index('popularity');
            $table->index('view_count');
            $table->fullText(['title', 'original_title', 'overview', 'cast', 'director']);
        });
    }

    public function down()
    {
        Schema::table('movies', function (Blueprint $table) {
            // Drop fulltext index first
            $table->dropFullText(['title', 'original_title', 'overview', 'cast', 'director']);
            
            // Drop regular indexes
            $table->dropIndex(['language']);
            $table->dropIndex(['quality']);
            $table->dropIndex(['has_subtitle']);
            $table->dropIndex(['is_dubbed']);
            $table->dropIndex(['popularity']);
            $table->dropIndex(['view_count']);
            
            // Drop columns
            $table->dropColumn([
                'language',
                'quality', 
                'has_subtitle',
                'is_dubbed',
                'cast',
                'director',
                'popularity',
                'view_count'
            ]);
        });
    }
};