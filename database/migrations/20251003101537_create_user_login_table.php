<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserLoginTable extends Migrator
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
        $this->table('user_login', ['primary_key' => 'id', 'comment' => '用户登陆记录表']) //不生成id字段
            ->addColumn(Column::char('uid', 32)->setDefault('')->setComment('用户id'))
            ->addColumn(Column::string('username', 60)->setDefault('')->setComment('用户名'))
            ->addColumn(Column::char('session_id', 32)->setDefault('')->setComment('session id'))
            ->addColumn(Column::string('agent', 500)->setDefault('')->setComment('浏览器信息'))
            ->addColumn(Column::integer('login_time')->setDefault(0)->setComment('发录时间'))
            ->addColumn(Column::string('login_ip', 15)->setDefault('')->setComment('登录ip'))
            ->addColumn(Column::char('login_country', 2)->setDefault('')->setComment('登录国家'))
            ->addColumn(Column::tinyInteger('login_status')->setDefault(0)->setComment('登录时状态 1=成功，0=失败'))
            ->addColumn(Column::string('cli_hash', 32)->setDefault('')->setComment('用户登录名和ip的hash'))
            ->addIndex(['login_time', 'cli_hash', 'login_status'])
            ->create();
    }
}
