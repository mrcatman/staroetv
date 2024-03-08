<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrosspostsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crossposting', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->text('post_data');
            $table->text('post_data_old')->nullable();
            $table->integer('post_ts')->nullable();
            $table->timestamps();
        });
        Schema::create('crossposting_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('crosspost_id');
            $table->integer('status')->default(-1);
            $table->string('service');
            $table->text('error_log')->nullable();
            $table->text('post_ids')->nullable();
            $table->text('media_data')->nullable();
            $table->text('last_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crossposting');
        Schema::dropIfExists('crossposting_posts');
    }
}
