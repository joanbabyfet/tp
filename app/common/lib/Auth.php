<?php

namespace app\common\lib;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Log;

class Auth
{
    public static function createToken($uid)
    {
        $key = config('myconfig.jwt_secret'); //私钥
        $now = time();
        $payload = [
            'iss' => '',            //签发者, 可为空
            'aud' => '',            //接收者, 可为空
            'iat' => $now,          //签发时间
            'nbf' => $now,          //立马生效, 该时间之前不接收处理该token
            'exp' => $now + 7200,   //过期时间(2小时后)
            //'sub' => '',          //所面向的用户
            'data' => [             //自定义数据
                'uid' => $uid
            ]
        ];
        //签发jwt token, 对称加密用HS256算法
        $token = JWT::encode($payload, $key, 'HS256');
        return $token;
    }

    public static function checkToken(string $token, &$ret_data = [])
    {
        $key = config('myconfig.jwt_secret'); //私钥

        $status = 1;
        try {
            //对称解密用HS256算法
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $ret_data = (array)$decoded->data;
        } catch(\Exception $e) {
            $status = response::INVALID_TOKEN;
            //记录日志
            Log::error(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'args'    => func_get_args()
            ]);
        }
        return $status;
    }
}
