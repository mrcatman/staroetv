<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuestionnaireTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('topic_id');
            $table->string('title')->nullable();
            $table->boolean('multiple_variants')->default(false);
        });
        Schema::create('questionnaires_variants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('questionnaire_id');
            $table->integer('order')->default(0);
            $table->string('title')->nullable();
            $table->integer('initial_count')->default(0);
        });
        Schema::create('questionnaires_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('variant_id');
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
        Schema::dropIfExists('questionnaire');
        Schema::dropIfExists('questionnaire_variants');
        Schema::dropIfExists('questionnaire_answers');
    }
}
