<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WxUser;
use App\Models\Apply;
use App\Models\Task;
use App\Http\Response;
use App\Models\Project;
use Carbon\Carbon;

class ApplyController extends Controller
{
    private function user($token) {
        return WxUser::where('token', $token)->first();
    }

    // 申请志愿者项目
    public function apply(Request $request) {
        $request->validate([
            'tasks' => 'bail|required|array|exists:tasks,id',
        ]);
        try {
            $taskIds = $request->input('tasks', false);            
            $project = Task::find($taskIds[0])->project;
            if ($project->end < Carbon::now()) {
                return Response::wrongParams([
                    'errMsg' => '当前项目已结束',
                ]);
            }
            $projectTaskIds = $project->tasks->pluck('id')->toArray();
            $result = array_intersect($taskIds, $projectTaskIds);
            if (array_diff_assoc($result, $taskIds)) {
                return Response::wrongParams();
            }
            $user = $this->user($request->header('token'));
            if (!$user->realInfo) {
                return [
                    'errcode' => 133,
                    'errMsg' => '用户未实名认证',
                ];
            }
            $userId = $user->id;
            $obey = $request->input('obey', false);
            $projectId = $project->id;
            $exists = Apply::where('project_id', $projectId)->where('wxuser_id', $userId)->first();
            $reapply = $request->input('reapply', false);
            if ($exists && !$reapply) {
                return [
                    'errcode' => 134,
                    'errMsg' => '用户已报名',
                ];
            } else if($exists && $reapply) {
                Apply::where('project_id', $projectId)->where('wxuser_id', $userId)->delete();
            }
            for($i = 0; $i < count($taskIds); $i++) {
                $apply = new Apply();
                $apply->project_id = $projectId;
                $apply->task_id = $taskIds[$i];
                $apply->wxuser_id = $userId;
                $apply->obey = $obey;
                $apply->save();
            }
            return Response::success([
                'applyId' => $apply->id,
            ]);
        } catch (\Exception $e) {
            throw $e;
            return Response::wrongParams();
        }
    }

    /**
     * 管理员处理用户的申请的方法
     */
    public function dealApply(Request $request) {
        $request->validate([
            'wxuserId' => 'bail|required|integer|exists:applies,wxuser_id',
            'projectId' => 'bail|required|integer|exists:applies,project_id',
            'dealType' => 'bail|required|integer|between:2, 4',
            'taskId' => 'bail|required_if:dealType,2|integer|exists:applies,task_id',
            'reason' => 'bail|required_if:dealType,3|string',
            'judge' => 'bail|required_if:dealType,4|integer|between:1,3'
        ]);
        $projectId = $request->input('projectId');
        $taskId = $request->input('taskId');
        $dealType = $request->input('dealType');
        $userId = $request->input('wxuserId');
        $apply = Apply::where('project_id', $projectId)->where('wxuser_id', $userId)->first();
        
        if (!$apply) {
            return [
                'errcode' => 404,
                'errMsg' => '没有此申请记录',
            ];
        }
        if ($dealType == 2) { //通过审核
            $apply = Apply::where('project_id', $projectId)->where('wxuser_id', $userId)->where('task_id', $taskId)->first();
            $res = $this->dealPass($apply, $taskId);
            // 删除其它的申请记录
            Apply::where('project_id', $projectId)->where('wxuser_id', $userId)->where('task_id', '<>', (int)$taskId)->delete();
            return $res ? Response::success() : Response::wrongParams();
        } else if ($dealType == 3) {
            
            $applies = Apply::where('project_id', $projectId)->where('wxuser_id', $userId);
            $res = $this->dealUnpass($applies, $request->input('reason'));
            return $res ? Response::success() : Response::wrongParams();
        } else if ($dealType == 4) {
            
            $res = $this->dealJudge($apply, $request->input('judge'));
            // 删除其它的申请记录
            Apply::where('project_id', $projectId)->where('wxuser_id', $userId)->where('status', '<>', 4)->delete();
            return $res ? Response::success() : Response::wrongParams();
        }
        return Response::wrongParams();
    }

    private function dealPass($apply, $taskId) {
        $apply->status = 2;
        $apply->save();
        return true;
    }

    private function dealJudge($apply, $judge) {
        $project = Project::find($apply->project_id);
        $money = $project->money;
        $points = $project->points;

        if ($judge == 2) {
            $apply->money = $money / 2;
            $apply->points = $points / 2;
        } else if($judge == 1) {
            $apply->money = $money;
            $apply->points = $points;
        }
        $apply->status = 4;
        $apply->judge = $judge;
        $apply->save();
        return true;
    }

    private function dealUnpass($applies, $reason) {
        $applies->update(['status' => 3, 'reason' => $reason]);
        return true;
    }
}
