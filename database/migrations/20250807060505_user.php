<?php

use think\migration\Migrator;
use think\migration\db\Column;

class User extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->table('user', ['id' => false, 'primary_key' => 'id', 'comment' => '用户表']) //不生成id字段
            ->addColumn(Column::char('id', 32)->setDefault('')->setNull(false)->setComment('用戶id'))
            ->addColumn(Column::char('pid', 32)->setDefault('')->setComment('上级/邀请人id'))
            ->addColumn(Column::char('channel_pid', 32)->setDefault('')->setComment('推广渠道上级id'))
            ->addColumn(Column::char('channel_id', 32)->setDefault('')->setComment('推广渠道id'))
            ->addColumn(Column::tinyInteger('is_office')->setDefault(0)->setComment('是否官方 0=否 1=是'))
            ->addColumn(Column::string('no', 10)->setDefault('')->setComment('会员号(类似qq号展示用)'))
            ->addColumn(Column::string('nickname', 50)->setDefault('')->setComment('昵称'))
            ->addColumn(Column::string('account', 30)->setDefault('')->setComment('账号'))
            ->addColumn(Column::string('password', 30)->setDefault('')->setComment('密码'))
            ->addColumn(Column::tinyInteger('sex')->setDefault(1)->setComment('性别 0=女 1=男'))
            ->addColumn(Column::string('email', 100)->setDefault('')->setComment('邮箱'))
            ->addColumn(Column::string('phone', 20)->setDefault('')->setComment('手機號'))
            ->addColumn(Column::string('avatar', 100)->setDefault('')->setComment('头像'))
            ->addColumn(Column::string('bg_img', 100)->setDefault('')->setComment('背景图'))
            ->addColumn(Column::string('desc')->setDefault('')->setComment('简介'))
            ->addColumn(Column::string('invite_code', 20)->setDefault('')->setComment('邀请码'))
            ->addColumn(Column::integer('login_numbers')->setDefault(0)->setComment('登录数'))
            ->addColumn(Column::string('reg_ip', 15)->setDefault('')->setComment('注册ip'))
            ->addColumn(Column::integer('login_time')->setDefault(0)->setComment('最后登录时间'))
            ->addColumn(Column::string('login_country', 2)->setDefault('')->setComment('最后登录国家'))
            ->addColumn(Column::string('last_ip', 15)->setDefault('')->setComment('最后登录IP'))
            ->addColumn(Column::string('location_name', 64)->setDefault('')->setComment('最后登录地区位置'))
            ->addColumn(Column::tinyInteger('device_type')->setDefault(0)->setComment('使用的设备类型 1安卓 2ios 3ios轻量版(暂不用)'))
            ->addColumn(Column::json('device_info')->setComment('设备信息'))
            ->addColumn(Column::json('app_info')->setComment('应用信息, 客户端当前版本号，格式 x.x.x'))
            ->addColumn(Column::string('did', 64)->setDefault('')->setComment('机器码'))
            ->addColumn(Column::string('last_did', 64)->setDefault('')->setComment('最后登录机器码'))
            ->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('状态：0=禁用 1=启用'))
            ->addColumn(Column::string('language', 10)->setDefault('')->setComment('用戶語言'))
            ->addColumn(Column::integer('create_time')->setDefault(0)->setComment('注册時間'))
            ->addColumn(Column::char('create_user', 32)->setDefault('0')->setComment('創建人'))
            ->addColumn(Column::integer('update_time')->setDefault(0)->setComment('修改時間'))
            ->addColumn(Column::char('update_user', 32)->setDefault('0')->setComment('修改人'))
            ->addIndex(['nickname', 'account'])
            ->create();
    }
}
