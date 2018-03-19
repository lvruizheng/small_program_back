<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apply extends Model
{
    /**
     * 模型日期的存储格式
     *
     * @var string
     */
    // protected $dateFormat = 'U';

    /**
     * 在数组中隐藏的属性
     *
     * @var array
     */
    protected $hidden = ['project_id', 'wxuser_id'];
}
