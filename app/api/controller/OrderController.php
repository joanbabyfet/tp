<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\common\service\OrderService;

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
        $id = request()->param('order_id/d');
        //获取详情
        $data = [
            'id'        => $id,
            'is_cache'  => 1, //前台使用缓存
            'cache_key' => 'order:detail:%d',
        ];
        $status = $this->orderService->detail($data, $res);
        if($status < 0) {
            return $this->error($this->orderService->get_err_msg($status), $status);
        }
        return $this->success($res);
    }
}
