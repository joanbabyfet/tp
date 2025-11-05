<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\model\UserActiveStatModel;

class TestController extends BaseController
{
    public function demo()
    {
//        $userActiveStatModel = new UserActiveStatModel();
//        $data = [
//            'date' => '2025/11/05',
//            'agent_id' => 'xxx',
//            'timezone' => 'ETC/GMT-8',
//            'user_count' => 10,
//            'd1' => 11,
//            'd3' => 22,
//            'd7' => 33,
//            'd14' => 44,
//            'd30' => 55,
//        ];
//        $userActiveStatModel->InsertOrUpdate($data, ['user_count', 'd1']);

        return $this->success();
    }
}
