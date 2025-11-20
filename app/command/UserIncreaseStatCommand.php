<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class UserIncreaseStatCommand extends Command
{
    protected function configure()
    {
        //命令名称及用途描述
        $this->setName('user_increase_stat:gen')
            ->setDescription('生成用户增长数据')
            ->addArgument('from_date');
    }

    protected function execute(Input $input, Output $output)
    {
        //获取日期 例 2025/11/10
        $from_date = $input->getArgument('from_date') ?? date('Y/m/d', strtotime('-1 day'));
        $data = [
            'from_date' => $from_date,
        ];
        $status = app('user_increase_stat')->generate_data($data);
        if($status < 0) {
            $output->error('任务执行失败');
        }
        $output->writeln('任务执行成功');
    }
}
