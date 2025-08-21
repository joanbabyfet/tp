<?php

namespace app\service\pay;

use app\service\pay\strategy\CYStrategy;

/**
 * 工厂类, 統一管理所有具体策略类, 包含一个静态方法，根据传入的参数，返回一个具体策略类的实例
 */
class PayFactory
{
    public static function strategy($type)
    {
        switch($type)
        {
            case 'CY':
                $strategy = new CYStrategy();
                break;
            default: //默认使用 CY
                $strategy = new CYStrategy();
                break;
        }
        //返回对象
        return $strategy;
    }
}
