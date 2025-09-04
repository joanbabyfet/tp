<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Ad extends Migrator
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
        $this->table('ad', ['primary_key' => 'id', 'comment' => '广告表']) //不生成id字段
        ->addColumn(Column::integer('ad_set_id')->setDefault(0)->setComment('广告设置ID'))
            ->addColumn(Column::tinyInteger('type')->setDefault(0)->setComment('广告类型 1=视频,2=图片'))
            ->addColumn(Column::string('title', 50)->setDefault('')->setComment('标题'))
            ->addColumn(Column::string('img', 255)->setDefault('')->setComment('广告图'))
            ->addColumn(Column::string('video_url', 255)->setDefault('')->setComment('播放地址'))
            ->addColumn(Column::integer('video_id')->setDefault(0)->setComment('视频id'))
            ->addColumn(Column::tinyInteger('jump_type')->setDefault(0)->setComment('链接跳转类型 0=外部跳转链接,1=签到活动,2=会员金币,3=购买观影卷,4=购买会员卡,5=视频详情,6=抽奖活动,7=社区分类,8=社区详情'))
            ->addColumn(Column::string('url', 255)->setDefault('')->setComment('跳转链接地址'))
            ->addColumn(Column::tinyInteger('position')->setDefault(0)->setComment('随机出现位置：0=否 1=是'))
            ->addColumn(Column::tinyInteger('action_type')->setDefault(0)->setComment('操作 0=无操作,1=打开链接'))
            ->addColumn(Column::integer('start_time')->setDefault(0)->setComment('开始时间'))
            ->addColumn(Column::integer('end_time')->setDefault(0)->setComment('结束时间'))
            ->addColumn(Column::integer('weight')->setDefault(0)->setComment('权重'))
            ->addColumn(Column::integer('sort')->setDefault(0)->setComment('排序'))
            ->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('状态：0=禁用 1=启用'))
            ->addColumn(Column::integer('create_time')->setDefault(0)->setComment('注册時間'))
            ->addColumn(Column::char('create_user', 32)->setDefault('0')->setComment('創建人'))
            ->addColumn(Column::integer('update_time')->setDefault(0)->setComment('修改時間'))
            ->addColumn(Column::char('update_user', 32)->setDefault('0')->setComment('修改人'))
            ->addColumn(Column::integer('delete_time')->setDefault(0)->setComment('删除時間'))
            ->addColumn(Column::char('delete_user', 32)->setDefault('0')->setComment('删除人'))
            ->addIndex(['title', 'create_time', 'sort'])
            ->create();
    }
}
