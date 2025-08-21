<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\App;
use think\Request;

class Base
{
    protected $app;     //应用实例
    protected $request; //Request实例

    //构造函数
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
    }
}
