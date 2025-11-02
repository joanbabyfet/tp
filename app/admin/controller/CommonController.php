<?php
declare (strict_types = 1);

namespace app\admin\controller;


use app\common\service\UploadService;
use app\common\traits\SmsTrait;
use app\common\traits\UploadTrait;

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
     * 获取图片验证码(api模式)
     * @return \think\response\Json
     */
    public function captcha()
    {
        $captcha = Captcha::create();

        return $this->success($captcha);
    }
}
