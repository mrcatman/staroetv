<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRadio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('videos', 'records');
        Schema::table('channels', function (Blueprint $table) {
           $table->boolean('is_radio')->default(false);
        });
        Schema::table('records', function (Blueprint $table) {
            $table->boolean('is_radio')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('records', 'videos');
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('is_radio');
        });
        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn('is_radio');
        });
    }
}
