<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\service\ArticleService;
use think\facade\Cache;

class ArticleController extends BaseController
{
    protected $service;
    public function __construct()
    {
        parent::__construct();
        $this->service = new ArticleService(); //实例化服务
    }

    /**
     * 获取分页列表
     * @return \think\response\Json
     */
    public function index()
    {
        $page   = request()->param('page', 1);
        $page_size   = request()->param('page_size', 10);

        //筛选
        $where = ['status' => 1];
        $data = [
            'page'      => $page,
            'page_size' => $page_size,
            'where'     => $where,
        ];
        $status = $this->service->get_list($data, $ret_data);
        if($status < 0) {
            return $this->error($this->service->get_err_msg($status), $status);
        }
        return $this->success($ret_data);
    }

    /**
     * 获取详请
     * @return \think\response\Json
     */
    public function detail()
    {
        $id = request()->param('id');
        if(empty($id)) {
            return $this->invalid_params();
        }

        $cache_key = sprintf("article:id:%s", $id);
        $res = Cache::store('redis')->get($cache_key);
        if(empty($res)) {
            //获取详情
            $this->service->detail(['id' => $id], $res);
            //写入缓存
            Cache::store('redis')->set($cache_key, $res);
        }
        return $this->success($res);
    }
}
