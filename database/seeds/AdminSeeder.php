<?php

use app\common\lib\cls_util;
use think\migration\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run(): void
    {
        $data = [
            'id'        => '1',
            'username'  => 'admin',
            'password'  => cls_util::get_password('Bb123456'),
            'realname'  => '超级管理员',
            'email'     => 'admin@example.com',
            'status'    => 1,
            'reg_time'  => time(),
            'reg_ip'    => '127.0.0.1',
        ];
        //添加
        $this->table('admin')->insert($data)->save();
    }
}