<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// 后台用户的登录接口
Route::post('/user/login', 'AuthController@login')->name('login');

// 微信小程序用户的登录接口
Route::post('wxuser/info/submit', 'WxUserController@getToken');
// 获取所有志愿项目的接口
Route::get('project/get', 'ProjectController@index');
// 获取志愿项目信息的接口
Route::get('project/info/get', 'ProjectController@getProjectInfo')->name('getProjectInfo');

// 小程序接口
Route::middleware('token')->group(function() {
    Route::get('wxuser/info/get', 'WxUserController@getInfo');
    Route::get('wxuser/income/get', 'WxUserController@getIncome');
    Route::get('project/completed/get', 'ProjectController@getMyCompletedProjects');
    Route::post('wxuser/info/auth', 'WxUserController@authUser');
    Route::post('project/apply', 'ApplyController@apply');
    Route::post('image/upload', 'WxUserController@uploadImg');
});

// 后台用户接口
Route::prefix('admin')->middleware('auth:api')->group(function() {
    // 新建项目
    Route::post('project/create', 'ProjectController@store');
    // 删除项目
    Route::post('project/delete', 'ProjectController@destroy');
    // 获取所有的志愿者项目
    Route::get('project/all', 'UserController@getAllProjects');
    // 获取一个志愿项目的职责列表
    Route::get('project/tasks', 'ProjectController@getTasks');
    // 处理一个用户的申请
    Route::post('apply/deal', 'ApplyController@dealApply');
    // 获取一个志愿者项目的所有志愿者
    Route::get('project/wxuser/all', 'ProjectController@getProjectWxusers');
    // 获取一个志愿项目的单个用户
    Route::get('project/wxuser/info', 'ProjectController@getWxUserByProject');
    
    // 获取一个志愿者的信息
    Route::get('wxuser/info', 'UserController@getWxuserInfo');
    // 获取所有的志愿者
    Route::get('wxuser/all', 'UserController@getAllWxusers');

    // 创建一个后台用户
    Route::post('user/create', 'UserController@create');
    // 获取所有后台管理用户
    Route::get('user/all', 'UserController@getAdminUsers');
    // 获取后台管理用户的信息
    Route::get('user/info', 'UserController@getAdminUserInfo');
    // 更新后台用户信息
    Route::post('user/update', 'UserController@updateAdminUser');
    // 删除后台用户
    Route::post('user/del', 'UserController@deleteAdminUser');

    // 上传图片
    Route::post('image/upload', 'WxUserController@uploadImg');

    // 职责模板
    Route::post('templet/create', 'TaskTempletController@create');
    Route::post('templet/update', 'TaskTempletController@update');
    Route::post('templet/delete', 'TaskTempletController@delete');
    Route::get('templet/all', 'TaskTempletController@getAll');
    Route::get('templet/info', 'TaskTempletController@getOne');

    // 拒绝原因模板
    Route::post('reason/create', 'RefuseReasonController@create');
    Route::post('reason/delete', 'RefuseReasonController@delete');
    Route::get('reason/all', 'RefuseReasonController@getAll');

    // export 
    Route::get('export/project/wxuser', 'ExcelController@projectWxusersByFilter');
    Route::get('export/wxuser/all', 'ExcelController@allWxusersByFilter');
    Route::get('export/wxuser/income', 'ExcelController@incomeByFilter');
});
