<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAdminTable extends Migrator
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
        $this->table('admin', ['id' => false, 'primary_key' => 'id', 'comment' => '管理员']) //不生成id字段
            ->addColumn(Column::char('id', 32)->setDefault('')->setNull(false)->setComment('管理员id'))
            ->addColumn(Column::string('groups', 1000)->setDefault('')->setComment('权限组'))
            ->addColumn(Column::string('username', 60)->setDefault('')->setComment('用户名'))
            ->addColumn(Column::string('password', 60)->setDefault('')->setComment('用户密码'))
            ->addColumn(Column::string('realname', 50)->setDefault('')->setComment('真实姓名'))
            ->addColumn(Column::string('email', 50)->setDefault('')->setComment('邮箱'))
            ->addColumn(Column::string('safe_ips', 200)->setDefault('')->setComment('登录ip限制'))
            ->addColumn(Column::tinyInteger('is_first_login')->setDefault(1)->setComment('是否首次登录'))
            ->addColumn(Column::tinyInteger('need_audit')->setDefault(0)->setComment('登陆是否需要后台进行人工审核 0: 不需要 1:需要'))
            ->addColumn(Column::char('session_id', 32)->setDefault('')->setComment('session id'))
            ->addColumn(Column::integer('session_expire')->setDefault(1440)->setComment('SESSION有效期，默认24分钟'))
            ->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('帐号状态 1:正常 0:禁止登陆'))
            ->addColumn(Column::integer('reg_time')->setDefault(0)->setComment('注册時間'))
            ->addColumn(Column::string('reg_ip', 15)->setDefault('')->setComment('注册ip'))
            ->addColumn(Column::integer('login_time')->setDefault(0)->setComment('最后登录时间'))
            ->addColumn(Column::string('login_ip', 15)->setDefault('')->setComment('最后登录ip'))
            ->addIndex(['username', 'status', 'reg_time'])
            ->create();
    }
}
