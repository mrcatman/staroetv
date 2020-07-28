<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddViewsToSomeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    protected $tables = ['interprogram_packages', 'events', 'programs'];
    public function up() {
        foreach ($this->tables as $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->integer('views')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->dropColumn('views');
            });
        }
    }
}
