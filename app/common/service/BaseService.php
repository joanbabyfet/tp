<?php
declare (strict_types = 1);

namespace app\common\service;

use app\common\lib\cls_response;
use think\facade\Lang;
use think\facade\Validate;

class BaseService
{
    protected $model;
    public static $unknow_err_status = -1211; //未知错误,一般都是数据库死锁
    public static $msg_maps = []; //错误映射

    /**
     * 抛异常封装
     * @param $msg
     * @param $code
     * @return mixed
     * @throws \Exception
     */
    public static function exception($msg = '', $code = null)
    {
        $code = $code ? $code : static::$unknow_err_status;
        throw new \Exception($msg, $code);
    }

    /**
     * 统一处理错误后的status值，防止乱抛出
     * @param \Exception $e
     * @return int|mixed
     */
    public static function get_exception_status(\Exception $e)
    {
        $err_code = $e->getCode();
        $status = $err_code >= 0 ? static::$unknow_err_status : $err_code;
        self::$msg_maps[$status] = $e->getMessage();

        return $status;
    }

    public static function get_err_msg($status)
    {
        return isset(static::$msg_maps[$status]) ? static::$msg_maps[$status] : 'Unknow error';
    }

    /**
     * 获取分页列表
     * @param array $data
     * @param $sort
     * @param $ret_data
     * @return int|mixed
     */
    public function get_list(array $data, &$ret_data = [], $sort = [])
    {
        //参数过滤
        $validate = Validate::rule([
            'page'          => 'integer',   // 第几页
            'page_size'     => 'integer',   // 每页展示几条
            'count'         => 'integer',   // 显示总条数
            'limit'         => 'integer',   // 显示几条
            'field'         => 'array',     // 弹性字段
            'is_master'     => 'integer',   // 是否查主库
            'lock'          => 'integer',    // 锁表
            'share'         => 'integer',    // 锁表
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $page       = $data['page'] ?? 1;
            $page_size  = $data['page_size'] ?? 20;
            $where      = $data['where'] ?? [];
            $count      = $data['count'] ?? 0;
            $limit      = $data['limit'] ?? 0;
            $field      = $data['field'] ?? ['*']; //默认显示全部字段
            $is_master  = $data['is_master'] ?? 0;
            $lock       = $data['lock'] ?? 0;
            $share      = $data['share'] ?? 0;

            // 常用查询条件
            $offset = ($page - 1) * $page_size;
            $order_by = empty($sort) ? ['create_time' => 'desc'] : $sort;

            $query = $this->model->where($where);
            //锁表只走主库，要不很容易悲剧
            if (!empty($lock) || !empty($share)) {
                // 优先锁定 lock
                if (!empty($lock)) {
                    $query->lock(true);
                } elseif (!empty($share)) {
                    $query->lock('lock in share mode');
                }
                // 标记主库
                $is_master = 1;
            }
            // 切换数据库
            if($is_master) {
                $query->master(true);
            }
            else {
                $query->master(false);
            }

            // 支持弹性字段
            if (!empty($field)) {
                $query->field($field);
            }
            //总条数(不受 limit 影响)
            $total = !empty($count) ? (int)$query->count() : 0;

            if(isset($data['page']) || isset($data['page_size'])) {
                $query->limit($offset, (int)$page_size);
            }
            elseif(isset($data['limit'])) {
                $query->limit($limit);
            }
            $list = $query->order($order_by)->select();

            //返回结果
            $ret_data = !empty($count) ? ['count' => $total, 'list'  => $list] : $list;
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
     * 获取详情
     * @param array $data
     * @param $ret_data
     * @return int|mixed
     */
    public function detail(array $data, &$ret_data = [])
    {
        //参数过滤
        $validate = Validate::rule([
            'id' => 'require',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $id = $data['id'];

            //获取单条
            $res = $this->model->find($id);
            $info = empty($res) ? [] : $res->toArray();

            // 数据格式化(预留扩展方法,可不用)
            if (method_exists($this->model, 'formatInfo')) {
                $info = $this->model->formatInfo($info);
            }
            $ret_data = $info;
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
     * 删除(批量删除)
     * @param array $data
     * @return int|mixed
     */
    public function del(array $data)
    {
        //参数过滤
        $validate = Validate::rule([
            'id' => 'require',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $id = $data['id'];

            //删除
            $this->model->destroy($id);
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
     * 启用
     * @param array $data
     * @param $ret_data
     * @return int|mixed
     */
    public function enable(array $data)
    {
        //参数过滤
        $validate = Validate::rule([
            'id' => 'require|integer',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $id = $data['id'] ?? 0;

            $update_data = [
                'status'        => 1,
                'update_time'   => time()
            ];
            //更新
            $this->model->where('id', '=', $id)->update($update_data);
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
     * 禁用
     * @param array $data
     * @param $ret_data
     * @return int|mixed
     */
    public function disable(array $data)
    {
        //参数过滤
        $validate = Validate::rule([
            'id' => 'require|integer',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $id = $data['id'] ?? 0;

            $update_data = [
                'status'        => 0,
                'update_time'   => time()
            ];
            //更新
            $this->model->where('id', '=', $id)->update($update_data);
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
