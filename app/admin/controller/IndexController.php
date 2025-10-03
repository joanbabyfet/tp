<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\lib\cls_auth;

class IndexController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->success();
    }

    public function login()
    {
        $uid = '72318b522cf851248e683edb9e1a2a92';
        $token = cls_auth::create_token($uid);
        $info = [
            'token' => $token
        ];
        return $this->success($info);
    }
}
