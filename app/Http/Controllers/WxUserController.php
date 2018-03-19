<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WxUser;
use GuzzleHttp\Client;
use App\Models\RealInfo;
use App\Models\Apply;
use App\Http\Resources\ProjectResource;
use SmsManager;
use Exception;
use Validator;
use App\Providers\OSS;
use App\Http\Response;
use App\Models\Project;

class WxUserController extends Controller
{
    /**
     * 获取当前请求的用户
     */
    private function getUser($token) {
        return WxUser::where('token', $token)->first();
    }
    /**
     * 返回当前请求的用户信息
     *
     * @return \Illuminate\Http\Response
     */
    public function getInfo(Request $request)
    {
        $user = $this->getUser($request->header('token'));
        if ($user->realInfo) {
            $realInfo = $user->realInfo;
            return Response::success([
                'avatar' => $user->avatar,
                'nickName' => $user->nick_name,
                'name' => $realInfo->name,
                'id' => $realInfo->id_number,
                'school' => $realInfo->school,
                'schoolArea' => $realInfo->school_area,
            ]);
        } else {
            return Response::success([
                'avatar' => $user->avatar,
                'nickName' => $user->nick_name,
            ]);
        }
    }
    /**
     * 实名认证接口
     */
    public function authUser(Request $request) {
        // 验证手机验证码
        $validator = Validator::make($request->all(), [
            'mobile'     => 'required|confirm_mobile_not_change|confirm_rule:mobile_required',
            'verifyCode' => 'required|verify_code',
        ]);
        if ($validator->fails()) {
           //验证失败后建议清空存储的发送状态，防止用户重复试错
        //    SmsManager::forgetState();
           return [
               'errcode' => 130,
               'errMsg' => '手机号验证失败',
           ];
        }

        // 验证用户姓名和身份证号是否匹配
        $name = $request->input('name');
        $idNumber = $request->input('idNumber');
        if (!$name || !$idNumber) {
            return Response::wrongParams();
        }
        try {
            $user = $this->getUser($request->header('token'));
            if ($user->realInfo) {
                return [
                    'errcode' => 136,
                    'errMsg' => '用户已经实名认证'
                ];
            }
            $realInfo = new RealInfo();
            $realInfo->name = $name;
            $realInfo->id_number = $idNumber;
            $realInfo->photo = $request->input('photo');
            $realInfo->sex = $request->input('sex');
            $realInfo->school_area = $request->input('schoolArea');
            $realInfo->school = $request->input('school');
            $realInfo->has_agent = $request->input('hasAgent');
            $realInfo->has_volunteer = $request->input('hasVolunteer');
            $realInfo->experience = $request->input('experience');
            $realInfo->mobile = $request->input('mobile');
            $realInfo->wxuser_id = $user->id;
            $realInfo->status = 1;
            $realInfo->save();
            return Response::success();
        } catch (Exception $e) {
            return Response::wrongParams();
        }
    }

    /**
     * 上传图片
     */
    public function uploadImg(Request $request) {
        // return dd($request);
        if (!$request->hasFile('image')) {
            return Response::wrongParams();
        }
        $image = $request->file('image');
        $originalName = $image->getClientOriginalName();
        $key = time() . '-' . $originalName;
        $tempPath = $image->getRealPath();
        $buckName = 'develop-hello-orange';
        try {
            $res = OSS::publicUpload($buckName, $key, $tempPath);
            return Response::success([
                'url' => OSS::getPublicObjectURL($buckName, $key),
            ]);
        } catch (Exception $err) {
            return [
                'errcode' => 131,
                'errMsg' => '上传遇到错误',
            ];
        }
    }
    /**
     * Get wechat session_key and openid
     */
    private function getWeChatInfo($code)
    {
        $appid = config('app.wx_appid');
        $secret = config('app.wx_secret');
        $grantType = 'authorization_code';
        $baseUrl = 'https://api.weixin.qq.com/sns/jscode2session';
        $url = "$baseUrl?appid=$appid&secret=$secret&js_code=$code&grant_type=$grantType";
        $http = new Client();
        $response = $http->get($url, [
            'verify' => false,
        ]);
        $statusCode = $response->getStatusCode();
        $body = (string)$response->getBody();
        $res = json_decode($body, true);        
        if ($response->getStatusCode() == 200) {
            return $res;
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getToken(Request $request)
    {
        $input = $request->all();
        $wechatInfo = $this->getWeChatInfo($input['code']);
        if (array_key_exists('errcode', $wechatInfo)) {
            return $wechatInfo;
        }
        try {
            $user = WxUser::where('openid', $wechatInfo['openid'])->firstOrFail();
        } catch (Exception $err) {
            $user = new WxUser();
        }
        try {
            $user->nick_name = $input['nickName'];
            $user->city = $input['city'];
            $user->province = $input['city'];
            $user->avatar = $input['avatarUrl'];
            $user->gender = $input['gender'];
            $user->country = $input['country'];
            $user->wx_session = $wechatInfo['session_key'];
            $user->openid = $wechatInfo['openid'];
            while(true) {
                $randomStr = str_random(40);
                $exist = WxUser::where('token', $randomStr)->first();
                if(!$exist) {
                    break;
                }
            }
            $user->token = $randomStr;
            $user->save();
            return Response::success([
                'token' => $user->token,
                'userId' => $user->id,
                'auth' => $user->realInfo?true:false,
            ]);
        } catch(Exception $e) {
            throw $e;
            return Response::wrongParams();
        }
    }

    /** 
     * 获取当前请求用户的收益情况
     */
    public function getIncome(Request $request) {
        $type = $request->input('type', 3);
        $count = $request->input('size', 10);
        $page = $request->input('page', 1);

        $user = $this->getUser($request->header('token'));
        $completedApplies = $user->completedProjects;
        $totalCount = $completedApplies->count();
        $moneyTotal = $user->money();
        $pointsTotal = $user->points();
        $resp = Response::success([
            'totalCount' => $totalCount,
            'totalPoints' => $pointsTotal,
            'totalMoney' => $moneyTotal,
            'projects' => ProjectResource::collection($user->completedProjects),
        ]);
        return $resp;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        WxUser::destory($id);
    }
}
