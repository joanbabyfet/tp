<?php
declare (strict_types = 1);

namespace app\admin\controller;


use app\common\traits\SmsTrait;
use app\common\traits\UploadTrait;

class CommonController extends BaseController
{
    use UploadTrait, SmsTrait;
}
