<?php

namespace App\Http;

class Response {
    public static function wrongParams($extraInfo = []) {
        $basicRes = [
            'errcode' => 101,
            'errMsg' => '参数错误',
        ];
        return array_merge($basicRes, $extraInfo);
    }
    public static function success($extraInfo = []) {
        $basicRes = [
            'errcode' => 0,
            'errMsg' => 'ok',
        ];
        return array_merge($basicRes, $extraInfo);
    }
}