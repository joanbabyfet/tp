<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateSendCodeTable extends Migrator
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
        $this->table('send_code', ['primary_key' => 'id', 'comment' => '发送验证码记录'])
            ->addColumn(Column::tinyInteger('type')->setDefault(1)->setComment('消息类型，1表示短信验证码，2表示邮箱验证码'))
            ->addColumn(Column::string('to', 50)->setDefault('')->setComment('手机号或者邮箱'))
            ->addColumn(Column::text('content')->setDefault('')->setComment('消息内容'))
            ->addColumn(Column::tinyInteger('source')->setDefault(0)->setComment('来源 1=spug 2=unimtx 3=gmail'))
            ->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('发送状态，1=成功 <0=失败'))
            ->addColumn(Column::integer('create_time')->setDefault(0)->setComment('注册時間'))
            ->addColumn(Column::char('create_user', 32)->setDefault('0')->setComment('創建人'))
            ->addIndex(['to'])
            ->create();
    }
}
