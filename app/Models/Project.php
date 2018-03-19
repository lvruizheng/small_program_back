<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * 当前志愿项目的任务
     */
    public function tasks() {
        return $this->hasMany('App\Models\Task');
    }

    /**
     * 
     */
    public function apply() {
        return $this->hasMany('App\Models\Apply');
    }

    /**
     * 当前志愿的申请用户
     */
    public function users() {
        return $this->belongsToMany('App\Models\WxUser', 'applies', 'project_id', 'wxuser_id')
                    ->withPivot('points', 'money', 'status')
                    ->distinct();       // 去除重复用户
    }

    public function passUsers() {
        return $this->users()->whereIn('status', [2, 4]);
    }

    public function waitHandleUsers() {
        return $this->users()->where('status', 1);
    }
}
