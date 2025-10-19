<?php

namespace app\common\service;

use app\common\lib\cls_auth;
use app\common\lib\cls_response;
use app\common\lib\cls_util;
use app\model\AdminModel;
use think\facade\Lang;
use think\facade\Session;
use think\facade\Validate;

class AdminService extends BaseService
{
    protected $model;
    protected $adminLoginService;
    public function __construct()
    {
        $this->model = new AdminModel();
        $this->adminLoginService = new AdminLoginService();
    }

    public function login($data, &$ret_data = [])
    {
        //参数过滤
        $validate = Validate::rule([
            'username'  => 'require|string',
            'password'  => 'require|string',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $username = $data['username'];
            $password = $data['password'];

            $where = [
                'username' => $username
            ];
            $admin = $this->model->where($where)->find();
            $admin = empty($admin) ? [] : $admin->toArray();
            if(empty($admin)) {
                //写入登录失败日志
                $log = [
                    'uid'       => '0',
                    'username'  => $username,
                ];
                $this->adminLoginService->save($log, 0);

                $this->exception("用户名或密码无效", -1);
            }

            if($admin['status'] == 0) {
                $this->exception("用户禁用", -2);
            }

            if(cls_util::get_password($password) != $admin['password']) {
                //写入登录失败日志
                $log = [
                    'uid'       => $admin['id'],
                    'username'  => $admin['username'],
                ];
                $this->adminLoginService->save($log, 0);

                $this->exception("用户名或密码无效", -3);
            }

            //更新登录信息
            $up = [
                'session_id'    => Session::getId(), //web场景使用
                'login_ip'      => request()->ip(),
                'login_time'    => time()
            ];
            $this->model->where('id', '=', $admin['id'])->update($up);

            //写入登录成功日志
            $log = [
                'uid'       => $admin['id'],
                'username'  => $admin['username'],
            ];
            $this->adminLoginService->save($log, 1);

            $ret_data = [
                'id'        => $admin['id'],
                'username'  => $admin['username'],
                'realname'  => $admin['realname'],
                'token'     => cls_auth::create_token($admin['id']),
            ];
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
