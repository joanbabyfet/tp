<?php

namespace app\service\pay;

/**
 * 定义环境类, 当需要添加新的方式时，只需要添加具体的策略类，而无需修改环境类和客户端代码
 */
class PayContext
{
    private $strategy;

    //具体的策略选择, 依赖注入具体策略
    public function __construct(PayStrategy $payStrategy)
    {
        $this->strategy = $payStrategy;
    }

    //下单
    public function pay(array $data, &$ret_data=[])
    {
        return $this->strategy->pay($data, $ret_data);
    }

    //订单回调
    public function callback(array $data, $secret, &$ret_data=[])
    {
        return $this->strategy->callback($data, $secret, $ret_data);
    }

    //订单查询
    public function order_query(array $data, &$ret_data=[])
    {
        return $this->strategy->order_query($data, $ret_data);
    }
}
