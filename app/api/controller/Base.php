<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\common\traits\ResponseJson;
use think\App;
use think\Request;

class Base
{
    use ResponseJson;

    protected $app;     //应用实例
    protected $request; //Request实例

    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
    }
}
