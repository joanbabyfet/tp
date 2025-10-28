<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\admin\controller\BaseController;
use app\common\service\UploadService;
use app\common\traits\SmsTrait;
use app\common\traits\UploadTrait;
use think\captcha\facade\Captcha;

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
}
