<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Law extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laws', function (Blueprint $table) {
            $table->increments('id');
            $table->string('entry')->default('')->comment('条目');
            $table->text('content')->default('')->comment('内容');
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
        Schema::dropIfExists('laws');
    }
}
