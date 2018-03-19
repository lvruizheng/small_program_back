<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Excel;
use App\Models\WxUser;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ExcelController extends Controller
{
    /**
     * 筛选导出所有的志愿者
     */
    public function allWxusersByFilter(Request $request) {
        $filters = $this->getRealInfoFilter($request);
        $cellData = $this->queryAllwxuser($filters);
        // return $cellData;
        Excel::create('全部用户',function($excel) use ($cellData){
            $excel->sheet('全部用户', function($sheet) use ($cellData){
                $sheet->setAutoSize(true);
                $sheet->fromArray($cellData);
            });
        })->export('xls');
    }

    /**
     * 筛选导出指定项目的志愿者，projectId为必填项
     */
    public function projectWxusersByFilter(Request $request){
        $request->validate([
            'projectId' => 'required|integer|exists:projects,id',
        ]);
        $filters = $this->getProjectFilters($request);
        $cellData = $this->queryProjectVolunteers($filters);
        Excel::create('志愿者',function($excel) use ($cellData){
            $excel->sheet('志愿者', function($sheet) use ($cellData){
                $sheet->setAutoSize(true);
                $sheet->fromArray($cellData);
            });
        })->export('xls');
    }

    /**
     * 筛选导出志愿者的收益情况，传入type,确定现金收益还是积分收益
     */
    public function incomeByFilter(Request $request) {
        $request->validate([
            'type' => [
                'nullable',
                Rule::in(['money', 'points']),
            ],
        ]);
        $filters = $this->getRealInfoFilter($request);
        $cellData = $this->queryWxuserIncome($filters, $request->input('type', 'money'));
        Excel::create('志愿者收益',function($excel) use ($cellData){
            $excel->sheet('志愿者收益', function($sheet) use ($cellData){
                $sheet->setAutoSize(true);
                $sheet->fromArray($cellData);
            });
        })->export('xls');
    }

    /**
     * 根据过滤条件获取当前项目的志愿者情况
     * $filters[0] 为用户信息的过滤数组
     * $filters[1] 为申请信息的过滤数组
     */
    private function queryProjectVolunteers($filters = array()) {
        $query = DB::table('applies')
                    ->join('real_infos', 'applies.wxuser_id', '=', 'real_infos.wxuser_id')
                    ->join('tasks', 'applies.task_id', '=', 'tasks.id');        
        foreach($filters as $key => $filter) {
            if ($key == 0) {
                foreach($filter as $type=>$value) {
                    if ($type != 'role') {
                        $query->where("real_infos.$type", 'like', "%$value%");
                    }
                }
            } else {
                $query->where("applies.project_id", $filter['projectId']);
                $applyFilters = array();
                if (array_key_exists('duty', $filter)) {
                    $query->where("applies.task_id", $filter['duty']);
                }
                if (!array_key_exists('judge', $filter) && array_key_exists('status', $filter) && $filter['status'] == 5) {
                    $query->whereIn('applies.status', [2, 4]);
                }
                if (array_key_exists('start', $filter) && array_key_exists('end', $filter)) {
                    $query->whereBetween('applies.created_at', [$filter['start'], $filter['end']]);
                }
            }
        }
        $result = new Collection();
        $lastRecord = null;
        $statusArray = ['', '审核中', '已通过', '未通过', '已评价'];

        $query->orderBy('applies.id')->chunk(100, function($records) use ($result, $statusArray) {
            foreach($records as $record) {
                $lastRecord = $result->pop();
                if($lastRecord && $record->wxuser_id == $lastRecord['用户ID']) {
                    $lastRecord['申请职责'] .= "、$record->title";
                    $result->push($lastRecord);
                    continue;
                }
                $apply = [
                    '用户ID' => $record->wxuser_id,
                    '姓名' => $record->name,
                    '性别' => $record->sex,
                    '身份证号' => $record->id_number,
                    '手机号' => $record->mobile,
                    '学校地区' => $record->school_area,
                    '学校' => $record->school,
                    '是否参加过' => $record->has_volunteer?'是':'否',
                    '申请职责' => $record->title,
                    '分配' => $record->obey?'是':'否',
                    '录取' => '',
                    '申请时间' => $record->created_at,
                    '状态' => $statusArray[$record->status],
                ];
                if($record->status == 2) {
                    $apply['录取'] = $record->title;
                }
                $lastRecord = $apply;
                $result->push($apply);
            }
        });
        return $result->toArray();
    }


    private function queryWxuserIncome($filters = array(), $type = 'money') {
        $query = WxUser::whereHas('realInfo', function($query) use ($filters){
            foreach($filters as $key => $value) {
                if($key == 'role') {
                    continue;
                }
                $query->where($key, 'like', "%$value%");
            }
        });
        $result = new Collection();
        $query->chunk(100, function($wxusers) use ($result, $type) {
            foreach($wxusers as $wxuser) {
                $record = [
                    '用户ID' => $wxuser->id,
                    '姓名' => $wxuser->realInfo->name,
                    '手机号' => $wxuser->realInfo->mobile,
                ];
                if ($type == 'money') {
                    $record = array_add($record, '当前收益', $wxuser->money);
                    $record = array_add($record, '总收益', $wxuser->history_money);
                } else if($type == 'points') {
                    $record = array_add($record, '当前积分', $wxuser->points);
                    $record = array_add($record, '总积分', $wxuser->history_points);                    
                }
                $record = array_add($record, '任务数量', $wxuser->completed_count);
                $result->push($record);
            }
        });
        return $result->toArray();
    }


    private function queryAllwxuser($filters = array()) {
        $query = DB::table('wxusers')
                    ->leftJoin('real_infos', 'real_infos.wxuser_id', '=', 'wxusers.id')
                    ->leftJoin('users', 'wxusers.admin_id', '=', 'users.id');
        foreach($filters as $key => $value) {
            if ($key != 'role') {
                $query->where("real_infos.$key", 'like', "%$value%");
            } else if($value == 1) {    // 普通用户
                $query->where('wxusers.admin_id', NULL);
            } else if($value == 2) {
                $query->where('users.is_admin', true);
            } else if($value == 3) {
                $query->where('users.is_operator', true);
            } else if($value == 4) {
                $query->where('users.is_manager', true);
            }
        }
        $result = new Collection();
        $query->select([
            'wxusers.id',
            'real_infos.name',
            'real_infos.photo',
            'real_infos.sex',
            'real_infos.id_number',
            'real_infos.mobile',
            'real_infos.school',
            'real_infos.school_area',
            'users.is_admin',
            'users.is_operator',
            'users.is_manager',
            'wxusers.created_at',
        ])->orderBy('wxusers.id')->chunk(100, function($wxusers) use ($result) {
            foreach($wxusers as $wxuser) {
                $identity = '普通用户';
                $identity = $wxuser->is_admin ? '管理员' : $identity;
                $identity .= $wxuser->is_operator ? '、运营人员' : '';
                $identity .= $wxuser->is_manager ? '、志愿者负责人' : '';
                $wxuser = [
                    '用户ID' => $wxuser->id,
                    '姓名' => $wxuser->name,
                    '照片' => $wxuser->photo,
                    '性别' => $wxuser->sex,
                    '身份证号' => $wxuser->id_number,
                    '手机号' => $wxuser->mobile,
                    '学校' => $wxuser->school,
                    '学校地区' => $wxuser->school_area,
                    '身份' => $identity,
                    '注册时间' => $wxuser->created_at,
                ];
                $result->push($wxuser);
            }
        });
        
        return $result;
    }

    private function getProjectFilters($request) {
        $realInfoFilters = $this->getRealInfoFilter($request);
        $applyInfoFilters = $request->only(['start', 'end', 'duty', 'projectId', 'status', 'judge']);
        return [
            $realInfoFilters,
            $applyInfoFilters
        ];
    }

    private function getRealInfoFilter($request) {
        $filters = $request->only(['name', 'sex', 'id_number', 'mobile', 'school', 'school_area', 'role']);
        return $filters;
    }

}
