<?php

namespace app\common\service;

use app\common\lib\cls_response;
use app\common\lib\cls_util;
use app\model\UserIncreaseStatModel;
use app\model\UserModel;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Validate;

class UserIncreaseStatService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new UserIncreaseStatModel();
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
            $from_date = empty($data['from_date']) ? '2025/01/01' : $data['from_date'];
            $from_time = cls_util::date_convert_timestamp("$from_date 00:00:00", $to_timezone); //需要转化的时区，东七区是越南时间
            $now = time();

            //获取用户增长数据(从主库获取数据而不是默认的从库)
            $data_map = [];
            $pre_user_count = [];
            $userModel = new UserModel();
            $list = $userModel->master()->field([
                'agent_id',
                "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(`reg_time`, '%Y/%m/%d %H:00'), '+8:00', '+7:00'), '%Y/%m/%d') as date",
                'COUNT(id) as user_increase_count',
            ])->where('reg_time', '>=', $from_time)->group('date, agent_id')->select()->toArray();
            foreach($list as $v)
            {
                //获取最近1条统计
                if(!isset($pre_user_count[$v['agent_id']]))
                {
                    $pre_user_count[$v['agent_id']] = $this->model->where('date', '<', $v['date'])
                        ->where('agent_id', '=', $v['agent_id'])
                        ->order(['date' => 'desc'])->value('user_count');
                }

                $key = $v['date'].'_'.$v['agent_id'];
                $user_count = $v['user_increase_count'] + $pre_user_count[$v['agent_id']];
                $data_map[$key] = [
                    'user_count'            =>  $user_count,
                    'user_increase_count'   =>  $v['user_increase_count'],
                ];
                $pre_user_count[$v['agent_id']] = $user_count;
            }

            //更新字段
            $update_fields = ['user_count', 'user_increase_count'];
            $update_data = [];
            foreach($data_map as $k => $v)
            {
                $key = explode('_', $k);
                $data_item = [
                    'date'          => $key[0],
                    'agent_id'      => $key[1],
                    'timezone'      => $to_timezone,
                    'create_time'   => $now,
                ];

                foreach ($update_fields as $field) //匹配字段
                {
                    $data_item[$field] = empty($data_map[$k][$field]) ? 0 : $data_map[$k][$field];
                }
                $update_data[] = $data_item;
            }

            //唯一索引重复值则批量更新否则批量添加
            $this->model->duplicate($update_fields)->insertAll($update_data);
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
