<?php

use think\facade\Session;
use think\migration\Seeder;

class UserLoginSeeder extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run() : void
    {
        $uid        = '1';
        $username   = 'admin';
        $faker      = Faker\Factory::create('zh_CN'); //选择中文

        $data['data'] = [];
        for ($i = 1; $i <= 100; $i++) {
            $login_ip   = $faker->ipv4;
            $cli_hash   = md5($username.'-'.$login_ip);

            $data['data'][] = [
                'uid'           => $uid,
                'username'      => $username,
                'session_id'    => Session::getId(), //web场景使用
                'agent'         => $faker->userAgent,
                'login_time'    => $faker->unixTime,
                'login_ip'      => $login_ip,
                'login_country' => $faker->countryCode,
                'login_status'  => 1,   //登录时状态 1=成功，0=失败
                'cli_hash'      => $cli_hash, //用户登录名和ip的hash
            ];
        }
        //批量添加
        app('user_login')->save_all($data);
    }
}