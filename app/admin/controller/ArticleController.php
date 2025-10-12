<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\service\AdminOplogService;
use app\common\service\ArticleService;
use think\facade\Cache;

class ArticleController extends BaseController
{
    protected $articleService;
    protected $adminOplogService;
    public function __construct(
        ArticleService $articleService,
        AdminOplogService $adminOplogService)
    {
        parent::__construct();
        $this->articleService = $articleService;
        $this->adminOplogService = $adminOplogService;
    }

    /**
     * 获取分页列表
     * @return \think\response\Json
     */
    public function index()
    {
        $title = request()->param('title');
        $status = request()->param('status');
        $page   = request()->param('page', 1);
        $page_size   = request()->param('page_size', 20);

        //筛选
        $where = [];
        $title and $where['title'] = ['like', "%{$title}%"];
        is_numeric($status) and $where['status'] = $status;
        $data = [
            'page'      => $page,
            'page_size' => $page_size,
            'where'     => $where,
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
        $id = request()->param('id');
        if(empty($id)) {
            return $this->invalid_params();
        }

        $status = $this->articleService->detail(['id' => $id], $ret_data);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }
        return $this->success($ret_data);
    }

    /**
     * 添加
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function add()
    {
        $data = request()->post();

        $status = $this->articleService->edit($data, $ret_data);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }
        //写入操作日志
        $this->adminOplogService->save("文章添加 {$ret_data}");

        return $this->success();
    }

    /**
     * 编辑
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function edit()
    {
        $data = request()->post();

        $status = $this->articleService->edit($data);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }

        //干掉緩存
        $cache_key = sprintf("article:id:%s", $data['id']);
        Cache::store('redis')->delete($cache_key);

        //写入操作日志
        $this->adminOplogService->save("文章修改 {$data['id']}");

        return $this->success();
    }

    /**
     * 删除
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete()
    {
        $id = request()->post('id');
        if(empty($id)) {
            return $this->invalid_params();
        }

        $status = $this->articleService->del(['id' => $id]);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }

        //干掉緩存
        $cache_key = sprintf("article:id:%s", $id);
        Cache::store('redis')->delete($cache_key);

        //写入操作日志
        $this->adminOplogService->save("文章删除 {$id}");

        return $this->success();
    }

    /**
     * 启用
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function enable()
    {
        $id = request()->post('id');
        if(empty($id)) {
            return $this->invalid_params();
        }

        $status = $this->articleService->enable(['id' => $id]);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }

        //干掉緩存
        $cache_key = sprintf("article:id:%s", $id);
        Cache::store('redis')->delete($cache_key);

        //写入操作日志
        $this->adminOplogService->save("文章启用 {$id}");

        return $this->success();
    }

    /**
     * 禁用
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function disable()
    {
        $id = request()->post('id');
        if(empty($id)) {
            return $this->invalid_params();
        }

        $status = $this->articleService->disable(['id' => $id]);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }

        //干掉緩存
        $cache_key = sprintf("article:id:%s", $id);
        Cache::store('redis')->delete($cache_key);

        //写入操作日志
        $this->adminOplogService->save("文章禁用 {$id}");

        return $this->success();
    }
}
