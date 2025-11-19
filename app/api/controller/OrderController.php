<?php
declare (strict_types = 1);

namespace app\Api\controller;

use app\common\lib\cls_redis_lock;
use app\common\lib\cls_util;
use app\common\service\OrderService;
use app\common\service\UserService;
use think\Request;

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
}
