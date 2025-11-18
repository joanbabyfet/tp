<?php

namespace app\common\service;

use app\common\lib\cls_response;
use app\model\ApiReqLogModel;
use think\facade\Lang;
use think\facade\Validate;

class ApiReqLogService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new ApiReqLogModel();
    }

    //保存api请求日志
    public function save($data)
    {
        //参数过滤
        $validate = Validate::rule([
            'ct'        => 'string',
            'ac'        => 'string',
            'ip'        => 'string',
            'req_data'  => 'array',
            'res_data'  => 'array',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $ct         = $data['ct'] ?? request()->controller();
            $ac         = $data['ac'] ?? request()->action();
            $ip         = $data['ip'] ?? request()->ip();
            $uid        = empty(request()->auth) ? '' : request()->auth;
            $type       = 'api';
            $req_data   = empty($data['req_data']) ? request()->param() : $data['req_data'];
            $res_data   = empty($data['res_data']) ? [] : $data['res_data'];

            //组装数据
            $save_data = [
                'type'          => $type,
                'uid'           => $uid,
                'ct'            => $ct,
                'ac'            => $ac,
                'ip'            => $ip,
                'req_data'      => json_encode($req_data, JSON_UNESCAPED_UNICODE),
                'res_data'      => json_encode($res_data, JSON_UNESCAPED_UNICODE),
                'req_time'      => time(),
            ];
            //添加
            $this->model->save($save_data);
        }
        catch (\Exception $e) {
            $status = $this->get_exception_status($e);
            //写入日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data
            ]);
        }
        return $status;
    }
}
