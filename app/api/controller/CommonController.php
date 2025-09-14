<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\admin\controller\BaseController;
use app\common\traits\SmsTrait;
use app\common\traits\UploadTrait;

class CommonController extends BaseController
{
    use UploadTrait, SmsTrait;
}
