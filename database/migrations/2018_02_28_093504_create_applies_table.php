<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');  // 项目id
            $table->unsignedInteger('task_id');     // 任务id的json数组
            $table->unsignedInteger('wxuser_id');   // 用户id
            $table->unsignedInteger('status')->default(1); // 1审核中，2已通过，3未通过, 4已评价
            $table->string('reason')->nullable();   // 未通过原因
            $table->string('judge')->nullable();    // 评价, 1优秀，2合格
            $table->unsignedInteger('money')->nullable(); // 获取钱
            $table->unsignedInteger('points')->nullable(); // 获得的积分
            $table->boolean('obey')->default(false);    // 是否服从分配
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
        Schema::dropIfExists('applies');
    }
}
