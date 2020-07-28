<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtendUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //$table->dropUnique('users_email_unique');
            $table->string('username');
            $table->integer('original_id')->nullable();
            $table->bigInteger('ucoz_uid')->nullable();
            $table->integer('avatar_id')->nullable();
            $table->integer('group_id')->default(0);
            $table->string('ip_address')->nullable();
            $table->string('ip_address_reg')->nullable();
            $table->string('user_comment')->nullable();
            $table->text('signature')->nullable();
            $table->datetime('was_online')->nullable();
        });
        Schema::create('users_meta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('gender')->default(0);
            $table->string('yandex_video')->default('');
            $table->string('youtube')->default('');
            $table->string('country')->default('');
            $table->string('city')->default('');
            $table->string('vk')->default('');
            $table->string('facebook')->default('');
            $table->date('date_of_birth')->nullable();
        });
        Schema::create('users_reputation', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('from_id');
            $table->integer('to_id');
            $table->integer('weight')->default(1);
            $table->text('comment')->nullable();
            $table->text('link')->nullable();
            $table->text('reply_comment')->nullable();
            $table->timestamps();
        });
        Schema::create('users_awards', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('from_id');
            $table->integer('to_id');
            $table->integer('award_id');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
        Schema::create('users_warnings', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('from_id');
            $table->integer('to_id');
            $table->integer('weight')->default(1);
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
            $table->dropColumn('original_id');
            $table->dropColumn('ucoz_uid');
            $table->dropColumn('avatar_id');
            $table->dropColumn('group_id');
            $table->dropColumn('ip_address');
            $table->dropColumn('ip_address_reg');
            $table->dropColumn('user_comment');
            $table->dropColumn('signature');
            $table->dropColumn('was_online');
        });
        Schema::dropIfExists('users_meta');
        Schema::dropIfExists('users_reputation');
        Schema::dropIfExists('users_awards');
        Schema::dropIfExists('users_warnings');
    }
}
