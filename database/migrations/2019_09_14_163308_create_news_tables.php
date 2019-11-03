<?php

use App\Helpers\CSVHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('original_id')->nullable();
            $table->integer('type_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('day')->nullable();
            $table->boolean('pending')->default(false);
            $table->string('username')->nullable();
            $table->text('title')->nullable();
            $table->text('short_content')->nullable();
            $table->mediumText('content')->nullable();
            $table->integer('views')->default(0);
            $table->string('ip')->nullable();
            $table->string('cover')->nullable();
            $table->string('cover_text')->nullable();
            $table->string('source')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('path')->nullable();
            $table->timestamps();
        });

        Schema::create('articles_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('original_id');
            $table->integer('type_id');
            $table->string('url')->nullable();
            $table->string('title')->nullable();
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
        Schema::dropIfExists('articles');
        Schema::dropIfExists('articles_categories');
    }
}
