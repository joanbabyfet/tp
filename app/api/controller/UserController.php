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

        //获取详情
        $data = [
            'id'        => $id,
            'is_cache'  => 1, //前台使用缓存
            'cache_key' => 'user:detail:%d',
        ];
        $status = $this->userService->detail($data, $res);
        if($status < 0) {
            return $this->error($this->userService->get_err_msg($status), $status);
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
        $phone              = request()->post('phone/s');
        $phone_area_code    = request()->post('phone_area_code/s');
        $sms_verify_code    = request()->post('sms_verify_code/d');

        $data = [
            'phone'             => $phone,
            'phone_area_code'   => $phone_area_code,
            'sms_verify_code'   => $sms_verify_code,
        ];
        $status = $this->userService->forgot_pwd($data);
        if($status < 0) {
            return $this->error($this->userService->get_err_msg($status), $status);
        }
        return $this->success();
    }
}
