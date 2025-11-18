<?php

namespace app\common\traits;

use app\common\lib\cls_response;
use app\job\ApiReqLogJob;
use think\facade\Lang;
use think\facade\Queue;
use think\response\Json;

trait ResponseJson
{
    /**
     * 响应json
     * @param string $msg
     * @param int $code
     * @param $data
     * @param array $header
     * @param array $option
     * @return Json
     */
    public function result(int $code = 500, string $msg = '', $data = [], array $header = [], array $option = []) : Json
    {
        $rdata = [
            'code'      => $code,
            'msg'       => $msg,
            'timestamp' => time(),
            'data'      => empty($data) ? (object)$data : $data,
        ];

        //通过队列写入api请求日志
        Queue::push(ApiReqLogJob::class, [
            'ct'        => request()->controller(),
            'ac'        => request()->action(),
            'ip'        => request()->ip(),
            'req_data'  => request()->param(),
            'res_data'  => $rdata
        ], $queue = null);

        return \json($rdata, 200, $header, $option);
    }

    /**
     * 成功响应
     * @param string $msg
     * @param int $code
     * @param $data
     * @param array $header
     * @param array $option
     * @return Json
     */
    public function success($data = [], string $msg = '', int $code = cls_response::SUCCESS, array $header = [], array $option = []) : Json
    {
        $msg = empty($msg) ? cls_response::SUCCESS_MSG : $msg;
        return $this->result($code, $msg, $data, $header, $option);
    }

    /**
     * 失败响应
     * @param string $msg
     * @param int $code
     * @param $data
     * @param array $header
     * @param array $option
     * @return Json
     */
    public function error(string $msg='', int $code = cls_response::ERROR, $data = [], array $header = [], array $option = []) : Json
    {
        $msg = empty($msg) ? cls_response::ERROR_MSG : $msg;
        return $this->result($code, $msg, $data, $header, $option);
    }

    /**
     * 参数错误
     * @param $error_code
     * @return Json
     */
    public function invalid_params($error_code = cls_response::SYS_PARAMS_ERROR)
    {
        return $this->error(Lang::get('common_param_error'), $error_code);
    }

    /**
     * 未知错误
     * @param $error_code
     * @return Json
     */
    public function unknown_error($error_code = cls_response::UNKNOWN_ERROR)
    {
        return $this->error(Lang::get('common_unknow_error'), $error_code);
    }

    /**
     * 无权限
     * @param $error_code
     * @return Json
     */
    public function no_permission($error_code = cls_response::SYS_NO_PERMISSION)
    {
        return $this->error(Lang::get('common_no_permission'), $error_code);
    }
}
