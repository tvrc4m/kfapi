<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LawCate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('law_cates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('分类名称');
            $table->string('short_name')->default('')->comment('简称');
            $table->string('pingyin')->default('')->comment('拼音缩写');
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
        Schema::dropIfExists('law_cates');
    }
}
