<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserTable extends Migrator
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
    {$this->table('user', ['id' => false, 'primary_key' => 'id', 'comment' => '用户']) //不生成id字段
        ->addColumn(Column::char('id', 32)->setDefault('')->setNull(false)->setComment('用户id'))
        ->addColumn(Column::integer('short_id')->setDefault(0)->setComment('短id'))
        ->addColumn(Column::char('agent_id', 32)->setDefault('')->setComment('代理id'))
        ->addColumn(Column::string('avatar', 200)->setDefault('')->setComment('头像'))
        ->addColumn(Column::string('username', 60)->setDefault('')->setComment('用户名'))
        ->addColumn(Column::string('password', 60)->setDefault('')->setComment('用户密码'))
        ->addColumn(Column::string('nickname', 80)->setDefault('')->setComment('昵称'))
        ->addColumn(Column::tinyInteger('sex')->setDefault(1)->setComment('性别 0=女 1=男'))
        ->addColumn(Column::string('currency_code', 10)->setDefault('CNY')->setComment('币种'))
        ->addColumn(Column::integer('country_id')->setDefault(0)->setComment('所在国家(运营国家)'))
        ->addColumn(Column::tinyInteger('is_visitor')->setDefault(0)->setComment('是否游客'))
        ->addColumn(Column::string('phone', 20)->setDefault('')->setComment('电话'))
        ->addColumn(Column::string('email', 50)->setDefault('')->setComment('邮箱'))
        ->addColumn(Column::char('session_id', 32)->setDefault('')->setComment('session id'))
        ->addColumn(Column::integer('session_expire')->setDefault(1440)->setComment('SESSION有效期，默认24分钟'))
        ->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('帐号状态 1:正常 0:禁止登陆'))
        ->addColumn(Column::integer('reg_time')->setDefault(0)->setComment('注册時間'))
        ->addColumn(Column::string('reg_ip', 15)->setDefault('')->setComment('注册ip'))
        ->addColumn(Column::integer('login_time')->setDefault(0)->setComment('最后登录时间'))
        ->addColumn(Column::string('login_ip', 15)->setDefault('')->setComment('最后登录ip'))
        ->addColumn(Column::tinyInteger('change_username')->setDefault(0)->setComment('是否修改用户名，1是已修改过'))
        ->addIndex(['nickname'])
        ->create();
    }
}
