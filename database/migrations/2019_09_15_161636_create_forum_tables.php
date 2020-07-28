<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forums', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('parent_id')->nullable();
            $table->smallInteger('state')->default(1);
            $table->string('title');
            $table->string('description')->nullable();
            $table->integer('topics_count')->default(0);
            $table->integer('replies_count')->default(0);
            $table->datetime('last_reply_at')->nullable();
            $table->string('can_post')->nullable();
            $table->string('can_create_topics')->nullable();
            $table->string('can_view')->nullable();
            $table->string('last_username')->nullable();
            $table->integer('last_topic_id')->nullable();
            $table->string('last_topic_name')->nullable();
            $table->timestamps();
        });
        Schema::create('forum_topics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('forum_id');
            $table->boolean('is_poll')->default(false);
            $table->boolean('is_fixed')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->boolean('first_message_fixed')->default(false);
            $table->datetime('last_reply_at')->nullable();
            $table->integer('answers_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->string('title');
            $table->string('description')->nullable();
            $table->integer('topic_starter_id')->nullable();
            $table->string('topic_starter_username')->nullable();
            $table->string('topic_last_username')->nullable();
            $table->timestamps();
        });
        Schema::create('forum_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('topic_id');
            $table->integer('forum_id')->nullable();
            $table->boolean('is_first')->default(false);
            $table->text('content')->nullable();
            $table->string('username')->nullable();
            $table->string('edited_by')->nullable();
            $table->dateTime('edited_at')->nullable();
            $table->string('ip')->nullable();
            $table->text('questionnaire')->nullable();
            $table->integer('user_id')->nullable();
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
        Schema::dropIfExists('forums');
        Schema::dropIfExists('forum_topics');
        Schema::dropIfExists('forum_messages');
    }
}
