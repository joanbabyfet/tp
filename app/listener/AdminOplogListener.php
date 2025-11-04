<?php
declare (strict_types = 1);

namespace app\listener;

class AdminOplogListener
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        //写入操作日志
        app('admin_oplog')->save($event->getMsg());
    }
}
