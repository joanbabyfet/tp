<?php

namespace app\common\service;

use app\common\lib\cls_redis_lock;
use app\common\lib\cls_response;
use app\common\lib\cls_util;
use app\model\OrderModel;
use app\model\UserModel;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Validate;

class OrderService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new OrderModel();
    }

    /**
     * 创建订单
     * @param $data
     * @return int|mixed
     */
    public function create_order($data, &$ret_data = [])
    {
        //参数过滤
        $validate = Validate::rule([
            'pay_type'          => 'require|integer',
            'pay_channel_code'  => 'require|string',
            'actual_amount'     => 'require|number',
            'amount'            => 'require|number',
            'device_system'     => 'require|integer',
            'currency_code'     => 'string',
            'agent_pid'         => 'string',
            'agent_id'          => 'string',
            'extra_info'        => 'array',
        ]);

        // 启动事务
        Db::startTrans();
        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $agent_pid      = $data['agent_pid'] ?? '';
            $agent_id       = $data['agent_id'] ?? '';
            $extra_info     = empty($data['extra_info']) ? '[]' : json_encode($data['extra_info'], JSON_UNESCAPED_UNICODE);
            $currency_code  = $data['currency_code'] ?? 'CNY';
            $now            = time();
            $uid            = request()->auth;
            $order_sn       = $this->make_order_id();

            //其他进程正在执行
            $lock_name = sprintf('%s', __FUNCTION__);
            if(!cls_redis_lock::auto_lock($lock_name))
            {
                $this->exception("其他进程占用中", -1);
            }

            //获取用户信息
            $user = new UserModel();
            $user_info = $user->find($uid);
            if(empty($user_info)) {
                $this->exception('用户不存在', -2);
            }
            $user_info = $user_info->toArray();

            //注册日与下单日为同一天为1=新用户, 否则为2=老用户
            $user_type = $now - strtotime($user_info['reg_time']) >= (24 * 3600) ? 2 : 1;

            //组装数据
            $save_data = [
                'order_sn'          => $order_sn,
                'order_type'        => 1,
                'agent_pid'         => $agent_pid,
                'agent_id'          => $agent_id,
                'user_type'         => $user_type,
                'uid'               => $uid,
                'actual_amount'     => $data['actual_amount'],
                'amount'            => $data['amount'],
                'currency_code'     => $currency_code,
                'device_system'     => $data['device_system'],
                'pay_channel_code'  => $data['pay_channel_code'],
                'pay_type'          => $data['pay_type'],
                'pay_status'        => 0,
                'ip'                => request()->ip(),
                'user_agent'        => request()->header('User-Agent'),
                'status'            => 0, //订单状态 0未付款 1已完成 -1未拉起
                'extra_info'        => $extra_info,
                'create_time'       => time(),
            ];
            //添加
            $this->model->save($save_data);

            // 提交事务
            Db::commit();

            $ret_data = [
                'order_sn' => $order_sn,
            ];
        }
        catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            $status = $this->get_exception_status($e);
            //写入日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data
            ]);
        }
        return $status;
    }

    /**
     * 生成订单号(默认19位)
     * @param $num
     * @return string
     */
    public function make_order_id($num = 7)
    {
        $number = cls_util::random('numeric', $num);
        return date("ymdHis").$number;
    }
}
