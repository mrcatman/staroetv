<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProgramIdToInterprogramPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interprogram_packages', function (Blueprint $table) {
            $table->integer('channel_id')->nullable()->change();
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
        Schema::table('interprogram_packages', function (Blueprint $table) {
            $table->dropColumn('program_id')->nullable();
        });
    }
}
