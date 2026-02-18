<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\service\ArticleService;

class ArticleController extends BaseController
{
    protected $articleService;
    public function __construct(
        ArticleService $articleService)
    {
        parent::__construct();
        $this->articleService = $articleService;
    }

    /**
     * 获取分页列表
     * @return \think\response\Json
     */
    public function index()
    {
        $title      = request()->param('title/s');
        $status     = request()->param('status/d');
        $page       = request()->param('page/d', 1);
        $page_size  = request()->param('page_size/d', 20);

        //筛选
        $where = [];
        !empty($title) && $where['title'] = ['like', "%{$title}%"];
        is_numeric($status) && $where['status'] = $status;

        $data = [
            'page'      => $page,
            'page_size' => $page_size,
            'where'     => $where,
            'count'     => 1,
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
        $status = $this->articleService->edit(request()->post(), $ret_data);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }
        return $this->success();
    }

    /**
     * 编辑
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function edit()
    {
        $data = array_merge(request()->post(), [
            'cache_key' => 'article:detail:%s',
        ]);
        $status = $this->articleService->edit($data);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }
        //写入操作日志
        $this->write_log("文章编辑 {$data['id']}");

        return $this->success();
    }

    /**
     * 删除
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete()
    {
        $id = request()->post('id/d');

        $data = [
            'id'        => $id,
            'cache_key' => 'article:detail:%s',
        ];
        $status = $this->articleService->del($data);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }
        //写入操作日志
        $this->write_log("文章删除 {$id}");

        return $this->success();
    }

    /**
     * 启用
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function enable()
    {
        $id = request()->post('id/d');

        $data = [
            'id'        => $id,
            'cache_key' => 'article:detail:%s',
        ];
        $status = $this->articleService->enable($data);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }
        //写入操作日志
        $this->write_log("文章启用 {$id}");

        return $this->success();
    }

    /**
     * 禁用
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function disable()
    {
        $id = request()->post('id/d');

        $data = [
            'id'        => $id,
            'cache_key' => 'article:detail:%s',
        ];
        $status = $this->articleService->disable($data);
        if($status < 0) {
            return $this->error($this->articleService->get_err_msg($status), $status);
        }
        //写入操作日志
        $this->write_log("文章禁用 {$id}");

        return $this->success();
    }
}
