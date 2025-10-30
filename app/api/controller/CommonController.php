<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\admin\controller\BaseController;
use app\common\service\MailService;
use app\common\service\UploadService;
use app\common\traits\SmsTrait;
use app\common\traits\UploadTrait;
use think\captcha\facade\Captcha;
use think\facade\Cache;
use think\facade\Lang;

class CommonController extends BaseController
{
    use UploadTrait, SmsTrait;

    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        parent::__construct();
        $this->uploadService = $uploadService;
    }

    /**
     * 获取图片验证码
     * @return \think\response\Json
     */
//    public function captcha()
//    {
//        $id = mt_rand(100000, 999999);
//        $uniqid = uniqid("$id");
//        //返回 https://example.com/captcha/292867690067b1c1b0f.html
//        $url = request()->domain().captcha_src($uniqid);
//
//        $res = [
//            'src'       => $url,
//            'uniqid'    => $uniqid
//        ];
//        return $this->success($res);
//    }

    /**
     * 获取图片验证码(api模式)
     * @return \think\response\Json
     */
    public function captcha()
    {
        $captcha = Captcha::create();

        return $this->success($captcha);
    }

    /**
     * 发送邮箱验证码
     * @return \think\response\Json
     */
    public function send_email_code()
    {
        $code   = mt_rand(100000, 999999);    //生成6位随机数
        $email  = request()->post('email');   //邮箱
        if(empty($email)) {
            return $this->invalid_params();
        }

        $tpl = config('config.text_tpl.login');
        $text = str_replace('{{code}}', (string)$code, $tpl);

        $mail_service = new MailService();
        $data = [
            'to'        => $email,
            'subject'   => config('config.app_name'),
            'body'      => $text,
        ];
        $status = $mail_service->send($data);
        if($status < 0) {
            return $this->error(Lang::get('common_send_fail'), -1);
        }
        //写入缓存
        $expire = 300; //发送短信间隔时间, 默认5分钟
        $cache_key = sprintf("email_verify_code:%s", $email);
        Cache::store('redis')->set($cache_key, [
            'code'  => $code,  //验证码
            'time'  => time()  //发送时间
        ], $expire);

        return $this->success();
    }

    /**
     * 检查邮箱验证码
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function check_email_code()
    {
        $email          = request()->post('email');         //邮箱
        $verify_code    = request()->post('verify_code');   //验证码
        if(empty($email) || empty($verify_code)) {
            return $this->invalid_params();
        }

        $cache_key = sprintf("email_verify_code:%s", $email);
        $info = Cache::store('redis')->get($cache_key);
        if(empty($info) || strtotime('-5 minute') > $info['time']) {
            return $this->error(Lang::get('common_verify_code_expired'), -1);
        }
        if($info['code'] != $verify_code) {
            return $this->error(Lang::get('common_verify_code_error'), -2);
        }

        return $this->success();
    }
}
