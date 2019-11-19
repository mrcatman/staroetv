<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeToBans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_warnings', function (Blueprint $table) {
            $table->integer('time_expires')->nullable();
            $table->boolean('is_forever')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_warnings', function (Blueprint $table) {
            $table->dropColumn('time_expires');
            $table->dropColumn('is_forever');
        });
    }
}
