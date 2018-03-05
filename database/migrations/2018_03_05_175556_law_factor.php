<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LawFactor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('law_factors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('law_id')->default(0)->unsigned()->comment('法规id');
            $table->integer('case_factor_id')->default(0)->unsigned()->comment('要素关键词id');
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
        Schema::dropIfExists('law_factors');
    }
}
