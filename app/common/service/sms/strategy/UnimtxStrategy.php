<?php

namespace app\common\service\sms\strategy;

use app\common\lib\cls_response;
use app\common\service\BaseService;
use app\common\service\sms\SmsStrategy;
use think\facade\Lang;
use think\facade\Validate;
use Uni\UniClient;

/**
 * 创建具体策略类
 */
class UnimtxStrategy extends BaseService implements SmsStrategy
{
    public function send(array $data)
    {
        //参数过滤
        $validate = Validate::rule([
            'phone'     => 'require|string',    //手机号
            'code'      => 'require|string',    //短信验证码
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $access_key     = config('config.unimtx.access_key'); //开发者ID
            $access_secret  = config('config.unimtx.access_secret'); //密钥
            $phone      = $data['phone'];
            $code       = $data['code'];

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
