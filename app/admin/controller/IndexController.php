<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\service\AdminService;

class IndexController extends BaseController
{
    protected $adminService;
    public function __construct(
        AdminService $adminService
    )
    {
        parent::__construct();
        $this->adminService = $adminService;
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
        $status = $this->adminService->login(request()->post(), $ret_data);
        if($status < 0) {
            return $this->error($this->adminService->get_err_msg($status), $status);
        }
        return $this->success($ret_data);
    }
}
