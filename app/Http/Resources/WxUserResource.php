<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Models\Apply;
use App\Models\Task;

class WxUserResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $realInfo = $this->realInfo;
        $projectId = $request->input('projectId');
        $applies = Apply::where('project_id', $projectId)->where('wxuser_id', $this->id)->get();
        $apply = $applies->first();

        $baseInfo = [
            'id' => $this->id,
            'real_info' => $this->realInfo,
            "avatar" => $this->avatar,
            "nick_name" => $this->nick_name,
        ];
        $applyInfo = [
            'tasks' => Task::find($applies->pluck('task_id')),
            'obey' => $apply->obey,
            'applyTime' => $apply->created_at->format('Y-m-d H:i:s'),
            'status' => $apply->status,
            'judge' => $apply->judge,
            'reason' => $apply->reason,
        ];
        return array_merge(['applyInfo' => $applyInfo], $baseInfo);
    }
}
