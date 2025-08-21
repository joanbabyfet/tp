<?php

namespace app\service\sms;

use app\service\sms\strategy\SpugStrategy;
use app\service\sms\strategy\UnimtxStrategy;

/**
 * 工厂类, 統一管理所有具体策略类, 包含一个静态方法，根据传入的参数，返回一个具体策略类的实例
 */
class SmsFactory
{
    public static function strategy($type)
    {
        switch($type)
        {
            case 'unimtx':
                $strategy = new UnimtxStrategy();
                break;
            case 'spug':
                $strategy = new SpugStrategy();
                break;
            default: //默认使用 unimtx
                $strategy = new UnimtxStrategy();
                break;
        }
        //返回对象
        return $strategy;
    }
}
