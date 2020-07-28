<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url')->nullable();
            $table->integer('user_id')->nullable();
            $table->dateTime('date');
            $table->dateTime('date_end')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('cover_id')->nullable();
        });
        Schema::create('events_videos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('event_id');
            $table->integer('video_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('events_videos');
    }
}
