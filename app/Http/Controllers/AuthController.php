<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\User;

class AuthController extends Controller
{
    public function __contruct() {
        $this->middleware('auth:api', ['except' => ['login']]);
    }
    
    /**
     * 后台管理用户登录
     */
    public function login(Request $request) {
        $username = $request->input('username');
        $password = $request->input('password');
        // return config('app.client_secret');
        try {
            $http = new Client([
                'base_uri' => env('BASE_URI', 'http://localhost/'),
                'timeout' => 10,
            ]);
            $response = $http->request('POST', '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => '2',
                    'client_secret' => config('app.client_secret'),
                    'username' => $username,
                    'password' => $password,
                    'scope' => '',
                ],
            ]);
            $res = json_decode((string)($response->getBody()), true);
            // return dd($res);
            return Response::success([
                'token_type' => $res['token_type'],
                'access_token' => $res['access_token'],
                'expires_in' => $res['expires_in'],
                'admin_info' => User::where('name', $username)->first(),
            ]);
        } catch (RequestException $err) {
            return [
                'errcode' => '401',
                'errMsg' => '用户名或密码错误',
            ];
        } catch (\Exception $err) {
            throw $err;
        }
    }
}
