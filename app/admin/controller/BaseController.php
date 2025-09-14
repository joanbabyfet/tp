<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\traits\ResponseJson;
use think\App;

class BaseController
{
    use ResponseJson;

    //构造函数
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
    }
}
