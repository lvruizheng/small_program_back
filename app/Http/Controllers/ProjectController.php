<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Http\Resources\ProjectResource;
use Illuminate\Support\Facades\DB;
use App\Http\Response;
use App\Models\WxUser;
use App\Http\Resources\WxUserResource;
use Illuminate\Validation\Rule;
use App\Models\Apply;
use App\Models\RealInfo;

class ProjectController extends Controller
{
    /**
     * 分页获取所有的志愿项目列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return ProjectResource::collection(Project::orderBy('id', 'desc')->paginate($request->input('size', 10)));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->input();
        DB::beginTransaction();
        try{
            $project = new Project();
            $project->title = $input['title'];
            $project->introduce = $input['intro'];
            $project->location = $input['location'];
            $project->start = $input['start'];
            $project->end = $input['end'];
            $project->money = $input['money'];
            $project->points = $input['points'];
            $project->need = $input['need'];
            $project->image = $input['image'];
            $project->show_obey = $input['showObey'];
            $project->publisher_id = $request->user()->id;
            $project->save();
            $tasks = $input['tasks'];
            $count = count($tasks);
            for ($i = 0; $i < $count; $i++) {
                $task = $tasks[$i];
                $taskModel = new Task();
                $taskModel->title = $task['title'];
                $taskModel->introduce = $task['introduce'];
                $taskModel->location = $task['location'];
                $taskModel->start = $task['start'];
                $taskModel->end = $task['end'];
                $taskModel->project_id = $project->id;
                $taskModel->save();
            }
            DB::commit();
            return Response::success([
                'projectId' => $project->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
            return Response::wrongParams();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getProjectInfo(Request $request)
    {
        $id = $request->input('projectId');
        if (!$id) {
            return Response::wrongParams();
        }
        $project = Project::find($id);
        if ($project) {
            return new ProjectResource($project);
        } else {
            return Response::wrongParams();
        }

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->input('id');
        $project = Project::find($id);
        if(!$project) {
            return Response::wrongParams();
        }
        $input = $request->input();
        DB::beginTransaction();
        try{
            $project->title = $input['title'];
            $project->introduce = $input['intro'];
            $project->location = $input['location'];
            $project->start = $input['start'];
            $project->end = $input['end'];
            $project->money = $input['money'];
            $project->point = $input['points'];
            $project->total_need = $input['need'];
            $project->save();
            $tasks = $input['tasks'];
            $count = count($tasks);
            for ($i = 0; $i < $count; $i++) {
                $task = $tasks[$i];
                $taskModel = new Task();
                $taskModel->title = $task['title'];
                $taskModel->introduce = $task['intro'];
                $taskModel->location = $task['location'];
                $taskModel->start = $task['start'];
                $taskModel->end = $task['end'];
                $taskModel->project_id = $project->id;
                $taskModel->save();
            }
            DB::commit();
            return Response::success([
                'projectId' => $project->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
            return Response::wrongParams();
        }
    }

    /**
     * 获取用户参与的项目（已经完成并给予评价的)
     */
    public function getMyCompletedProjects(Request $request) {
        $user = WxUser::where('token', $request->header('token'))->first();
        $completedProjects = $user->appliedProjects()->where('status', 4);
        $size = $request->input('size', 10);
        $page = $request->input('page', 1);
        return ProjectResource::collection($completedProjects->paginate($size)->appends(['size' =>(string)10]));
    }

    /**
     * 删除项目
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'projectId' => 'bail|required|exists:projects,id'
        ]);
        try {
            $project = Project::find($id);
            $project->tasks()->delete();
            Project::destroy($id);
            return Response::success();
        } catch (\Exception $e) {
            throw $e;
            return [
                'errcode' => 135,
                'errMsg' => '删除失败',
            ];
        }
    }

    // 获取一个项目的所有志愿者
    public function getProjectWxusers(Request $request) {
        $userInfoTypes = ['name', 'mobile', 'id_number', 'sex', 'school', 'school_area', 'role'];
        $applyInfoTypes = ['start', 'end', 'duty', 'status', 'judge'];
        $request->validate([
            'projectId' => 'required|integer|exists:projects,id',
            'size' => 'number',
        ]);
        
        $size = $request->input('size', 10);

        // $users = Project::find($projectId)->users()->with('realInfo');

        // 过滤实名认证表
        $input = $request->only(['name', 'sex', 'id_number', 'mobile', 'school', 'school_area']);
        $isFirst = true;
        $realInfos = RealInfo::all();
        foreach($input as $key => $value) {
            if ($isFirst) {
                $realInfos = RealInfo::where($key, 'like', "%$value%");
                $isFirst = false;
            } else {
                $realInfos = $realInfos->where($key, 'like', "%$value%");
            }
        }
        $realInfoWxuserIds = $realInfos->pluck('wxuser_id');
        
        // 过滤申请表
        $projectId = $request->input('projectId');
        $applyFilters = array();
        if ($request->has('judge')) {
            $applyFilters = $request->only('judge');
        } else if($request->input('status') != 5) {
            $applyFilters = $request->only('status');
        }
        if ($request->has('duty')) {
            $applyFilters['task_id'] = $request->input('duty');
        }
        $applyFilters['project_id'] = $projectId;
        
        $applies = Apply::where($applyFilters);
        if (!$request->has('judge') && $request->input('status') == 5) {
            $applies->whereIn('status', [2, 4]);
        }
        if ($request->has(['start', 'end'])) {
            $applies = $applies->whereBetween('created_at', [$request->input('start'), $request->input('end')]);
        }
        $applyWxuserIds = $applies->pluck('wxuser_id');

        $resultWxuserIds = array_intersect($realInfoWxuserIds->toArray(), $applyWxuserIds->toArray());
        // if (!count($resultWxuserIds)) {
        //     return [
        //         "current_page" => 1,
        //         "data" => [],
        //         "first_page_url" => "http://localhost/api/admin/project/wxuser?size=$size&page=1",
        //         "from" => 0,
        //         "last_page" => 1,
        //         "last_page_url" => "http://localhost/api/admin/project/wxuser?size=$size&page=1",
        //         "next_page_url" => null,
        //         "path" => "http://localhost/api/admin/project/wxuser",
        //         "per_page" => $size,
        //         "prev_page_url" => null,
        //         "to" => 0,
        //         "total" => 0
        //     ];
        // }
        $users = Project::find($projectId)->users()->whereIn('wxuser_id', $resultWxuserIds);
        $paginate = $users->paginate($size)->appends([
            'size' => (string)$size,
            'projectId' => (string)$projectId]);
        return WxUserResource::collection($paginate);
    }

    // 获取一个项目的单个志愿者
    public function getWxUserByProject(Request $request) {
        $request->validate([
            'projectId' => 'required|integer|exists:applies,project_id',
            'wxuserId' => 'required|integer|exists:applies,wxuser_id'
        ]);
        $wxuserId = $request->input('wxuserId');
        $projectId = $request->input('projectId');
        $wxuser = Project::find($projectId)->users()->where('wxusers.id', $wxuserId)->first();
        // return $wxuser;
        if (!$wxuser) {
            return [
                'errcode' => 404,
                'errMsg' => '没有找到该用户',
            ];
        }
        return new WxUserResource($wxuser);
    }

    // 获取一个项目的任务
    public function getTasks(Request $request) {
        $request->validate([
            'projectId' => 'required|integer|exists:projects,id',
        ]);
        return Project::find($request->input('projectId'))->tasks;
    }
}
