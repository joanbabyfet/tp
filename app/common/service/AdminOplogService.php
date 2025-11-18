<?php

namespace app\common\service;

use app\common\lib\cls_response;
use app\model\AdminOplogModel;
use think\facade\Lang;
use think\facade\Session;
use think\facade\Validate;

class AdminOplogService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new AdminOplogModel();
    }

    //保存管理员操作日志
    public function save($msg)
    {
        //参数过滤
        $validate = Validate::rule([
            'msg'     => 'require|string',
        ]);

        $status = 1;
        try {
            if (!$validate->check(['msg' => $msg])) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $op_url = request()->pathinfo(); ///获取地址不含参数 example

            //组装数据
            $save_data = [
                'uid'           => request()->auth,
                'username'      => 'admin',
                'session_id'    => Session::getId(), //web场景使用
                'msg'           => $msg,
                'op_time'       => time(),
                'op_ip'         => request()->ip(),
                'op_country'    => request()->country(),
                'op_url'        => addslashes($op_url), //在特定字符前添加反斜杠
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
                'data'    => $msg
            ]);
        }
        return $status;
    }
}
