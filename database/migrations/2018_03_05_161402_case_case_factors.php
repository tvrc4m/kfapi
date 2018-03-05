<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CaseCaseFactors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('case_case_factors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('case_id')->default(0)->unsigned()->comment('对应的案例ID');
            $table->integer('case_factor_id')->default(0)->unsigned()->comment('案例对应的要素ID');
            $table->tinyInteger('stat')->default(1)->unsigned()->comment('状态 1正常 0不正常');
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
        Schema::dropIfExists('case_case_factors');
    }
}
