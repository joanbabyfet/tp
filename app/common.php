<?php
// 应用公共文件

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
