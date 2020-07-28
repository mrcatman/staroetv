<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChannelAndYearToVideoCuts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_cuts', function (Blueprint $table) {
            $table->integer('channel_id')->nullable();
            $table->integer('year')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('video_cuts', function (Blueprint $table) {
            $table->dropColumn('channel_id');
            $table->dropColumn('year');
        });
    }
}
