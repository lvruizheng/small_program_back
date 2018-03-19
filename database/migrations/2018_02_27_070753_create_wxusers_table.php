<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWxusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wxusers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('openid')->unique();
            $table->string('wx_session');
            $table->string('token', 100)->unique();
            $table->string('avatar')->nullable();
            $table->string('nick_name');
            $table->string('gender');
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
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
        Schema::dropIfExists('wxusers');
    }
}
