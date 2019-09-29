<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtendChannelsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->string('url')->nullable();
            $table->integer('logo_id')->nullable();
            $table->integer('author_id')->nullable();
            $table->boolean('pending')->default(false);
            $table->longText('description')->nullable();
            $table->boolean('is_regional')->default(false);
            $table->boolean('is_abroad')->default(false);
            $table->string('region')->nullable();
            $table->date('date_of_start')->nullable();
            $table->date('date_of_closedown')->nullable();
        });
        Schema::create('channels_names', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id');
            $table->string('name');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->integer('logo_id')->nullable();
            $table->timestamps();
        });
        Schema::table('videos', function (Blueprint $table) {
            $table->integer('cover_id')->nullable();
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
            $table->dropColumn('url');
            $table->dropColumn('logo_id');
            $table->dropColumn('author_id');
            $table->dropColumn('pending');
            $table->dropColumn('description');
            $table->dropColumn('is_regional');
            $table->dropColumn('is_abroad');
            $table->dropColumn('region');
            $table->dropColumn('date_of_start');
            $table->dropColumn('date_of_closedown');
        });
        Schema::dropIfExists('channels_names');
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('cover_id');
        });
    }
}
