<?php

namespace app\service\sms\strategy;

use app\service\BaseService;
use app\service\sms\SmsStrategy;
use think\facade\Lang;
use think\facade\Log;
use Uni\UniClient;

/**
 * 创建具体策略类
 */
class UnimtxStrategy extends BaseService implements SmsStrategy
{
    public function send($phone, $code)
    {
        $access_key     = config('myconfig.unimtx.access_key'); //开发者ID
        $access_secret  = config('myconfig.unimtx.access_secret'); //密钥

        $status = 1;
        try {
            $client = new UniClient([
                'accessKeyId'       => $access_key,
                'accessKeySecret'   => $access_secret, // 若使用简易验签模式请删除此行
                'endpoint'          => 'https://api.unimtx.com' // 设置接入点到中国大陆, 若使用全球节点请移除此行代码
            ]);
            $res = $client->messages->send([
                'to' => $phone, // 以 E.164 格式传入手机号
                'templateId' => 'pub_otp_zh_ttl4',
                'templateData' => [
                    'code'  => $code,
                    'ttl'   => '10'
                ]
            ]);
            if(empty($res)) {
                $this->exception('请求失败', -2);
            }

            if ($res->code !== '0') { //非0為发送失败
                $this->exception(Lang::get('common_send_fail'), -3);
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
