<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTransferTable extends Migrator
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
        $this->table('transfer', ['primary_key' => 'id', 'comment' => '钱包转账记录']) //不生成id字段
            ->addColumn(Column::integer('from_id')->setDefault(0)->setComment(''))
            ->addColumn(Column::string('from_type')->setDefault('')->setComment(''))
            ->addColumn(Column::char('to_id', 32)->setDefault('')->setComment(''))
            ->addColumn(Column::string('to_type')->setDefault('')->setComment(''))
            ->addColumn(Column::char('status', 10)->setDefault('transfer')->setComment(''))
            ->addColumn(Column::char('status_last', 10)->setDefault('')->setComment(''))
            ->addColumn(Column::integer('deposit_id')->setDefault(0)->setComment(''))
            ->addColumn(Column::integer('withdraw_id')->setDefault(0)->setComment(''))
            ->addColumn(Column::decimal('discount', 64, 0)->setDefault(0)->setComment(''))
            ->addColumn(Column::decimal('fee', 64, 0)->setDefault(0)->setComment(''))
            ->addColumn(Column::char('uuid', 36)->setDefault('')->setComment('标识'))
            ->addColumn(Column::integer('update_time')->setDefault(0)->setComment('修改時間'))
            ->addColumn(Column::integer('create_time')->setDefault(0)->setComment('添加时间'))
            ->addIndex(['deposit_id', 'withdraw_id'])
            ->addIndex(['uuid'], ['unique' => true])
            ->create();
    }
}
