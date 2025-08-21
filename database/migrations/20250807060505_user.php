<?php

use think\migration\Migrator;
use think\migration\db\Column;

class User extends Migrator
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
        $this->table('user', ['id' => false, 'primary_key' => 'id', 'comment' => '用户表']) //不生成id字段
            ->addColumn(Column::char('id', 32)->setDefault('')->setNull(false))
            ->addColumn(Column::char('pid', 32)->setDefault('')->setComment('上级id'))
            ->addColumn(Column::char('channel_pid', 32)->setDefault(''))
            ->addColumn(Column::char('channel_id', 32)->setDefault(''))
            ->addColumn(Column::tinyInteger('is_office')->setDefault(0))
            ->addColumn(Column::string('no', 10)->setDefault(''))
            ->addColumn(Column::string('nickname', 50)->setDefault(''))
            ->addColumn(Column::string('account', 30)->setDefault(''))
            ->addColumn(Column::string('password', 30)->setDefault(''))
            ->addColumn(Column::tinyInteger('sex')->setDefault(1))
            ->addColumn(Column::string('email', 100)->setDefault(''))
            ->addColumn(Column::string('phone', 20)->setDefault(''))
            ->addColumn(Column::string('avatar', 100)->setDefault(''))
            ->addColumn(Column::string('bg_img', 100)->setDefault(''))
            ->addColumn(Column::string('desc')->setDefault(''))
            ->addColumn(Column::string('invite_code', 20)->setDefault(''))
            ->addColumn(Column::integer('login_numbers')->setDefault(0))
            ->addColumn(Column::string('reg_ip', 15)->setDefault(''))
            ->addColumn(Column::integer('login_time')->setDefault(0))
            ->addColumn(Column::string('login_country', 2)->setDefault(''))
            ->addColumn(Column::string('last_ip', 15)->setDefault(''))
            ->addColumn(Column::string('location_name', 64)->setDefault(''))
            ->addColumn(Column::tinyInteger('device_type')->setDefault(0))
            ->addColumn(Column::json('device_info'))
            ->addColumn(Column::json('app_info'))
            ->addColumn(Column::string('did', 64)->setDefault(''))
            ->addColumn(Column::string('last_did', 64)->setDefault(''))
            ->addColumn(Column::tinyInteger('status')->setDefault(1))
            ->addColumn(Column::string('language', 10)->setDefault(''))
            ->addColumn(Column::integer('create_time')->setDefault(0))
            ->addColumn(Column::char('create_user', 32)->setDefault('0'))
            ->addColumn(Column::integer('update_time')->setDefault(0))
            ->addColumn(Column::char('update_user', 32)->setDefault('0'))
            ->addIndex(['nickname', 'account'])
            ->create();
    }
}
