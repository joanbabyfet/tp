<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\service\AdService;
use think\App;
use think\facade\Cache;

class AdController extends BaseController
{
    protected $service;
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new AdService(); //实例化服务
    }

    /**
     * 获取分页列表
     * @return \think\response\Json
     */
    public function index()
    {
        $title = $this->request->param('title');
        $status = $this->request->param('status');
        $page   = $this->request->param('page', 1);
        $page_size   = $this->request->param('page_size', 20);

        //筛选
        $where = [];
        $title and $where['title'] = ['like', "%{$title}%"];
        is_numeric($status) and $where['status'] = $status;
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
        $id = $this->request->param('id');
        if(empty($id)) {
            return $this->invalid_params();
        }

        $status = $this->service->detail(['id' => $id], $ret_data);
        if($status < 0) {
            return $this->error($this->service->get_err_msg($status), $status);
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
        $data = $this->request->post();

        $status = $this->service->edit($data);
        if($status < 0) {
            return $this->error($this->service->get_err_msg($status), $status);
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
        $data = $this->request->post();

        $status = $this->service->edit($data);
        if($status < 0) {
            return $this->error($this->service->get_err_msg($status), $status);
        }

        //干掉緩存
        $cache_key = sprintf("ad:id:%s", $data['id']);
        Cache::store('redis')->delete($cache_key);

        return $this->success();
    }

    /**
     * 删除
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete()
    {
        $id = $this->request->post('id');
        if(empty($id)) {
            return $this->invalid_params();
        }

        $status = $this->service->del(['id' => $id]);
        if($status < 0) {
            return $this->error($this->service->get_err_msg($status), $status);
        }

        //干掉緩存
        $cache_key = sprintf("ad:id:%s", $id);
        Cache::store('redis')->delete($cache_key);

        return $this->success();
    }

    /**
     * 启用
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function enable()
    {
        $id = $this->request->post('id');
        if(empty($id)) {
            return $this->invalid_params();
        }

        $status = $this->service->enable(['id' => $id]);
        if($status < 0) {
            return $this->error($this->service->get_err_msg($status), $status);
        }

        //干掉緩存
        $cache_key = sprintf("ad:id:%s", $id);
        Cache::store('redis')->delete($cache_key);

        return $this->success();
    }

    /**
     * 禁用
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function disable()
    {
        $id = $this->request->post('id');
        if(empty($id)) {
            return $this->invalid_params();
        }

        $status = $this->service->disable(['id' => $id]);
        if($status < 0) {
            return $this->error($this->service->get_err_msg($status), $status);
        }

        //干掉緩存
        $cache_key = sprintf("ad:id:%s", $id);
        Cache::store('redis')->delete($cache_key);

        return $this->success();
    }
}
