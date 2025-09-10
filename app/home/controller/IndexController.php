<?php
declare (strict_types = 1);

namespace app\home\controller;

use think\App;
use think\facade\View;

class IndexController extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function index()
    {
        //渲染变量
//        View::assign([
//            'name' => 'kos',
//            'age'  => 20,
//            'phone'  => '0912345678',
//        ]);

        //渲染用户信息
        View::assign('user', [
            'session_id' => session_id(),
            'name' => 'kos',
            'age'  => 20,
            'phone'  => '0912345678',
        ]);
        return View::fetch();
    }

    public function workerman()
    {
        return View::fetch();
    }
}
