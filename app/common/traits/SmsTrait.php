<?php

namespace app\common\traits;

use app\service\sms\SmsContext;
use app\service\sms\SmsFactory;

trait SmsTrait
{
    /**
     * 发送短信验证码
     * @return \think\response\Json
     */
    public function send_verify_code()
    {
        $phone = $this->request->post('phone');
        if(empty($phone)) {
            return $this->invalid_params();
        }
        $code = rand(100000, 999999); //生成6位随机数

        //发送短信验证码
        $strategy = SmsFactory::strategy('unimtx'); //选择策略
        $smsContext = new SmsContext($strategy);
        $status = $smsContext->send($phone, $code);
        if($status < 0) {
            return $this->error($strategy->get_err_msg($status), $status);
        }
        return $this->success();
    }
}