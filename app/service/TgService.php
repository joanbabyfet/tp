<?php

namespace app\service;

use GuzzleHttp\Client;
use think\facade\Log;

class TgService extends BaseService
{
    /**
     * 发送消息
     * @param $chat_id
     * @param $text
     * @param $ret_data
     * @return int|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send($chat_id, $text, &$ret_data = [])
    {
        $token     = config('myconfig.telegram.token');

        $status = 1;
        try {
            $url    = 'https://api.telegram.org/bot' . $token . '/sendMessage';
            $headers = [];
            $param = [
                'chat_id'   => $chat_id,
                'text'      => $text,
            ];

            $client = new Client();
            $res = $client->post($url, [
                'headers' => $headers,
                'json' => $param,
            ]);
            if(empty($res->getBody())) {
                $this->exception('请求失败', -2);
            }

            $arr = json_decode($res->getBody(), true);
            if(!$arr['ok'])
            {
                $this->exception("数据验证失败", -3);
            }
            $ret_data = $arr['result'];
        }
        catch (\Exception $e) {
            $status = $this->get_exception_status($e);
            //记录日志
            Log::error(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'args'    => func_get_args()
            ]);
        }
        return $status;
    }
}
