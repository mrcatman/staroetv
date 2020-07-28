<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->boolean('order')->nullable();
        });
        Schema::table('programs', function (Blueprint $table) {
            $table->boolean('order')->nullable();
        });
        Schema::table('videos', function (Blueprint $table) {
            $table->boolean('order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
}
