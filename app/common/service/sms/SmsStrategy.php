<?php

namespace app\common\service\sms;

/**
 * 定义抽象策略接口
 */
interface SmsStrategy
{
    public function send($phone, $code);
}
