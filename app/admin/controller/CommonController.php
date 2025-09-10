<?php
declare (strict_types = 1);

namespace app\admin\controller;


use app\common\traits\UploadTrait;
use app\service\sms\SmsContext;
use app\service\sms\SmsFactory;
use app\service\sms\SmsStrategy;
use app\service\sms\strategy\UnimtxStrategy;
use think\captcha\Captcha;
use think\facade\App;
use think\facade\Validate;

class CommonController extends BaseController
{
    use UploadTrait;

    /**
     * 发送短信验证码
     * @return \think\response\Json
     */
    public function send_verify_code()
    {
        //参数过滤
        $validate = Validate::rule([
            'phone'  => 'require',
        ]);
        $data = [
            'phone' => $this->request->post('phone'),
        ];
        if (!$validate->check($data)) {
            return $this->invalid_params();
        }
        $code = rand(100000, 999999); //生成6位随机数

        //发送短信验证码
        $strategy = SmsFactory::strategy('unimtx'); //选择策略
        $smsContext = new SmsContext($strategy);
        $status = $smsContext->send($data['phone'], $code);
        if($status < 0) {
            return $this->error($strategy->get_err_msg($status), $status);
        }
        return $this->success();
    }

    public function unimtx()
    {
        $phone = '';
        $code = '';
        $unimtxStrategy = App::make(UnimtxStrategy::class); // 从容器获取Unimtx短信策略
        return $this->send($unimtxStrategy, $phone, $code);
    }

    public function send(SmsStrategy $smsStrategy, $phone, $code)
    {
        $result = $smsStrategy->send($phone, $code);
        return $result;
    }
}
