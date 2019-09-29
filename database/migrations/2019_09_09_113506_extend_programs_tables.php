<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtendProgramsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('url')->nullable();
            $table->integer('author_id')->nullable();
            $table->boolean('pending')->default(false);
            $table->integer('cover_id')->nullable();
            $table->longText('description')->nullable();
            $table->date('date_of_start')->nullable();
            $table->date('date_of_closedown')->nullable();
            $table->integer('genre_id')->nullable();
        });
        Schema::create('genres', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url')->nullable();
            $table->string('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('genres');
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->dropColumn('author_id');
            $table->dropColumn('pending');
            $table->dropColumn('cover_id');
            $table->dropColumn('description');
            $table->dropColumn('date_of_start');
            $table->dropColumn('date_of_closedown');
            $table->dropColumn('genre_id');
        });
    }
}
