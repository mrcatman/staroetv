<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('private_messages', function (Blueprint $table) {
            $table->boolean('is_group')->nullable();
            $table->string('group_ids')->nullable();
            $table->text('read_ids')->nullable();
            $table->text('deleted_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('private_messages', function (Blueprint $table) {
            $table->dropColumn('is_group');
            $table->dropColumn('group_ids');
            $table->dropColumn('read_ids');
            $table->dropColumn('deleted_ids');
        });
    }
}
