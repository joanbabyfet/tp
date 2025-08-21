<?php
declare (strict_types = 1);

namespace app\home\controller;

use think\App;

class Index extends Base
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function index()
    {
        return '您好！这是一个[home]示例应用';
    }
}
