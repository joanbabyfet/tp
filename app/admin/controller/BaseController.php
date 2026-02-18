<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\traits\ResponseJson;
use app\event\AdminOplogEvent;

class BaseController
{
    use ResponseJson;

    //构造函数
    public function __construct()
    {

    }

    //写入操作日志
    protected function write_log($content)
    {
        event(new AdminOplogEvent($content));
    }
}
