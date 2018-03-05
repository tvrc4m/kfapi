<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('名称');
            $table->integer('case_cate_id')->default(0)->unsigned()->comment('分类ID');
            $table->integer('province_id')->default(0)->unsigned()->comment('省份ID');
            $table->integer('city_id')->default(0)->unsigned()->comment('城市ID');
            $table->timestamp('date')->comment('日期');
            $table->text('info')->default('')->comment('案情');
            $table->text('judgment')->default('')->comment('判决');
            $table->text('suggest')->default('')->comment('建议');
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
        Schema::dropIfExists('cases');
    }
}
