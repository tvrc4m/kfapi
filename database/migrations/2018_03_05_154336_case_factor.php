<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CaseFactor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('case_factors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('名称');
            $table->integer('pid')->default(0)->unsigned()->comment('父id');
            $table->integer('count')->default(0)->unsigned()->comment('要素个数');
            $table->integer('weight')->default(100)->unsigned()->comment('要素权重');
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
        Schema::dropIfExists('case_factors');
    }
}
