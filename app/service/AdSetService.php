<?php

namespace app\service;

use app\common\lib\ResponseCode;
use app\model\AdSetModel;
use think\facade\Lang;
use think\facade\Validate;

class AdSetService extends BaseService
{
    protected $model;
    public function __construct()
    {
        parent::__construct();
        $this->model = new AdSetModel();
    }

    /**
     * 编辑
     * @param array $data
     * @return int|mixed
     */
    public function edit(array $data, &$ret_data = [])
    {
        //参数过滤
        $validate = Validate::rule([
            'id'        => 'string',
            'name'      => 'require|string',
            'status'    => 'require|string',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), ResponseCode::SYS_PARAMS_ERROR);
            }
            $id = $data['id'] ?? 0;

            //组装数据
            $save_data = [
                'name'          => $data['name'],
                'status'        => $data['status'],
            ];

            if($id)
            {
                //更新
                $up = array_merge($save_data, [
                    'update_time'   => time()
                ]);
                $this->model->where('id', '=', $id)->update($up);
            }
            else
            {
                //添加
                $add = array_merge($save_data, [
                    'create_time'   => time()
                ]);
                $this->model->save($add);
                $last_insert_id = $this->model->id; //获取自增id

                $ret_data = $last_insert_id;
            }
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
