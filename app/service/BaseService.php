<?php
declare (strict_types = 1);

namespace app\service;

use app\common\lib\ResponseCode;
use think\facade\Lang;
use think\facade\Validate;

class BaseService
{
    protected $model;
    public static $unknow_err_status = -1211; //未知错误,一般都是数据库死锁
    public static $msg_maps = []; //错误映射

    public function __construct()
    {

    }

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
            'page'      => 'integer',   // 第几页
            'page_size' => 'integer',   // 每页展示几条
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), ResponseCode::SYS_PARAMS_ERROR);
            }
            $page       = $data['page'] ?? 1;
            $page_size  = $data['page_size'] ?? 20;
            $where      = $data['where'] ?? [];

            // 常用查询条件
            $offset = ($page - 1) * $page_size;
            $order_by = empty($sort) ? ['create_time' => 'desc'] : $sort;
            $list = $this->model->where($where)->limit($offset , (int)$page_size)->order($order_by)->field('*')->select();
            //获取总条数
            $count = $this->model->where($where)->count();
            //返回结果
            $ret_data = [
                'count' => $count,
                'list' => $list
            ];
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
                $this->exception(Lang::get('common_param_error'), ResponseCode::SYS_PARAMS_ERROR);
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
                $this->exception(Lang::get('common_param_error'), ResponseCode::SYS_PARAMS_ERROR);
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
                $this->exception(Lang::get('common_param_error'), ResponseCode::SYS_PARAMS_ERROR);
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
                $this->exception(Lang::get('common_param_error'), ResponseCode::SYS_PARAMS_ERROR);
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
