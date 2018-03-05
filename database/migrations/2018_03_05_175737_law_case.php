<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LawCase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('law_cases', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('law_id')->default(0)->unsigned()->comment('法规id');
            $table->integer('case_id')->default(0)->unsigned()->comment('案例id');
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
        Schema::dropIfExists('law_cases');
    }
}
