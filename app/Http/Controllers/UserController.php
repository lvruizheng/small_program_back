<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Response;
use App\Http\Resources\UserResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\WxUser;
use Illuminate\Validation\Rule;
use App\Models\RealInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class UserController extends Controller
{
    // 创建一个新用户
    public function create(Request $request) {
        $validatedData = $request->validate([
            'username' => 'required|unique:users,name|string|between:5,30',
            'password' => 'required|string|between:6,50',
            'wxuserId' => 'nullable|integer|exists:wxusers,id',
        ]);
        $currentUser = $request->user();
        if (!$currentUser->is_manager) {
            return [
                'errcode' => 138,
                'errMsg' => '没有权限',
            ];
        }
        DB::beginTransaction();
            try {
            $user = new User();
            $user->name = $request->input('username');
            $user->password = bcrypt($request->input('password'));
            $user->is_admin = $currentUser->is_admin ? $request->input('isAdmin', false) : false;
            $user->is_operator = $currentUser->is_admin ? $request->input('isOperator', false) : false;
            $user->is_manager = $request->input('isManager', false);
            $user->save();
            if ($request->has('wxuserId')) {
                $wxuser = WxUser::find($request->input('wxuserId'));
                if ($wxuser->admin_id) {
                    DB::rollback();
                    return [
                        'errcode' => 142,
                        'errMsg' => '当前用户已是管理员',
                    ];
                } else {
                    $wxuser->admin_id = $user->id;
                    $wxuser->save();
                }
            }
            DB::commit();
            return Response::success([
                'userId' => $user->id,
            ]);
        } catch (\Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * 获取所有的后台用户
     */
    public function getAdminUsers(Request $request) {
        $size = $request->input('size', 10);
        return UserResource::collection(User::paginate($size)->appends(['size' => (string)$size]));
    }

    /**
     * 获取所有的志愿者信息
     */
    public function getAllWxusers(Request $request) {
        $request->validate([
            'size' => 'integer',
            'page' => 'integer',
            'orderType' => Rule::in(['asc', 'desc']),
            'orderValue' => Rule::in(['money', 'points', 'history_money', 'history_points']),
        ]);
        // $result = WxUser::with('realInfo')->get()->toArray();
        // return array_values(array_sort($result, function($wxuser) {
        //     return $wxuser['money'];
        // }));
        
        $userInfoTypes = ['name', 'mobile', 'id_number', 'sex', 'school', 'school_area'];        
        $input = $request->only($userInfoTypes);
        $role = $request->input('role', 1);  // 1普通用户，2管理员，3运营人员，4负责人
        // if (count($input)) {
        $wxusers = WxUser::with('realInfo')->whereHas('realInfo', function($query) use ($input) {
            foreach($input as $key => $value) {
                $query->where($key, 'like', "%$value%");
            }
        });
        if ($role != 1) {
            $wxusers = $wxusers->whereHas('adminInfo', function($query) use ($role) {
                if ($role == 2) {
                    $query->where('is_admin', true);
                } else if ($role == 3) {
                    $query->where('is_operator', true);
                } else if ($role == 4) {
                    $query->where('is_manager', true);
                }
            });
        }
        $size = $request->input('size', 10);
        $page = $request->input('page', 1);
        if(!$request->has('orderType')) {
            return $wxusers->paginate($size)->appends(['size' => (string)$size]);
        }
        $orderType = $request->input('orderType', 'asc');
        $orderValue = $request->input('orderValue', 'money');
        $wxusers = Collection::make($wxusers->get()->toArray());
        $total = count($wxusers);
        if($orderType == 'asc') {
            $wxusers = $wxusers->sortBy($orderValue)->values();
        } else if($orderType == 'desc') {
            $wxusers = $wxusers->sortByDesc($orderValue)->values();
        }
        $wxusers = $wxusers->splice(($page-1)*$size, $size);
        return [
            "current_page" => (int)$page,
            "data" => $wxusers->all(),
            // "first_page_url" => "http://localhost/api/admin/wxuser/all?size=$size&page=$page",
            // "from" => 0,
            // "last_page" => $total/$size,
            // "last_page_url" => "http://localhost/api/admin/wxuser/all?size=$size&page=$page",
            // "next_page_url" => null,
            // "path" => "http://localhost/api/admin/wxuser/all",
            // "per_page" => $size,
            // "prev_page_url" => null,
            // "to" => 0,
            "total" => $total
        ];
    }
    /**
     * 获取一个志愿者的信息
     */
    public function getWxuserInfo(Request $request) {
        $request->validate([
            'wxuserId' => 'bail|required|Integer|exists:wxusers,id'
        ]);
        $wxuserId = $request->input('wxuserId');
        $wxuser = WxUser::with([
            'realInfo',
            'completedProjects',
            'workingProjects',
        ])->find($wxuserId)
        ->setAppends(['history_money', 'history_points', 'money', 'points', 'admin_info']);
        return Response::success($wxuser->toArray());
    }

    /**
     * 获取所有的志愿者项目
     */
    public function getAllProjects(Request $request) {
        $size = $request->input('size', 10);
        return ProjectResource::collection(Project::orderBy('id', 'desc')->paginate($size)->appends(['size' => (string)$size]));
    }

    public function getAdminUserInfo(Request $request) {
        $request->validate([
            'userId' => 'required|integer|exists:users,id',
        ]);
        return User::find($request->input('userId'));
    }

    public function updateAdminUser(Request $request) {
        $request->validate([
            'userId' => 'required|integer|exists:users,id',
            'isAdmin' => 'nullable|boolean',
            'isOperator' => 'nullable|boolean',
            'isManager' => 'nullable|boolean',
            'oldPassword' => 'nullable',
            'newPassword' => 'required_with:oldPassword|string|between:6,50',
        ]);
        $curUser = $request->user();
        $user = User::find($request->input('userId'));
        if ($request->has('isAdmin') && $curUser->is_admin) {
            $user->is_admin = $request->input('isAdmin');
        }
        if ($request->has('isOperator') && ($curUser->is_operator || $curUser->is_admin)) {
            $user->is_operator = $request->input('isOperator');
        }
        if ($request->has('isManager') && ($curUser->is_operator || $curUser->is_admin)) {
            $user->is_manager = $request->input('isManager');
        }
        if ($request->has(['oldPassword', 'newPassword'])
          && ($curUser->is_admin || $curUser->is_operator && !$user->is_admin)) {
            if (\Hash::check($request->input('oldPassword'), $user->password)) {
                $user->password = bcrypt($request->input('newPassword'));
            } else {
                return [
                    'errcode' => 140,
                    'errMsg' => '密码验证失败',
                ];
            }
        }
        $user->save();
        return $user;
    }

    public function deleteAdminUser(Request $request) {
        $request->validate([
            'userId' => 'required|integer|exists:users,id',
        ]);
        $curUser = $request->user();
        $userId = $request->input('userId');
        $user = User::find($userId);
        if ($curUser->is_admin || ($curUser->is_operator && !$user->is_admin)) {
            User::destroy($userId);
            $wxuser = WxUser::where('admin_id', $userId)->update(['admin_id' => NULL]);
            return Response::success();
        } else {
            return [
                'errcode' => 138,
                'errMsg' => '没有权限',
            ];
        }
    }
}
