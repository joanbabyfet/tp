<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAdminOplogTable extends Migrator
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
        $this->table('admin_oplog', ['primary_key' => 'id', 'comment' => '管理员操作日志']) //不生成id字段
            ->addColumn(Column::char('uid', 32)->setDefault('')->setComment('用户id'))
            ->addColumn(Column::string('username', 60)->setDefault('')->setComment('用户名'))
            ->addColumn(Column::char('session_id', 32)->setDefault('')->setComment('session id'))
            ->addColumn(Column::string('msg', 250)->setDefault('')->setComment('消息内容'))
            ->addColumn(Column::integer('op_time')->setDefault(0)->setComment('操作时间'))
            ->addColumn(Column::string('op_ip', 15)->setDefault('')->setComment('操作ip'))
            ->addColumn(Column::char('op_country', 2)->setDefault('')->setComment('操作国家'))
            ->addColumn(Column::string('op_url', 100)->setDefault('')->setComment('操作地址'))
            ->addIndex(['username', 'op_time'])
            ->create();
    }
}
