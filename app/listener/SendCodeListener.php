<?php
declare (strict_types = 1);

namespace app\listener;

class SendCodeListener
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        //写入发送验证码日志
        app('send_code')->save($event->getData());
    }
}
