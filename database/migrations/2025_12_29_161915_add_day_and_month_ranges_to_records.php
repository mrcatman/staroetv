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
            $table->integer('day_start')->nullable();
            $table->integer('month_start')->nullable();
            $table->integer('day_end')->nullable();
            $table->integer('month_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn('day_start');
            $table->dropColumn('month_start');
            $table->dropColumn('day_end');
            $table->dropColumn('month_end');
        });
    }
};
