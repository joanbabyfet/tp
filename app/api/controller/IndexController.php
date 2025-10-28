<?php
declare (strict_types = 1);

namespace app\api\controller;

class IndexController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->success();
    }
}
