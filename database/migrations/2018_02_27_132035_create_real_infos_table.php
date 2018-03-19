<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRealInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('real_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('photo');    // 真实照片
            $table->string('name');
            $table->string('id_number');
            $table->string('sex');
            $table->string('school');
            $table->string('school_area');
            $table->boolean('has_agent');
            $table->boolean('has_volunteer');
            $table->text('experience')->nullable();
            $table->string('mobile');
            $table->unsignedInteger('wxuser_id');
            $table->unsignedInteger('status')->default(1);  // 1未审核
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
        Schema::dropIfExists('real_infos');
    }
}
