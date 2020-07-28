<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTagToPictures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pictures', function (Blueprint $table) {
            $table->string('tag')->nullable();
        });
        Schema::table('user_groups', function (Blueprint $table) {
            $table->string('icon_id')->nullable();
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->string('cover_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pictures', function (Blueprint $table) {
            $table->dropColumn('tag');
        });
        Schema::table('user_groups', function (Blueprint $table) {
            $table->dropColumn('icon_id');
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('cover_id');
        });
    }
}
