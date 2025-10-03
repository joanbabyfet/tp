<?php
declare (strict_types = 1);

namespace app\service;

class TestService
{
    private static $instance = null; //私有静态变量

    private function __construct() { //防止外部直接创建对象

    }

    public static function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function test()
    {
        echo '单例模式测试';
    }
}
