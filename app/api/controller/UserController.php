<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\common\service\UserService;
use think\facade\Cache;
use think\facade\Lang;

class UserController  extends BaseController
{
    protected $userService;
    public function __construct(
        UserService $userService
    )
    {
        parent::__construct();
        $this->userService = $userService;
    }

    /**
     * 用户登录
     * @return \think\response\Json
     */
    public function login()
    {
        $status = $this->userService->login(request()->post(), $ret_data);
        if($status < 0) {
            return $this->error($this->userService->get_err_msg($status), $status);
        }
        return $this->success($ret_data);
    }

    /**
     * 用户注册
     * @return \think\response\Json
     */
    public function register()
    {
        $status = $this->userService->edit(request()->post(), $ret_data);
        if($status < 0) {
            return $this->error($this->userService->get_err_msg($status), $status);
        }
        return $this->success([], Lang::get('common_reg_suc'));
    }

    /**
     * 获取用户信息
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function userinfo()
    {
        $id = request()->auth;
        if(empty($id)) {
            return $this->invalid_params();
        }

        $cache_key = sprintf("user:id:%s", $id);
        $res = Cache::store('redis')->get($cache_key);
        if(empty($res)) {
            //获取详情
            $this->userService->detail(['id' => $id], $res);
            //写入缓存
            Cache::store('redis')->set($cache_key, $res);
        }
        return $this->success($res);
    }

    /**
     * 修改密码
     * @return \think\response\Json
     */
    public function edit_pwd()
    {
        $status = $this->userService->edit_pwd(request()->post());
        if($status < 0) {
            return $this->error($this->userService->get_err_msg($status), $status);
        }
        return $this->success();
    }

    /**
     * 忘记密码-重置密码
     * @return \think\response\Json
     */
    public function forgot_pwd()
    {
        $status = $this->userService->forgot_pwd(request()->post());
        if($status < 0) {
            return $this->error($this->userService->get_err_msg($status), $status);
        }
        return $this->success();
    }
}
