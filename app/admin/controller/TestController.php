<?php
declare (strict_types = 1);

namespace app\admin\controller;

class TestController extends BaseController
{
    public function demo()
    {
        $ret_data = [];
//        $data = [
//            'user_type' => 'user',
//            'uid'       => '713ddb372ebadfeb0c7abda784b08459',
//            'amount'    => 2,
//        ];
        $data = [
            'user_type'     => 'user',
            'uid'           => '713ddb372ebadfeb0c7abda784b08459',
            'to_user_type'  => 'user',
            'to_uid'        => '89451e9989dd58e79767e0386a7eed05',
            'amount'        => 2,
        ];
        app('wallet')->transfer($data, $ret_data);

        return $this->success($ret_data);
    }
}
