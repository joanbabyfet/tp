<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserActiveStatTable extends Migrator
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
        $this->table('user_active_stat', ['primary_key' => 'id', 'comment' => '用户活跃数据'])
            ->addColumn(Column::char('date', 10)->setDefault('')->setComment('日期'))
            ->addColumn(Column::char('agent_id', 32)->setDefault('')->setComment('代理id'))
            ->addColumn(Column::string('timezone', 10)->setDefault('')->setComment('统计时区'))
            ->addColumn(Column::integer('user_count')->setDefault(0)->setComment('用户总登入人数'))
            ->addColumn(Column::integer('d1')->setDefault(0)->setComment('次日活跃'))
            ->addColumn(Column::integer('d3')->setDefault(0)->setComment('3日活跃'))
            ->addColumn(Column::integer('d7')->setDefault(0)->setComment('7日活跃'))
            ->addColumn(Column::integer('d14')->setDefault(0)->setComment('14日活跃'))
            ->addColumn(Column::integer('d30')->setDefault(0)->setComment('30日活跃'))
            ->addColumn(Column::integer('create_time')->setDefault(0)->setComment('添加时间'))
            ->addIndex(['date', 'agent_id', 'timezone'], ['unique' => true])
            ->create();
    }
}
