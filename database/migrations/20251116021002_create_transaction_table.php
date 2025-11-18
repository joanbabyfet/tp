<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTransactionTable extends Migrator
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
        $this->table('transaction', ['primary_key' => 'id', 'comment' => '钱包交易日志'])
            ->addColumn(Column::string('payable_type')->setDefault('')->setComment(''))
            ->addColumn(Column::char('payable_id', 32)->setDefault('')->setComment('用户id'))
            ->addColumn(Column::integer('wallet_id')->setDefault(0)->setComment('钱包id'))
            ->addColumn(Column::char('type', 10)->setDefault('')->setComment('deposit=充值 withdraw=提现'))
            ->addColumn(Column::decimal('amount', 64, 0)->setDefault(0)->setComment('金额'))
            ->addColumn(Column::decimal('balance_before', 12, 2)->setDefault(0)->setComment('交易前余额'))
            ->addColumn(Column::decimal('balance_after', 12, 2)->setDefault(0)->setComment('交易后余额'))
            ->addColumn(Column::tinyInteger('confirmed')->setDefault(1)->setComment(''))
            ->addColumn(Column::json('meta', 255)->setComment('扩展'))
            ->addColumn(Column::char('uuid', 36)->setDefault('')->setComment('标识'))
            ->addColumn(Column::integer('update_time')->setDefault(0)->setComment('修改時間'))
            ->addColumn(Column::integer('create_time')->setDefault(0)->setComment('添加时间'))
            ->addIndex(['uuid'], ['unique' => true])
            ->create();
    }
}
