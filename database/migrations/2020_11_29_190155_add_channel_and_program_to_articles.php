<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChannelAndProgramToArticles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('url')->nullable();
        });
        Schema::create('articles_bindings', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('article_id');
            $table->integer('channel_id')->nullable();
            $table->integer('program_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('url');
        });
        Schema::dropIfExists('articles_bindings');
    }
}
