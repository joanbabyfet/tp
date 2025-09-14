<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\lib\Auth;

use think\App;

class IndexController extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function index()
    {
        $data = [
            ['id'=>1001,'title'=>'标题1'],
            ['id'=>1002,'title'=>'标题2']
        ];
        return $this->success($data);
    }

    public function login()
    {
        $uid = '72318b522cf851248e683edb9e1a2a92';
        $token = Auth::createToken($uid);
        $info = [
            'token' => $token
        ];
        return $this->success($info);
    }
}
