<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateArticleTable extends Migrator
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
        $this->table('article', ['primary_key' => 'id', 'comment' => '文章']) //不生成id字段
            ->addColumn(Column::tinyInteger('type')->setDefault(0)->setComment('类型，1=精彩瞬间'))
            ->addColumn(Column::string('title', 50)->setDefault('')->setComment('标题'))
            ->addColumn(Column::text('content')->setDefault('')->setComment('内容'))
            ->addColumn(Column::string('pic', 255)->setDefault('')->setComment('图片'))
            ->addColumn(Column::integer('view_count')->setDefault(0)->setComment('点击量/阅读数'))
            ->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('状态：0=禁用 1=启用'))
            ->addColumn(Column::integer('publish_time')->setDefault(0)->setComment('发布时间'))
            ->addColumn(Column::char('publish_user', 32)->setDefault('')->setComment('发布人'))
            ->addColumn(Column::integer('create_time')->setDefault(0)->setComment('注册時間'))
            ->addColumn(Column::char('create_user', 32)->setDefault('0')->setComment('創建人'))
            ->addColumn(Column::integer('update_time')->setDefault(0)->setComment('修改時間'))
            ->addColumn(Column::char('update_user', 32)->setDefault('0')->setComment('修改人'))
            ->addColumn(Column::integer('delete_time')->setDefault(0)->setComment('删除時間'))
            ->addColumn(Column::char('delete_user', 32)->setDefault('0')->setComment('删除人'))
            ->addIndex(['type', 'title', 'status', 'publish_time', 'create_time'])
            ->create();
    }
}
