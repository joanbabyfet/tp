<?php

namespace app\common\service;

use app\common\lib\cls_response;
use app\model\SendCodeModel;
use think\facade\Lang;
use think\facade\Validate;

class SendCodeService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new SendCodeModel();
    }

    //保存发送验证码日志
    public function save($data)
    {
        //参数过滤
        $validate = Validate::rule([
            'to'            => 'require|string',
            'content'       => 'require|string',
            'type'          => 'require|integer',
            'source'        => 'require|integer',
            'status'        => 'require|integer',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $now = time();

            //组装数据
            $save_data = [
                'type'          => $data['type'],
                'to'            => $data['to'],
                'content'       => $data['content'],
                'source'        => $data['source'],
                'status'        => $data['status'],
                'create_time'   => $now,
                'create_user'   => request()->auth,
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
