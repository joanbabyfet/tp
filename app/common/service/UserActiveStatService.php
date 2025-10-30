<?php

namespace app\common\service;

use app\common\lib\cls_response;
use app\common\lib\cls_util;
use app\model\UserActiveStatModel;
use think\facade\Lang;
use think\facade\Validate;

class UserActiveStatService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new UserActiveStatModel();
    }

    public function generate_data(array $data)
    {
        //参数过滤
        $validate = Validate::rule([
            'from_date' => 'string',    //筛选某日开始日期 例 2022/07/03
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $to_timezone = config('config.to_timezone');
            $from_date = empty($data['from_date']) ? '2019/01/01' : $data['from_date'];
            $from_time = cls_util::date_convert_timestamp("$from_date 00:00:00", $to_timezone); //需要转化的时区，东七区是越南时间

            //根据用户id与登入日期分组并去重, 只关注日期不关注时间

            $data = [
                'date' => '2025/10/30',
                'agent_id' => 'xxx',
                'timezone' => 'ETC/GMT-8',
            ];

            //添加或更新数据
            $this->model->duplicate(['user_count' => 10, 'd1' => 1, 'd3' => 2, 'd7' => 3, 'd14' => 4, 'd30' => 5])->insert($data);
        }
        catch (\Exception $e) {
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
}