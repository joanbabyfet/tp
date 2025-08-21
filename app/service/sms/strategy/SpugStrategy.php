<?php

namespace app\service\sms\strategy;

use app\service\BaseService;
use app\service\sms\SmsStrategy;
use GuzzleHttp\Client;
use think\facade\Log;

/**
 * 创建具体策略类
 */
class SpugStrategy extends BaseService implements SmsStrategy
{
    public function send($phone, $code)
    {
        $spug_id     = config('myconfig.spug_id'); //模板编号

        $status = 1;
        try {
            $headers = [
                'Content-Type'  => 'application/json',
            ];
            $param = [
                'code'      => $code,
                'targets'   => $phone,
            ];

            $client = new Client();
            $res = $client->post('https://push.spug.cc/send/'.$spug_id, [
                'headers' => $headers,
                'json' => $param,
            ]);
            if(empty($res->getBody())) {
                $this->exception('请求失败', -2);
            }

            $arr = json_decode($res->getBody(), true);
            if($arr['code'] != 200)
            {
                $arr['code'] = $arr['code'] ?? '';
                $this->exception("数据验证失败 [CODE={$arr['code']}]", -3);
            }
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
