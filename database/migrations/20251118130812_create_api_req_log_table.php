<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateApiReqLogTable extends Migrator
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
        $this->table('api_req_log', ['primary_key' => 'id', 'comment' => 'api请求日志'])
            ->addColumn(Column::string('type')->setDefault('')->setComment('类型，api/admin'))
            ->addColumn(Column::char('uid', 32)->setDefault('')->setComment('操作用户uid'))
            ->addColumn(Column::string('ct', 20)->setDefault('')->setComment('控制器'))
            ->addColumn(Column::string('ac', 20)->setDefault('')->setComment('操作'))
            ->addColumn(Column::string('ip', 15)->setDefault('')->setComment('ip'))
            ->addColumn(Column::text('req_data')->setDefault('')->setComment('请求数据，json格式'))
            ->addColumn(Column::text('res_data')->setDefault('')->setComment('响应数据，json格式'))
            ->addColumn(Column::string('error_msg', 1000)->setDefault('')->setComment('错误信息'))
            ->addColumn(Column::integer('req_time')->setDefault(0)->setComment('请求时间'))
            ->addIndex(['uid', 'ct', 'ac'])
            ->create();
    }
}
