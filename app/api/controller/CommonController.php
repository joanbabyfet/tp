<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\admin\controller\BaseController;
use app\common\traits\UploadTrait;
use think\Request;

class CommonController extends BaseController
{
    use UploadTrait;
}
