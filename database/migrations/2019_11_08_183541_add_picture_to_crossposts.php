<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPictureToCrossposts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crossposts', function (Blueprint $table) {
            $table->string('picture')->nullable();
            $table->string('link')->nullable();
            $table->text('text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crossposts', function (Blueprint $table) {
            $table->dropColumn('picture');
            $table->dropColumn('link');
            $table->dropColumn('text');
        });
    }
}
