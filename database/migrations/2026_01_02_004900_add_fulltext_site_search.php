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
        Schema::table('records', function (Blueprint $table) {
            $table->fullText(['title', 'short_description', 'description']);
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->fullText(['title', 'short_content', 'content']);
        });
        Schema::table('channels', function (Blueprint $table) {
            $table->fullText(['name', 'description']);
        });
        Schema::table('programs', function (Blueprint $table) {
            $table->fullText(['name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropFullText(['title', 'short_description', 'description']);
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->dropFullText(['title', 'short_content', 'content']);
        });
        Schema::table('channels', function (Blueprint $table) {
            $table->dropFullText(['name', 'description']);
        });
        Schema::table('programs', function (Blueprint $table) {
            $table->dropFullText(['name', 'description']);
        });
    }
};
