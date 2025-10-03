<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateConfigTable extends Migrator
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
        $this->table('config', ['id' => false, 'primary_key' => 'name', 'comment' => '系统配置变量表']) //不生成id字段
            ->addColumn(Column::string('type')->setDefault('string')->setComment('变量类型, 例 string'))
            ->addColumn(Column::string('name', 100)->setDefault('')->setNull(false)->setComment('变量名'))
            ->addColumn(Column::text('value')->setDefault('')->setComment('变量值'))
            ->addColumn(Column::string('title', 50)->setDefault('')->setComment('说明'))
            ->addColumn(Column::string('info', 200)->setDefault('')->setComment('备注'))
            ->addColumn(Column::smallInteger('group')->setDefault(1)->setComment('分组'))
            ->addColumn(Column::smallInteger('sort')->setDefault(0)->setComment('排序'))
            ->addIndex(['sort'])
            ->create();
    }
}
