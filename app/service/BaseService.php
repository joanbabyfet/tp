<?php
declare (strict_types = 1);

namespace app\service;

class BaseService
{
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
}
