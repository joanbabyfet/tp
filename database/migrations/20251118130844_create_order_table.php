<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateOrderTable extends Migrator
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
        $this->table('order', ['primary_key' => 'id', 'comment' => '订单'])
            ->addColumn(Column::char('order_sn', 30)->setDefault('')->setComment('订单编号(展示)'))
            ->addColumn(Column::tinyInteger('order_type')->setDefault(0)->setComment('订单类型：1.普通订单'))
            ->addColumn(Column::char('agent_pid', 32)->setDefault('')->setComment('代理上级id'))
            ->addColumn(Column::char('agent_id', 32)->setDefault('')->setComment('代理id'))
            ->addColumn(Column::tinyInteger('user_type')->setDefault(0)->setComment('用户类型 1=老用户 0=新用户'))
            ->addColumn(Column::char('uid', 32)->setDefault('')->setComment('用户id'))
            ->addColumn(Column::decimal('actual_amount', 16, 4)->setDefault(0)->setComment('订单实际支付金额，保留4位小数'))
            ->addColumn(Column::decimal('amount', 16, 4)->setDefault(0)->setComment('订单金额，保留4位小数'))
            ->addColumn(Column::string('currency_code', 10)->setDefault('CNY')->setComment('币种'))
            ->addColumn(Column::tinyInteger('device_system')->setDefault(0)->setComment('使用的设备类型 1安卓 2ios 3ios轻量版(暂不用)'))
            ->addColumn(Column::string('pay_channel_code', 50)->setDefault('')->setComment('支付渠道码'))
            ->addColumn(Column::tinyInteger('pay_type')->setDefault(0)->setComment('支付方式 1支付宝 2微信'))
            ->addColumn(Column::tinyInteger('pay_status')->setDefault(0)->setComment('支付状态，0=等待支付，1=支付成功，-1支付失败，-2=验证不通过'))
            ->addColumn(Column::integer('pay_time')->setDefault(0)->setComment('订单支付时间'))
            ->addColumn(Column::char('trade_id', 32)->setDefault('')->setComment('支付平台交易号'))
            ->addColumn(Column::string('ip', 15)->setDefault('')->setComment('下单时ip'))
            ->addColumn(Column::string('user_agent', 500)->setDefault('')->setComment('下单时浏览器'))
            ->addColumn(Column::tinyInteger('status')->setDefault(0)->setComment('订单状态 0未付款 1已完成 -1未拉起'))
            ->addColumn(Column::json('extra_info')->setComment('扩展'))
            ->addColumn(Column::integer('create_time')->setDefault(0)->setComment('添加时间'))
            ->addColumn(Column::integer('update_time')->setDefault(0)->setComment('修改時間'))
            ->addIndex(['order_sn'], ['unique' => true])
            ->create();
    }
}
