<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    public function project() {
        return $this->belongsTo('App\Models\Project');
    }

    public function users() {
        return $this->belongsToMany('App\Models\WxUser', 'applies', 'task_id', 'wxuser_id')
                    ->withPivot('applyInfo');
    }

    protected $hidden = ['created_at', 'updated_at'];
}
