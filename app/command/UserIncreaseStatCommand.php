<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;

/**
 * 配置示例
 * crontab -e
 * 0 3 * * * /usr/bin/php /var/www/tp/think abtest:stat >> /var/log/abtest.log 2>&1
 */
class UserIncreaseStatCommand extends Command
{
    public const SUCCESS = 0;
    public const FAILURE = -1;

    protected function configure()
    {
        //命令名称及用途描述
        $this->setName('gen:user_increase_stat')
            ->setDescription('生成用户增长数据')
            ->addArgument('from_date');
    }

    protected function execute(Input $input, Output $output)
    {
        //关闭超时 & 提升内存
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $redis = Cache::store('redis')->handler();
        $lock_key = 'lock:user_increase_stat';
        $ttl = 3600; // 必须 >= 任务最大执行时间

        //原子抢锁
        $locked = $redis->set($lock_key, time(), ['nx', 'ex' => $ttl]);
        if (!$locked) {
            $output->warning('任务正在执行中，已跳过');
            return self::SUCCESS;
        }
        //设置过期时间（防死锁）
        //$redis->expire($lock_key, $ttl);

        try {
            //获取日期 例 2025/11/10
            $from_date = $input->getArgument('from_date') ?? date('Y/m/d', strtotime('-1 day'));
            $data = [
                'from_date' => $from_date,
            ];
            $status = app('user_increase_stat')->generate_data($data);
            if($status < 0) {
                throw new \RuntimeException('任务执行失败');
            }
            $output->writeln('任务执行成功');
            return self::SUCCESS;
        }
        catch (\Throwable $e) {
            $output->error($e->getMessage());
            trace($e->getMessage(), 'error');
            return self::FAILURE;
        }
        finally {
            // 释放锁
            $redis->del($lock_key);
        }
    }
}
