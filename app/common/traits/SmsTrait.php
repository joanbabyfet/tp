<?php

namespace app\common\traits;

use app\common\service\sms\SmsContext;
use app\common\service\sms\SmsFactory;
use app\job\SmsJob;
use think\facade\Cache;
use think\facade\Lang;
use think\facade\Queue;

trait SmsTrait
{
    /**
     * 发送短信验证码
     * @return \think\response\Json
     */
    public function send_sms_code()
    {
        $code               = mt_rand(100000, 999999);              //生成6位随机数
        $phone              = request()->post('phone');             //手机号
        $img_verify_code    = request()->post('img_verify_code');   //图片验证码

        if(empty($phone) || empty($img_verify_code)) {
            return $this->invalid_params();
        }

        //先检测验证码(1次性)
        if(!captcha_check($img_verify_code)) {
            return $this->error(Lang::get('common_verify_code_error'), -1);
        }

        //检测发送次数是否超过限制
        $count  = 1;    //发送次数
        $cache_key = sprintf("sms_verify_code:%s", $phone);
        if(Cache::has($cache_key)) {
            $info = Cache::store('redis')->get($cache_key);
            $count = $info['count'] + 1;
            if($count > 20) {
                return $this->error('您当天累计已发送20次', -2);
            }
        }

        //发送短信验证码
        $data = [
            'phone' => $phone,
            'code'  => $code,
        ];
        $is_push = Queue::push(SmsJob::class, $data, $queue = null);
        if(!$is_push) {
            return $this->error(Lang::get('common_send_fail'), -3);
        }
        //写入缓存
        $expire = 180; //发送短信间隔时间, 默认3分钟
        Cache::store('redis')->set($cache_key, [
            'code'  => $code,  //验证码
            'count' => $count, //发送次数
            'time'  => time()  //发送时间
        ], $expire);

        return $this->success();
    }

    /**
     * 检查短信验证码
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function check_sms_code()
    {
        $phone          = request()->post('phone');         //手机号
        $verify_code    = request()->post('verify_code');   //验证码
        if(empty($phone) || empty($verify_code)) {
            return $this->invalid_params();
        }

        $cache_key = sprintf("sms_verify_code:%s", $phone);
        $info = Cache::store('redis')->get($cache_key);
        if(empty($info) || strtotime('-3 minute') > $info['time']) {
            return $this->error(Lang::get('common_verify_code_expired'), -1);
        }
        if($info['code'] != $verify_code) {
            return $this->error(Lang::get('common_verify_code_error'), -2);
        }

        return $this->success();
    }
}
