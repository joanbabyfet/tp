<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\lib\Response;
use app\model\AdModel;
use app\model\AdSetModel;
use think\facade\Cache;
use think\facade\Lang;
use think\facade\Validate;
use think\Request;

class AdSet extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $name = $this->request->param('name');
        $status = $this->request->param('status');
        $page   = $this->request->param('page', 1);
        $page_size   = $this->request->param('page_size', 10);
        $offset = ($page - 1) * $page_size;

        //获取广告列表
        $map = []; //筛选
        $name and $map['name'] = ['like', "%{$name}%"];
        is_numeric($status) and $map['status'] = $status;
        $ad_set_list = AdSetModel::where($map)->limit($offset , (int)$page_size)->order(['create_time' => 'desc'])->select();
        //获取总条数
        $count = AdSetModel::where($map)->count();
        $res = [
            'count' => $count,
            'list' => $ad_set_list
        ];
        return response::success('',0, $res);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function add()
    {
        $validate = Validate::rule([
            'name'     => 'require|string',
            'status'    => 'require|string',
        ]);
        if (!$validate->check($this->request->post())) {
            return response::error(Lang::get('common_param_error'), -1);
        }

        //添加
        $data = [
            'name' => $this->request->post('name'),
            'status' => $this->request->post('status'),
            'create_user' => '1',
        ];
        $adset_model = new AdSetModel();
        $status = $adset_model->save($data);
        if(!$status) {
            return response::error(Lang::get('common_add_fail'), -2);
        }
        return response::success(Lang::get('common_add_suc'),0);
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
            return response::error(Lang::get('common_param_error'), -1);
        }
        //获取详情
        $res = AdSetModel::where(['id' => $id])->find();
        return response::success('',0, $res);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit()
    {
        $validate = Validate::rule([
            'id'        => 'require|string',
            'name'     => 'require|string',
            'status'    => 'require|string',
        ]);
        if (!$validate->check($this->request->post())) {
            return response::error(Lang::get('common_param_error'), -1);
        }
        $id = $this->request->post('id');

        //更新
        $data = [
            'name' => $this->request->post('name'),
            'status' => $this->request->post('status'),
            'update_user' => '1',
            'update_time' => time()
        ];
        $status = AdSetModel::where(['id' => $id])->update($data);
        if(!$status) {
            return response::error(Lang::get('common_update_fail'), -2);
        }
        //干掉緩存
        $cache_key = sprintf("adset:id:%s", $id);
        Cache::store('redis')->delete($cache_key);

        return response::success(Lang::get('common_update_suc'),0);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete()
    {
        $id = $this->request->post('id');
        if(empty($id))
        {
            return response::error(Lang::get('common_param_error'), -1);
        }
        //软删除
        $status = AdSetModel::destroy($id);
        if(!$status) {
            return response::error(Lang::get('common_delete_fail'), -2);
        }
        //干掉緩存
        $cache_key = sprintf("adset:id:%s", $id);
        Cache::store('redis')->delete($cache_key);

        return response::success(Lang::get('common_delete_suc'),0);
    }
}
