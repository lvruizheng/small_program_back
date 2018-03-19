<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('introduce')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->integer('points');
            $table->integer('money');
            $table->integer('need');
            $table->string('image');
            $table->boolean('show_obey')->default(false);   // 是否显示服从分配
            $table->integer('duty_limit')->default(0);   // 职责数目限制
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
        Schema::dropIfExists('projects');
    }
}
