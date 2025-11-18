<?php
declare (strict_types = 1);

namespace app\home\controller;

use app\model\ArticleModel;
use think\facade\View;

class IndexController extends BaseController
{
    public function index()
    {
        $articleModel = new ArticleModel();
        $articles = $articleModel->paginate(10);
        $page = $articles->render(); //分页器
        $total = $articles->total(); //总条数

        View::assign([
            'articles'  => $articles,
            'page'      => $page,
            'total'     => $total,
            'pagesize'  =>  10,
        ]);
        return View::fetch();
    }

    public function workerman()
    {
        return View::fetch();
    }
}
