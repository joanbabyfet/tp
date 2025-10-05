<?php
namespace app;

// 应用请求对象类
use app\common\lib\cls_util;

class Request extends \think\Request
{
    public function country()
    {
        //$ip = $this->ip();
        $ip = '14.161.27.200'; //测试用(VN)
        $country = cls_util::ip2country($ip);
        return $country;
    }
}
