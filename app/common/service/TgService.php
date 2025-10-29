<?php

namespace app\common\service;

use app\common\lib\cls_response;
use GuzzleHttp\Client;
use think\facade\Lang;
use think\facade\Validate;

class TgService extends BaseService
{
    /**
     * 发送消息
     * @param array $data
     * @param $ret_data
     * @return int|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(array $data, &$ret_data = [])
    {
        //参数过滤
        $validate = Validate::rule([
            'chat_id'   => 'require|string',
            'text'      => 'require|string',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $token     = config('config.telegram.token');
            $url    = 'https://api.telegram.org/bot' . $token . '/sendMessage';
            $headers = [];
            $param = [
                'chat_id'   => $data['chat_id'],
                'text'      => $data['text'],
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
