<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeletextTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teletext', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('quality')->default(9);
            $table->integer('channel_id');
            $table->integer('author_id');
            $table->boolean('pending');

            $table->text('pages')->nullable();

            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('day')->nullable();

            $table->dateTime('date')->nullable();

            $table->integer('cover_id')->nullable();
            $table->integer('views')->default(0);
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
        Schema::dropIfExists('teletext');
    }
}
