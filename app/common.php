<?php
// 应用公共文件

use think\facade\Log;

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
     * @return true
     */
    function logger($name, $data, $channel = '')
    {
        $data_str = $data;
        if(is_array($data) || is_object($data))
        {
            $data_str = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        if(empty($channel))
        {
            if (isset($data['status']) && $data['status'] <= 0)
            {
                //有狀態錯誤則記錄到錯誤日誌
                Log::error("{$name}->{$data_str}\n\n");
            }
            else
            {
                //普通日誌
                Log::info("{$name}->{$data_str}\n\n");
            }
        }
        else
        {
            if (isset($data['status']) && $data['status'] <= 0)
            {
                //有狀態錯誤則記錄到錯誤日誌
                Log::channel($channel)->error("{$name}->{$data_str}\n\n");
            }
            else
            {
                //普通日誌
                Log::channel($channel)->info("{$name}->{$data_str}\n\n");
            }
        }

        return true;
    }
}