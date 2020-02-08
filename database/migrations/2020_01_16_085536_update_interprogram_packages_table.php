<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateInterprogramPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interprogram_packages', function (Blueprint $table) {
            $table->dropColumn('day_start');
            $table->dropColumn('month_start');
            $table->dropColumn('year_start');
            $table->dropColumn('day_end');
            $table->dropColumn('month_end');
            $table->dropColumn('year_end');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
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
            $table->integer('day_start')->nullable();
            $table->integer('month_start')->nullable();
            $table->integer('year_start')->nullable();
            $table->integer('day_end')->nullable();
            $table->integer('month_end')->nullable();
            $table->integer('year_end')->nullable();
            $table->dropColumn('date_start');
            $table->dropColumn('date_end');
        });
    }
}
