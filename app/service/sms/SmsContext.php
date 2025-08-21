<?php

namespace app\service\sms;

/**
 * 定义环境类, 当需要添加新的方式时，只需要添加具体的策略类，而无需修改环境类和客户端代码
 */
class SmsContext
{
    private $strategy;

    //具体的策略选择, 依赖注入具体策略
    public function __construct(SmsStrategy $smsStrategy)
    {
        $this->strategy = $smsStrategy;
    }

    public function send($phone, $code)
    {
        return $this->strategy->send($phone, $code);
    }
}
