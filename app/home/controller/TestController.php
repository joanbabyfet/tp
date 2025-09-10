<?php
declare (strict_types = 1);

namespace app\home\controller;

use think\Request;

class TestController extends BaseController
{
    public function demo()
    {
        //验证码
        $captcha = $this->request->param('captcha');
        if(!captcha_check($captcha)){
            pr('fail');
        }
        pr('success');
    }
}
