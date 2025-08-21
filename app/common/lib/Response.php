<?php

namespace app\common\lib;

use think\facade\Lang;
use think\response\Json;

class Response
{
    const SUCCESS = 0;
    const ERROR = -1;
    const NO_AUTH            = -4010;
    const INVALID_TOKEN      = -4011; //token失效
    //const INVALID_ACCESS_TOKEN = -4012; //access_token失效

    public static function result(string $msg = '', int $code = 500, $data = [], array $header = [], array $option = []) : Json
    {
        $rdata = [
            'code'      => $code,
            'msg'       => $msg,
            'timestamp' => time(),
            'data'      => empty($data) ? (object)$data : $data,
        ];

        return \json($rdata, 200, $header, $option);
    }

    public static function success(string $msg = '', int $code = self::SUCCESS, $data = [], array $header = [], array $option = []) : Json
    {
        $msg = empty($msg) ? 'success' : $msg;
        return self::result($msg, $code, $data, $header, $option);
    }

    public static function error(string $msg='', int $code = self::ERROR, $data = [], array $header = [], array $option = []) : Json
    {
        $msg = empty($msg) ? 'error' : $msg;
        return self::result($msg, $code, $data, $header, $option);
    }

    /**
     * 参数错误
     * @param $error_code
     * @return Json
     */
    public static function invalid_params($error_code = self::ERROR)
    {
        return self::error(Lang::get('common_param_error'), $error_code);
    }

    /**
     * 发生了未知错误
     * @param $msg
     * @return Json
     */
    public static function unknown_error()
    {
        return self::error(Lang::get('common_unknow_error'), self::ERROR);
    }
}
