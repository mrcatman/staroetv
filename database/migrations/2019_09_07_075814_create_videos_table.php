<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('is_from_ucoz')->default(false);
            $table->datetime('original_added_at')->nullable();
            $table->string('author_username')->nullable();
            $table->integer('author_id')->nullable();
            $table->string('title')->default("");
            $table->text('description')->nullable();
            $table->text('short_contents')->nullable();
            $table->text('embed_code')->nullable();
            $table->integer('views')->default(0);
            $table->integer('year')->nullable();
            $table->integer('year_end')->nullable();
            $table->integer('month')->nullable();
            $table->integer('day')->nullable();
            $table->date('date')->nullable();
            $table->integer('channel_id')->nullable();
            $table->integer('program_id')->nullable();
            $table->integer('ucoz_id')->nullable();
            $table->text('ucoz_url')->nullable();
            $table->text('cover')->nullable();
            $table->boolean('is_interprogram')->default(false);
            $table->string('short_description')->nullable();
            $table->string('original_url')->nullable();
            $table->timestamps();
        });
        Schema::create('channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('programs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id')->nullable();
            $table->text('cover');
            $table->string('name');
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
        Schema::dropIfExists('videos');
        Schema::dropIfExists('channels');
        Schema::dropIfExists('programs');
    }
}
