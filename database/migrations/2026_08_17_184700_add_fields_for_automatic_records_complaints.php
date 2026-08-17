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
        Schema::table('records_complaints', function (Blueprint $table) {
            $table->string('user_agent', 200)->nullable();
            $table->boolean('auto')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records_complaints', function (Blueprint $table) {
            $table->dropColumn('user_agent');
            $table->dropColumn('auto');
        });
    }
};
