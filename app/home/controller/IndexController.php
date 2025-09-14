<?php
declare (strict_types = 1);

namespace app\home\controller;

use think\facade\View;

class IndexController extends BaseController
{
    public function index()
    {
        //渲染用户信息
        View::assign('user', [
            'name' => 'peter',
            'phone'  => '0912345678',
        ]);
        return View::fetch();
    }

    public function workerman()
    {
        return View::fetch();
    }
}
