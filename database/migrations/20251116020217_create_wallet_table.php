<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateWalletTable extends Migrator
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
        $this->table('wallet', ['primary_key' => 'id', 'comment' => '钱包'])
            ->addColumn(Column::string('holder_type')->setDefault('')->setComment('类型'))
            ->addColumn(Column::char('holder_id', 32)->setDefault('')->setComment('用户id'))
            ->addColumn(Column::string('name')->setDefault('')->setComment('名称'))
            ->addColumn(Column::string('slug')->setDefault('')->setComment(''))
            ->addColumn(Column::char('uuid', 36)->setDefault('')->setComment('标识'))
            ->addColumn(Column::string('description', 255)->setDefault('')->setComment('说明'))
            ->addColumn(Column::json('meta', 255)->setComment('扩展'))
            ->addColumn(Column::decimal('balance', 64, 0)->setDefault(0)->setComment('余额'))
            ->addColumn(Column::smallInteger('decimal_places')->setDefault(2)->setComment(''))
            ->addColumn(Column::integer('update_time')->setDefault(0)->setComment('修改時間'))
            ->addColumn(Column::integer('create_time')->setDefault(0)->setComment('添加时间'))
            ->addIndex(['holder_type', 'holder_id', 'slug'], ['unique' => true])
            ->addIndex(['uuid'], ['unique' => true])
            ->create();
    }
}
