<?php

namespace app\common\service;

use app\common\lib\cls_auth;
use app\common\lib\cls_response;
use app\common\lib\cls_util;
use app\job\AdminLoginJob;
use app\model\AdminModel;
use think\facade\Lang;
use think\facade\Queue;
use think\facade\Session;
use think\facade\Validate;

class AdminService extends BaseService
{
    protected $model;
    protected $adminLoginService;
    public function __construct()
    {
        $this->model = new AdminModel();
        $this->adminLoginService = new AdminLoginService();
    }

    /**
     * 编辑
     * @param array $data
     * @return int|mixed
     */
    public function edit(array $data, &$ret_data = [])
    {
        //参数过滤
        $validate = Validate::rule([
            'id'        => 'string',
            'username'  => 'require|string',
            'password'  => 'require|string',
            'realname'  => 'require|string',
            'email'     => 'string',
            'status'    => 'require|integer',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $id     = $data['id'] ?? 0;
            $email  = $data['email'] ?? '';

            //组装数据
            $save_data = [
                'username'  => $data['username'],
                'password'  => cls_util::get_password($data['password']),
                'realname'  => $data['realname'],
                'email'     => $email,
                'status'    => $data['status'],
            ];

            if($id)
            {
                //更新
                $up = array_merge($save_data, [
                    'update_time'   => time()
                ]);
                $this->model->where('id', '=', $id)->update($up);
            }
            else
            {
                //添加
                $add = array_merge($save_data, [
                    'create_time'   => time()
                ]);
                $this->model->save($add);
                $last_insert_id = cls_util::random(); //获取自增id

                $ret_data = $last_insert_id;
            }
        }
        catch (\Exception $e) {
            $status = $this->get_exception_status($e);
            //写入日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data
            ]);
        }
        return $status;
    }

    /**
     * 修改密码
     * @param array $data
     * @return int|mixed
     */
    public function edit_pwd(array $data)
    {
        //参数过滤
        $validate = Validate::rule([
            'id'                => 'require|string',
            'password'          => 'require|string', //原密码
            'new_password'      => 'require|string', //新密码
            're_new_password'   => 'require|string', //重复新密码
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $password           = $data['password'];
            $new_password       = $data['new_password'];
            $re_new_password    = $data['re_new_password'];
            $id                 = $data['id'];

            //获取用户信息
            $admin = $this->model->find($id);
            if(empty($admin)) {
                $this->exception('该用户不存在', -1);
            }
            $admin_info = $admin->toArray();

            //两次密码不一致
            if($new_password != $re_new_password) {
                $this->exception(Lang::get('common_pwd_and_check_pwd_different_invalid'), -2);
            }

            //原密码错误
            if(cls_util::get_password($password) != $admin['password']) {
                $this->exception(Lang::get('common_old_pwd_error'), -3);
            }

            //更新
            $up = [
                'password' => cls_util::get_password($new_password)
            ];
            $this->model->where('id', '=', $id)->update($up);
        }
        catch (\Exception $e) {
            $status = $this->get_exception_status($e);
            //写入日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data
            ]);
        }
        return $status;
    }

    public function login($data, &$ret_data = [])
    {
        //参数过滤
        $validate = Validate::rule([
            'username'      => 'require|string',
            'password'      => 'require|string',
            'verify_code'   => 'require|string', //图片验证码
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $username       = $data['username'];
            $password       = $data['password'];
            $verify_code    = $data['verify_code'];
            $login_ip       = request()->ip();
            $agent          = request()->header('User-Agent');

            //先检测验证码
            if(!captcha_check($verify_code)) {
                $this->exception(Lang::get('common_verify_code_error'), -1);
            }

            $where = [
                'username' => $username
            ];
            $admin = $this->model->where($where)->find();
            if(empty($admin)) {
                //通过队列写入登录失败日志
                $log = [
                    'uid'           => '0',
                    'username'      => $username,
                    'login_ip'      => $login_ip,
                    'agent'         => $agent,
                    'login_status'  => 0,
                ];
                $is_push = Queue::push(AdminLoginJob::class, $log, $queue = null);

                $this->exception("用户名或密码无效", -1);
            }
            $original_password = $admin->password;
            $admin = $admin->hidden(['password'])->toArray(); //隐藏敏感信息字段

            if($admin['status'] == 0) {
                $this->exception("用户禁用", -2);
            }

            if(cls_util::get_password($password) != $original_password) {
                //通过队列写入登录失败日志
                $log = [
                    'uid'           => $admin['id'],
                    'username'      => $admin['username'],
                    'login_ip'      => $login_ip,
                    'agent'         => $agent,
                    'login_status'  => 0,
                ];
                $is_push = Queue::push(AdminLoginJob::class, $log, $queue = null);

                $this->exception("用户名或密码无效", -3);
            }

            //更新登录信息
            $up = [
                'session_id'    => Session::getId(), //web场景使用
                'login_ip'      => request()->ip(),
                'login_time'    => time()
            ];
            $this->model->where('id', '=', $admin['id'])->update($up);

            //通过队列写入登录成功日志
            $log = [
                'uid'           => $admin['id'],
                'username'      => $admin['username'],
                'login_ip'      => $login_ip,
                'agent'         => $agent,
                'login_status'  => 1,
            ];
            $is_push = Queue::push(AdminLoginJob::class, $log, $queue = null);

            $ret_data = array_merge($admin, [
                'token' => cls_auth::create_token($admin['id'])
            ]);
        }
        catch (\Exception $e) {
            $status = $this->get_exception_status($e);
            //写入日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data
            ]);
        }
        return $status;
    }
}
