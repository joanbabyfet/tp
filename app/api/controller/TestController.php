<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;

class TestController extends BaseController
{
    public function demo()
    {
        return $this->success();
    }
}
