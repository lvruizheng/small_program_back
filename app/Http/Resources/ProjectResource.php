<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Models\WxUser;
use App\Models\Apply;

class ProjectResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $token = $request->header('token');
        $user = WxUser::where('token', $token)->first();
        $applyInfo = NULL;
        if ($user) {
            $apply = Apply::where('project_id', $this->id)->where('wxuser_id', $user->id);
            $appliedTasks = $apply->pluck('task_id')->toArray();
            $applyInfo = $apply->first();
        }
        $format = [
            'id' => $this->id,
            'title' => $this->title,
            'intro' => $this->introduce,
            'image' => $this->image,
            'location' => $this->location,
            'start' => $this->start,
            'end' => $this->end,
            'money' => $this->money,
            'points' => $this->points,
            'need' => $this->need,
            'current' => $this->users()->get()->count(),
            'tasks' => TaskResource::collection($this->tasks),
            'showObey' => $this->show_obey,
            $this->mergeWhen($user && $applyInfo, [
                'applyInfo' => [
                    'status' => $applyInfo?$applyInfo->status:NULL,
                    'reason' => $applyInfo?$applyInfo->reason:NULL,
                    'tasks' => $applyInfo?$appliedTasks: NULL,
                    'judge' => $applyInfo?$applyInfo->judge:NULL,
                    'points' => $applyInfo?$applyInfo->points:NULL,
                    'money' => $applyInfo?$applyInfo->money:NULL,
                    'updateTime' => $applyInfo&&$applyInfo->updated_at?$applyInfo->updated_at->format('Y-m-d H:i:s'):'app',
                ]
            ]),
            $this->mergeWhen($request->user(), [
                'pass' => $this->passUsers()->get()->count(),
                'wait_handle' => $this->waitHandleUsers()->get()->count(),
            ]),
        ];
        return $format;
    }
}
