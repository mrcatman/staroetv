<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterprogramPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interprogram_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id');
            $table->string('name')->nullable();
            $table->integer('day_start')->nullable();
            $table->integer('month_start')->nullable();
            $table->integer('year_start')->nullable();
            $table->integer('day_end')->nullable();
            $table->integer('month_end')->nullable();
            $table->integer('year_end')->nullable();
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->timestamps();
        });
        Schema::create('interprogram_packages_pictures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('package_id');
            $table->integer('picture_id');
        });
        Schema::table('videos', function (Blueprint $table) {
            $table->integer('interprogram_package_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interprogram_packages');
        Schema::dropIfExists('interprogram_packages_pictures');
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('interprogram_package_id');
        });
    }
}
