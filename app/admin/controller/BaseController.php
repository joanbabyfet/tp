<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\traits\ResponseJson;
use app\event\AdminOplogEvent;
use think\facade\Cache;

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

    //干掉缓存
    protected function clear_cache($cache_key)
    {
        Cache::store('redis')->delete($cache_key);
    }
}
