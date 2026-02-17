<?php

namespace app\common\service;

use app\common\lib\cls_auth;
use app\common\lib\cls_response;
use app\common\lib\cls_util;
use app\job\UserLoginJob;
use app\model\UserModel;
use think\facade\Cache;
use think\facade\Lang;
use think\facade\Queue;
use think\facade\Session;
use think\facade\Validate;

class UserService extends BaseService
{
    protected $model;
    protected $userLoginService;
    public function __construct()
    {
        $this->model = new UserModel();
        $this->userLoginService = new UserLoginService();
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
            'id'            => 'string',
            'username'      => 'require|string',    //用户名, 唯一标识
            'password'      => 'require|string',
            'nickname'      => 'require|string',
            'sex'           => 'require|integer',
            'phone'         => 'require|string',
            'email'         => 'require|string',
            'currency_code' => 'string',
            'country_id'    => 'integer',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $id             = $data['id'] ?? '';
            $country_id     = $data['country_id'] ?? 0;
            $currency_code  = $data['currency_code'] ?? 'CNY';
            $username       = $data['username'];

            //组装数据
            $save_data = [
                'username'      => $username,
                'password'      => cls_util::get_password($data['password']),
                'nickname'      => $data['nickname'],
                'sex'           => $data['sex'],
                'phone'         => $data['phone'],
                'email'         => $data['email'],
                'currency_code' => $currency_code,
                'country_id'    => $country_id,
            ];

            if($id)
            {
                //更新
                $up = array_merge($save_data, []);
                $this->model->where('id', '=', $id)->update($up);
            }
            else
            {
                //检测账号是否存在
                $user = $this->model->where('username', '=', $username)->find();
                if(!empty($user)) {
                    $this->exception(Lang::get('common_account_has_register'), -1);
                }

                //添加
                $id = cls_util::random();
                $add = array_merge($save_data, [
                    'id'         => $id,
                    'reg_time'   => time(),
                    'reg_ip'     => request()->ip()
                ]);
                $this->model->save($add);

                $ret_data = $id;
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
     * 登录
     * @param $data
     * @param $ret_data
     * @return int|mixed
     */
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

            //先检测验证码(1次性)
//            if(!captcha_check($verify_code)) {
//                $this->exception(Lang::get('common_verify_code_error'), -1);
//            }

            $where = [
                'username' => $username
            ];
            $user = $this->model->where($where)->find();
            if(empty($user)) {
                //通过队列写入登录失败日志
                $log = [
                    'uid'           => '0',
                    'username'      => $username,
                    'login_ip'      => $login_ip,
                    'agent'         => $agent,
                    'login_status'  => 0,
                ];
                $is_push = Queue::push(UserLoginJob::class, $log, $queue = null);

                $this->exception("用户名或密码无效", -2);
            }
            $original_password = $user->password;
            $user = $user->hidden(['password'])->toArray(); //隐藏敏感信息字段

            if($user['status'] == 0) {
                $this->exception("用户禁用", -3);
            }

            if(cls_util::get_password($password) != $original_password) {
                //通过队列写入登录失败日志
                $log = [
                    'uid'           => $user['id'],
                    'username'      => $user['username'],
                    'login_ip'      => $login_ip,
                    'agent'         => $agent,
                    'login_status'  => 0,
                ];
                $is_push = Queue::push(UserLoginJob::class, $log, $queue = null);

                $this->exception("用户名或密码无效", -4);
            }

            //更新登录信息
            $up = [
                'session_id'    => Session::getId(), //web场景使用
                'login_time'    => time(),
                'login_ip'      => request()->ip(),
            ];
            $this->model->where('id', '=', $user['id'])->update($up);

            //通过队列写入登录成功日志, database 驱动时，返回值为 自增id|false  ;   redis 驱动时，返回值为 随机字符串|false
            $log = [
                'uid'           => $user['id'],
                'username'      => $user['username'],
                'login_ip'      => $login_ip,
                'agent'         => $agent,
                'login_status'  => 1,
            ];
            $is_push = Queue::push(UserLoginJob::class, $log, $queue = null);

            $ret_data = array_merge($user, [
                'token' => cls_auth::create_token($user['id'])
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

    /**
     * 获取详情
     * @param array $data
     * @param $ret_data
     * @return int|mixed
     */
    public function detail(array $data, &$ret_data = [])
    {
        //参数过滤
        $validate = Validate::rule([
            'id' => 'require',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $id = $data['id'];

            //获取单条
            $res = $this->model->find($id);
            $info = empty($res) ? [] : $res->hidden(['password'])->toArray(); //隐藏敏感信息字段

            // 数据格式化(预留扩展方法,可不用)
            if (method_exists($this->model, 'formatInfo')) {
                $info = $this->model->formatInfo($info);
            }
            $ret_data = $info;
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
            $id                 = request()->auth;

            //获取用户信息
            $user = $this->model->find($id);
            if(empty($user)) {
                $this->exception('该用户不存在', -1);
            }
            $user = $user->toArray();

            //两次密码不一致
            if($new_password != $re_new_password) {
                $this->exception(Lang::get('common_pwd_and_check_pwd_different_invalid'), -2);
            }

            //原密码错误
            if(cls_util::get_password($password) != $user['password']) {
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

    /**
     * 忘记密码-重置密码
     * @param array $data
     * @return int|mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function forgot_pwd(array $data)
    {
        //参数过滤
        $validate = Validate::rule([
            'phone'             => 'require|string',
            'phone_area_code'   => 'require|string',
            'sms_verify_code'   => 'require|string',
            'password'          => 'require|string', //新密码
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $phone              = $data['phone'];
            $phone_area_code    = $data['phone_area_code'];
            $password           = $data['password'];

            //获取用户信息(读主库)
            $user = $this->model::master()->where('phone', '=', $phone_area_code.$phone)->find();
            if(empty($user)) {
                $this->exception('该用户不存在', -3);
            }
            $user = $user->toArray();

            //更新
            $up = [
                'password' => cls_util::get_password($password),
            ];
            $this->model->where('id', '=', $user['id'])->update($up);
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
