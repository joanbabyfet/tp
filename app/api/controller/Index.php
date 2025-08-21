<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\common\lib\Response;
use app\home\controller\Base;
use think\App;

class Index extends Base
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function index()
    {
        $data = [
            ['id'=>1001,'title'=>'标题1'],
            ['id'=>1002,'title'=>'标题2']
        ];
        //return response::success('',0, $data);
        return response::error('error',-1);
    }
}
