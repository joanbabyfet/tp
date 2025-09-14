<?php
declare (strict_types = 1);

namespace app\admin\controller;

class TestController extends BaseController
{
    public function demo()
    {
        return $this->success();
    }
}
