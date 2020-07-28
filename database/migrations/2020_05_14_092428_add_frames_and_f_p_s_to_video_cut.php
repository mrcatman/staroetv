<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFramesAndFPSToVideoCut extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_cuts', function (Blueprint $table) {
            $table->integer('frames')->nullable();
            $table->integer('fps')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('video_cuts', function (Blueprint $table) {
            $table->dropColumn('frames');
            $table->dropColumn('fps');
        });
    }
}
