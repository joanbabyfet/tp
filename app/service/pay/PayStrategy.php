<?php

namespace app\service\pay;

/**
 * 定义抽象策略接口
 */
interface PayStrategy
{
    //下单
    public function pay(array $data, &$ret_data=[]);

    //订单回调
    public function callback(array $data, $secret, &$ret_data=[]);

    //订单查询
    public function order_query(array $data, &$ret_data=[]);
}
