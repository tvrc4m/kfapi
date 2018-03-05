<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LowFactor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('low_factors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('low_id')->default(0)->unsigned()->comment('法规id');
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
        Schema::dropIfExists('low_factors');
    }
}
