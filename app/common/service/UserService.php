<?php

namespace app\common\service;

use app\common\lib\cls_auth;
use app\common\lib\cls_response;
use app\common\lib\cls_util;
use app\model\UserModel;
use think\facade\Lang;
use think\facade\Session;
use think\facade\Validate;

class UserService extends BaseService
{
    protected $model;
    protected $userLoginService;
    public function __construct()
    {
        $this->model = new UserModel();
        $this->userLoginService = new UserLoginService();
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
            $user = $this->model->where($where)->find();
            $user = empty($user) ? [] : $user->toArray();
            if(empty($user)) {
                //写入登录失败日志
                $log = [
                    'uid'       => '0',
                    'username'  => $username,
                ];
                $this->userLoginService->save($log, 0);

                $this->exception("用户名或密码无效", -1);
            }

            if($user['status'] == 0) {
                $this->exception("用户禁用", -2);
            }

            if(cls_util::get_password($password) != $user['password']) {
                //写入登录失败日志
                $log = [
                    'uid'       => $user['id'],
                    'username'  => $user['username'],
                ];
                $this->userLoginService->save($log, 0);

                $this->exception("用户名或密码无效", -3);
            }

            //更新登录信息
            $up = [
                'session_id'    => Session::getId(), //web场景使用
                'login_ip'      => request()->ip(),
                'login_time'    => time()
            ];
            $this->model->where('id', '=', $user['id'])->update($up);

            //写入登录成功日志
            $log = [
                'uid'       => $user['id'],
                'username'  => $user['username'],
            ];
            $this->userLoginService->save($log, 1);

            $ret_data = [
                'id'        => $user['id'],
                'username'  => $user['username'],
                'nickname'  => $user['nickname'],
                'token'     => cls_auth::create_token($user['id']),
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
