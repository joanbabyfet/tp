<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\lib\cls_util;

class TestController extends BaseController
{
    public function demo()
    {
        return $this->success();
    }
}
