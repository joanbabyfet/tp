<?php

namespace app\common\service;

use app\common\lib\cls_response;
use app\model\UserLoginModel;
use think\facade\Lang;
use think\facade\Session;
use think\facade\Validate;

class UserLoginService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new UserLoginModel();
    }

    //保存登录日志
    public function save($data)
    {
        //参数过滤
        $validate = Validate::rule([
            'uid'           => 'require|string',
            'username'      => 'require|string',
            'login_ip'      => 'string',
            'agent'         => 'string',
            'login_status'  => 'require|integer',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $uid = $data['uid'] ?? '0';
            $username = $data['username'];
            $login_status = $data['login_status'] ?? 0;
            $login_ip = $data['login_ip'] ?? '';
            $agent    = $data['agent'] ?? '';
            $cli_hash = md5($username.'-'.$login_ip);
            $now = time();

            //组装数据
            $save_data = [
                'uid'           => $uid,
                'username'      => $username,
                'session_id'    => Session::getId(), //web场景使用
                'agent'         => $agent,
                'login_time'    => $now,
                'login_ip'      => $login_ip,
                'login_country' => request()->country(),
                'login_status'  => $login_status,   //登录时状态 1=成功，0=失败
                'cli_hash'      => $cli_hash, //用户登录名和ip的hash
            ];

            //添加=
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

    /**
     * 批量添加或更新
     * @param array $data
     * @return int|mixed
     */
    public function save_all(array $data)
    {
        //参数过滤
        $validate = Validate::rule([
            'data'              => 'require|array',
            'data.*.uid'        => 'require|string',
            'data.*.username'   => 'require|string',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }

            //批量添加或更新(带主键字段)
            $this->model->saveAll($data['data']);
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
