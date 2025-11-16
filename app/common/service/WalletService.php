<?php

namespace app\common\service;

use app\common\lib\cls_response;
use app\common\lib\cls_util;
use app\model\TransactionModel;
use app\model\TransferModel;
use app\model\UserModel;
use app\model\WalletModel;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Validate;

class WalletService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new WalletModel();
    }

    /**
     * 充值
     * @param array $data
     * @param $ret_data
     * @return int|mixed
     */
    public function deposit(array $data, &$ret_data=[])
    {
        //参数过滤
        $validate = Validate::rule([
            'user_type' => 'require|string', //用户类型 例 user
            'uid'       => 'require|string',
            'amount'    => 'require|number',
            'confirmed' => 'integer',
            'meta'      => 'array',
        ]);

        // 启动事务
        Db::startTrans();
        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $confirmed  = $data['confirmed'] ?? 1;
            $meta       = empty($data['meta']) ? '[]' : json_encode($data['meta'], JSON_UNESCAPED_UNICODE);
            $uuid       = cls_util::random('uuid');
            $now        = time();

            //初始化钱包
            $wallet_status = $this->balance([
                'uid' => $data['uid'],
                'user_type' => $data['user_type']
            ], $wallet);
            if($wallet_status < 0) {
                $this->exception('初始化钱包失败', -1);
            }

            //写入交易记录
            $balance = $data['amount'] + $wallet['balance'];
            $add = [
                'payable_type'  => $wallet['holder_type'],
                'payable_id'    => $wallet['holder_id'],
                'wallet_id'     => $wallet['id'],
                'type'          => 'deposit',
                'amount'        => $data['amount'],
                'balance'       => $balance,
                'confirmed'     => $confirmed,
                'meta'          => $meta,
                'uuid'          => $uuid,
                'create_time'   => $now,
                'update_time'   => $now,
            ];
            $transactionModel = new TransactionModel();
            $transactionModel->save($add);

            //更新钱包余额
            $update_data = [
                'balance'       => DB::raw("balance + {$data['amount']}"),
                'update_time'   => $now
            ];
            $this->model->where('id', '=', $wallet['id'])->update($update_data);

            // 提交事务
            Db::commit();

            $ret_data = [
                'uid'           => $data['uid'],
                'balance'       => $balance, //余额
                'uuid'          => $uuid, //交易号
            ];
        }
        catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

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
     * 提款
     * @param array $data
     * @param $ret_data
     * @return int|mixed
     */
    public function withdraw(array $data, &$ret_data=[])
    {
        //参数过滤
        $validate = Validate::rule([
            'user_type' => 'require|string', //用户类型 例 user
            'uid'       => 'require|string',
            'amount'    => 'require|number',
            'confirmed' => 'integer',
            'meta'      => 'array',
        ]);

        // 启动事务
        Db::startTrans();
        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $confirmed  = $data['confirmed'] ?? 1;
            $meta       = empty($data['meta']) ? '[]' : json_encode($data['meta'], JSON_UNESCAPED_UNICODE);
            $uuid       = cls_util::random('uuid');
            $now        = time();

            //初始化钱包
            $wallet_status = $this->balance([
                'uid'       => $data['uid'],
                'user_type' => $data['user_type']
            ], $wallet);
            if($wallet_status < 0) {
                $this->exception('初始化钱包失败', -1);
            }

            //检测余额
            if($data['amount'] > $wallet['balance']) {
                $this->exception('余额不足', -2);
            }

            //写入交易记录
            $balance = $data['amount'] * -1 + $wallet['balance'];
            $add = [
                'payable_type'  => $wallet['holder_type'],
                'payable_id'    => $wallet['holder_id'],
                'wallet_id'     => $wallet['id'],
                'type'          => 'withdraw',
                'amount'        => $data['amount'] * -1,
                'balance'       => $balance,
                'confirmed'     => $confirmed,
                'meta'          => $meta,
                'uuid'          => $uuid,
                'create_time'   => $now,
                'update_time'   => $now,
            ];
            $transactionModel = new TransactionModel();
            $transactionModel->save($add);

            //更新钱包余额
            $update_data = [
                'balance'       => DB::raw("balance - {$data['amount']}"),
                'update_time'   => $now
            ];
            $this->model->where('id', '=', $wallet['id'])->update($update_data);

            // 提交事务
            Db::commit();

            $ret_data = [
                'uid'           => $data['uid'],
                'balance'       => $balance, //余额
                'uuid'          => $uuid, //交易号
            ];
        }
        catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

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
     * 转账
     * @param array $data
     * @param $ret_data
     * @return int|mixed
     */
    public function transfer(array $data, &$ret_data=[])
    {
        //参数过滤
        $validate = Validate::rule([
            'user_type'     => 'require|string', //用户类型 例 user
            'uid'           => 'require|string',
            'to_user_type'  => 'require|string', //用户类型 例 user
            'to_uid'        => 'require|string',
            'amount'        => 'require|number',
            'confirmed'     => 'integer',
            'meta'          => 'array',
        ]);

        // 启动事务
        Db::startTrans();
        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $confirmed = $data['confirmed'] ?? 1;
            $meta = empty($data['meta']) ? '[]' : json_encode($data['meta'], JSON_UNESCAPED_UNICODE);
            $now = time();

            //初始化钱包
            $wallet_status = $this->balance([
                'uid'       => $data['uid'],
                'user_type' => $data['user_type']
            ], $wallet);
            if($wallet_status < 0) {
                $this->exception('初始化钱包失败', -1);
            }

            $wallet_status = $this->balance([
                'uid'       => $data['to_uid'],
                'user_type' => $data['to_user_type']
            ], $to_wallet);
            if($wallet_status < 0) {
                $this->exception('初始化钱包失败', -2);
            }

            //检测余额
            if($data['amount'] > $wallet['balance']) {
                $this->exception('余额不足', -3);
            }

            //提现
            $add = [
                'payable_type'  => $wallet['holder_type'],
                'payable_id'    => $wallet['holder_id'],
                'wallet_id'     => $wallet['id'],
                'type'          => 'withdraw',
                'amount'        => $data['amount'] * -1,
                'balance'       => $data['amount'] * -1 + $wallet['balance'],
                'confirmed'     => $confirmed,
                'meta'          => $meta,
                'uuid'          => cls_util::random('uuid'),
                'create_time'   => $now,
                'update_time'   => $now,
            ];
            $transactionModel = new TransactionModel();
            $transactionModel->save($add);
            $withdraw_id = $transactionModel->id; //获取自增id

            //充值
            $add = [
                'payable_type'  => $to_wallet['holder_type'],
                'payable_id'    => $to_wallet['holder_id'],
                'wallet_id'     => $to_wallet['id'],
                'type'          => 'deposit',
                'amount'        => $data['amount'],
                'balance'       => $data['amount'] + $to_wallet['balance'],
                'confirmed'     => $confirmed,
                'meta'          => $meta,
                'uuid'          => cls_util::random('uuid'),
                'create_time'   => $now,
                'update_time'   => $now,
            ];
            $transactionModel = new TransactionModel();
            $transactionModel->save($add);
            $deposit_id = $transactionModel->id; //获取自增id

            //写入转账记录
            $add = [
                'from_type'     => WalletModel::class,
                'from_id'       => $wallet['id'], //钱包id
                'to_type'       => $to_wallet['holder_type'],
                'to_id'         => $data['to_uid'],
                'status'        => 'transfer',
                'deposit_id'    => $deposit_id,
                'withdraw_id'   => $withdraw_id,
                'discount'      => 0,
                'fee'           => 0,
                'uuid'          => cls_util::random('uuid'),
                'create_time'   => $now,
                'update_time'   => $now,
            ];
            $transferModel = new TransferModel();
            $transferModel->save($add);

            //更新钱包余额
            $update_data = [
                'balance'       => DB::raw("balance - {$data['amount']}"),
                'update_time'   => $now
            ];
            $this->model->where('id', '=', $wallet['id'])->update($update_data);

            $update_data = [
                'balance'       => DB::raw("balance + {$data['amount']}"),
                'update_time'   => $now
            ];
            $this->model->where('id', '=', $to_wallet['id'])->update($update_data);

            // 提交事务
            Db::commit();
        }
        catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

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
     * 获取钱包余额
     * @param array $data
     * @param $ret_data
     * @return int|mixed
     */
    public function balance(array $data, &$ret_data=[])
    {
        //参数过滤
        $validate = Validate::rule([
            'user_type'     => 'require|string', //用户类型 例 user
            'uid'           => 'require|string',
            'name'          => 'string',
            'slug'          => 'string',
            'description'   => 'string',
            'meta'          => 'array',
        ]);

        $status = 1;
        try {
            if (!$validate->check($data)) {
                $this->exception(Lang::get('common_param_error'), cls_response::SYS_PARAMS_ERROR);
            }
            $uid            = $data['uid'];
            $name           = $data['name'] ?? 'Default Wallet';
            $slug           = $data['slug'] ?? 'default';
            $description    = $data['description'] ?? '';
            $meta           = empty($data['meta']) ? '[]' : json_encode($data['meta'], JSON_UNESCAPED_UNICODE);
            $uuid           = cls_util::random('uuid');
            $now            = time();

            //获取对象信息
            $class = $this->get_class($data['user_type']);
            $obj = $class::find($uid);
            if (empty($obj)) {
                $this->exception('该对象不存在', -1);
            }

            //获取钱包信息
            $wallet = $this->model->where('holder_id', '=', $obj->id)->find();
            if(empty($wallet)) {
                //初始化钱包
                $add = [
                    'holder_type'       => $class,
                    'holder_id'         => $obj->id,
                    'name'              => $name,
                    'slug'              => $slug,
                    'uuid'              => $uuid,
                    'description'       => $description,
                    'meta'              => $meta,
                    'balance'           => 0,
                    'decimal_places'    => 2,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
                $this->model->save($add);
                $wallet_id = $this->model->id; //获取自增id

                $ret_data = [
                    'id'            => $wallet_id,
                    'holder_type'   => $class,
                    'holder_id'     => $obj->id,
                    'balance'       => 0,
                    'uuid'          => $uuid,
                ];
            }
            else {
                $wallet = $wallet->toArray();
                $ret_data = [
                    'id'            => $wallet['id'],
                    'holder_type'   => $wallet['holder_type'],
                    'holder_id'     => $wallet['holder_id'],
                    'balance'       => (int)$wallet['balance'], //余额
                    'uuid'          => $wallet['uuid'],
                ];
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
     * 获取类文件名包含路径 app\model\WalletModel
     * @param $class_name
     * @return string
     */
    private function get_class($class_name){
        return match($class_name) {
            'user' => UserModel::class,
        };
    }
}
