<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WxUser extends Model
{
    //
    public $table = 'wxusers';

    protected $hidden = ['wx_session', 'openid', 'token', 'admin_id'];

    protected $appends = ['money', 'points', 'history_money', 'history_points', 'completed_count', 'admin_info'];

    // 用户的实名认证信息
    public function realInfo () {
        return $this->hasOne('App\Models\RealInfo', 'wxuser_id');
    }

    // 用户的后台管理信息
    public function adminInfo () {
        return $this->belongsTo('App\User', 'admin_id');
    }

    /**
     * 用户申请的任务
     */
    public function applies () {
        return $this->hasMany('App\Models\Apply', 'wxuser_id');
    }

    /**
     * 用户申请的所有志愿项目
     */
    public function appliedProjects () {
        return $this->belongsToMany('App\Models\Project', 'applies', 'wxuser_id', 'project_id')
                    ->withPivot('status', 'reason', 'points', 'money')->withTimestamps();
    }

    /**
     * 用户已经完成的项目
     */
    public function completedProjects () {
        return $this->belongsToMany('App\Models\Project', 'applies', 'wxuser_id', 'project_id')
                    ->as('applyInfo')
                    ->wherePivot('status', 4)->withPivot('status', 'reason', 'points', 'money', 'updated_at');
    }

    public function workingProjects() {
        return $this->belongsToMany('App\Models\Project', 'applies', 'wxuser_id', 'project_id')
        ->with('tasks')
        ->wherePivot('status', 2)
        ->withPivot('task_id')
        ->as('applyInfo');
    }

    /**
     * 用户收益的现金
     */
    public function money() {
        return $this->belongsToMany('App\Models\Project', 'applies', 'wxuser_id', 'project_id')
            ->as('applyInfo')
            ->wherePivot('status', 4)->withPivot('money')
            ->get()
            ->sum('applyInfo.money');
    }

    /**
     * 用户收益的积分
     */
    public function points() {
        return $this->belongsToMany('App\Models\Project', 'applies', 'wxuser_id', 'project_id')
            ->as('applyInfo')
            ->wherePivot('status', 4)->withPivot('points')
            ->get()
            ->sum('applyInfo.points');
    }

    public function getHistoryMoneyAttribute()
    {
        return $this->money();
    }
    public function getHistoryPointsAttribute() {
        return $this->points();
    }
    public function getMoneyAttribute()
    {
        return $this->money();
    }
    public function getPointsAttribute() {
        return $this->points();
    }
    public function getWorkingProjectsAttribute() {
        return $this->workingProjects();
    }
    public function getCompletedCountAttribute() {
        return $this->belongsToMany('App\Models\Project', 'applies', 'wxuser_id', 'project_id')
            ->as('applyInfo')
            ->wherePivot('status', 4)
            ->get()
            ->count();
    }
    public function getAdminInfoAttribute() {
        $adminInfo = $this->belongsTo('App\User', 'admin_id')->get()->makeHidden(['created_at', 'updated_at'])->first();
        return $adminInfo;
    }
}
