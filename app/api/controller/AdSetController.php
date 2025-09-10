<?php
declare (strict_types = 1);

namespace app\api\controller;


use app\model\AdModel;
use app\model\AdSetModel;
use think\App;
use think\facade\Cache;
use think\facade\Lang;
use think\Request;

class AdSetController extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page   = $this->request->param('page', 1);
        $page_size   = $this->request->param('page_size', 10);
        $offset = ($page - 1) * $page_size;

        //获取广告设置列表
        $map = ['status' => 1];
        $ad_set_list = AdSetModel::where($map)->limit($offset , (int)$page_size)->order(['create_time' => 'desc'])->select();
        //获取总条数
        $count = AdSetModel::where($map)->count();
        $res = [
            'count' => $count,
            'list' => $ad_set_list
        ];
        return $this->success($res);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function detail()
    {
        $id = $this->request->param('id');
        if(empty($id))
        {
            return $this->invalid_params();
        }

        $cache_key = sprintf("adset:id:%s", $id);
        $res = Cache::store('redis')->get($cache_key);
        if(empty($res)) {
            //获取详情
            $map = [];
            $map['status'] = 1;
            $map['id'] = $id;
            $res = AdSetModel::where($map)->find();
            //写入缓存
            Cache::store('redis')->set($cache_key, $res);
        }
        return $this->success($res);
    }
}
