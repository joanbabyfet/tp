<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\common\service\ArticleService;

class ArticleController extends BaseController
{
    protected $articleService;
    public function __construct(ArticleService $articleServic)
    {
        parent::__construct();
        $this->articleService = $articleServic; //实例化服务
    }

    /**
     * 获取分页列表
     * @return \think\response\Json
     */
    public function index()
    {
        $page       = request()->param('page/d', 1);
        $page_size  = request()->param('page_size/d', 10);

        //筛选
        $where = ['status' => 1];
        $data = [
            'page'      => $page,
            'page_size' => $page_size,
            'where'     => $where,
            'count'     => 1,
            'is_cache'  => 1, //前台使用缓存
            'cache_key' => 'article:list:%d:%d',
        ];
        $status = $this->articleService->get_list($data, $ret_data);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }
        return $this->success($ret_data);
    }

    /**
     * 获取详请
     * @return \think\response\Json
     */
    public function detail()
    {
        $id = request()->param('id/d');

        //获取详情
        $data = [
            'id'        => $id,
            'is_cache'  => 1, //前台使用缓存
            'cache_key' => 'article:detail:%d',
        ];
        $status = $this->articleService->detail($data, $res);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }
        return $this->success($res);
    }
}
