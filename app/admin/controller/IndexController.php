<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\lib\cls_auth;
use app\common\service\AdminLoginService;

class IndexController extends BaseController
{
    protected $adminLoginService;
    public function __construct(AdminLoginService $adminLoginService)
    {
        parent::__construct();
        $this->adminLoginService = $adminLoginService;
    }

    public function index()
    {
        return $this->success();
    }

    public function login()
    {
        $uid = '1';
        $username = 'admin';
        $token = cls_auth::create_token($uid);
        $info = [
            'token' => $token
        ];

        //登录成功
        $data = [
            'uid'       => $uid,
            'username'  => $username,
        ];
        //写入登录日志
        $this->adminLoginService->save($data, 1);

        return $this->success($info);
    }
}
