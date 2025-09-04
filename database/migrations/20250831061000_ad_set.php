<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AdSet extends Migrator
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
        $this->table('ad_set', ['primary_key' => 'id', 'comment' => '广告设置']) //不生成id字段
            ->addColumn(Column::string('name', 50)->setDefault('')->setComment('名称'))
            ->addColumn(Column::string('flag', 50)->setDefault('')->setComment('唯一标识'))
            ->addColumn(Column::string('position', 50)->setDefault('0')->setComment('随机出现位置'))
            ->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('状态：0=禁用 1=启用'))
            ->addColumn(Column::integer('create_time')->setDefault(0)->setComment('注册時間'))
            ->addColumn(Column::char('create_user', 32)->setDefault('0')->setComment('創建人'))
            ->addColumn(Column::integer('update_time')->setDefault(0)->setComment('修改時間'))
            ->addColumn(Column::char('update_user', 32)->setDefault('0')->setComment('修改人'))
            ->addColumn(Column::integer('delete_time')->setDefault(0)->setComment('删除時間'))
            ->addColumn(Column::char('delete_user', 32)->setDefault('0')->setComment('删除人'))
            ->addIndex(['name', 'flag', 'create_time'])
            ->create();
    }
}
