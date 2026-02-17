<?php
declare (strict_types = 1);

namespace app\Api\controller;

use app\common\service\OrderService;
use think\facade\Cache;

class OrderController extends BaseController
{
    protected $orderService;
    public function __construct(
        OrderService $orderService
    )
    {
        parent::__construct();
        $this->orderService = $orderService;
    }

    /**
     * 创建订单
     * @return \think\response\Json
     */
    public function create_order()
    {
        $status = $this->orderService->create_order(request()->post(), $ret_data);
        if($status < 0) {
            return $this->error($this->orderService->get_err_msg($status), $status);
        }
        return $this->success($ret_data);
    }

    /**
     * 获取订单详请
     * @return \think\response\Json
     */
    public function detail()
    {
        $id = request()->param('order_id');
        if(empty($id)) {
            return $this->invalid_params();
        }

        $cache_key = sprintf("order:order_id:%s", $id);
        $res = Cache::store('redis')->get($cache_key);
        if(empty($res)) {
            //获取详情
            $this->orderService->detail(['id' => $id], $res);
            //写入缓存
            Cache::store('redis')->set($cache_key, $res, 300);
        }
        return $this->success($res);
    }
}
