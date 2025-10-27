<?php
declare (strict_types = 1);

namespace app\common\service;

use app\common\lib\cls_response;
use app\model\ArticleModel;
use think\facade\Lang;
use think\facade\Validate;

/**
 * 自定义文章服务类
 */
class ArticleService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new ArticleModel();
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
            'title'     => 'require|string',
            'content'   => 'require|string',
            'status'    => 'require|integer',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $id = $data['id'] ?? 0;

            //组装数据
            $save_data = [
                'title'         => $data['title'],
                'content'       => $data['content'],
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

    /**
     * 批量添加或更新
     * @param array $data
     * @return int|mixed
     */
    public function save_all(array $data)
    {
        //参数过滤
        $validate = Validate::rule([
            'data'             => 'require|array',
            'data.*.title'     => 'require|string',
            'data.*.content'   => 'require|string',
            'data.*.status'    => 'require|integer',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }

            //批量添加或更新(带主键字段)
            $this->model->saveAll($data['data']);
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
