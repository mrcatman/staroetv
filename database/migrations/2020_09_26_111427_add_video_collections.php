<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVideoCollections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('parent_id')->nullable();
            $table->string('parent_type')->nullable();
            $table->integer('main_material_id')->nullable();
            $table->integer('order')->default(0);
            $table->integer('user_id')->nullable();
            $table->timestamps();
        });
        Schema::create('collections_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('collection_id');
            $table->integer('material_id')->nullable();
            $table->integer('order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collections');
        Schema::dropIfExists('collections_records');
    }
}
