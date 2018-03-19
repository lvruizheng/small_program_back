<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealInfo extends Model
{
    public function wxuser() {
        return $this->belongsTo('App\Models\WxUser', 'wxuser_id');
    }
}
