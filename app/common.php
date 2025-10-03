<?php
// 应用公共文件

use app\common\lib\cls_util;

if (!function_exists('pr')) {
    /**
     * 打印
     * @param array $data
     */
    function pr($data = [])
    {
        echo '<pre>';
        print_r($data);
        exit;
    }
}

if (!function_exists('logger')) {
    /**
     * 写入日志
     * @param $name
     * @param $data
     * @param $channel
     * @return true
     */
    function logger($name, $data, $channel = '')
    {
        return cls_util::log($name, $data, $channel);
    }
}
