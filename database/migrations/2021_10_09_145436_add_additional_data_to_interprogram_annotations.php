<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalDataToInterprogramAnnotations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interprogram_annotations', function (Blueprint $table) {
            $table->string('annotation_type')->nullable();
            $table->text('annotation_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interprogram_annotations', function (Blueprint $table) {
            $table->dropColumn('annotation_type');
            $table->dropColumn('annotation_data');
        });
    }
}
