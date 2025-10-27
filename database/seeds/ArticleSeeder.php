<?php

use think\migration\Seeder;

class ArticleSeeder extends Seeder
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
        $faker      = Faker\Factory::create('zh_CN'); //选择中文

        $data['data'] = [];
        for ($i = 1; $i <= 100; $i++) {
            $data['data'][] = [
                'type'          => 1,
                'title'         => $faker->paragraph(),     //随机生成1条段落
                'content'       => $faker->text(),          //随机生成1个文本
                'view_count'    => $faker->numberBetween(1000, 9999), //在指定范围内生成随机数
                'status'        => 1,
            ];
        }
        //批量添加
        app('article')->save_all($data);
    }
}