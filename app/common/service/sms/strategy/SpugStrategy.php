<?php

namespace app\common\service\sms\strategy;


use app\common\lib\cls_response;
use app\common\service\BaseService;
use app\common\service\sms\SmsStrategy;
use GuzzleHttp\Client;
use think\facade\Lang;
use think\facade\Validate;

/**
 * 创建具体策略类
 */
class SpugStrategy extends BaseService implements SmsStrategy
{
    public function send(array $data)
    {
        //参数过滤
        $validate = Validate::rule([
            'phone'     => 'require|string',    //手机号
            'code'      => 'require|integer',    //短信验证码
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $spug_id    = config('config.spug_id'); //模板编号
            $phone      = $data['phone'];
            $code       = $data['code'];

            $headers = [
                'Content-Type'  => 'application/json',
            ];
            $param = [
                'targets'   => $phone,
                'code'      => $code,
            ];

            $client = new Client();
            $res = $client->post('https://push.spug.cc/send/'.$spug_id, [
                'headers'   => $headers,
                'json'      => $param,
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
