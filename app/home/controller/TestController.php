<?php
declare (strict_types = 1);

namespace app\home\controller;


class TestController extends BaseController
{
    public function demo()
    {
        return $this->success();
    }
}
