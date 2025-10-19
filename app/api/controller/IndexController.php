<?php
declare (strict_types = 1);

namespace app\api\controller;


use app\common\service\UserService;

class IndexController extends BaseController
{
    protected $userService;
    public function __construct(
        UserService $userService
    )
    {
        parent::__construct();
        $this->userService = $userService;
    }

    public function index()
    {
        return $this->success();
    }

    /**
     * 用户登录
     * @return \think\response\Json
     */
    public function login()
    {
        $data = request()->post();

        $status = $this->userService->login($data, $ret_data);
        if($status < 0) {
            return $this->error($this->userService->get_err_msg($status), $status);
        }
        return $this->success($ret_data);
    }
}
