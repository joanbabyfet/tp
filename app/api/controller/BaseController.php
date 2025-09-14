<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\common\traits\ResponseJson;

class BaseController
{
    use ResponseJson;

    public function __construct()
    {

    }
}
