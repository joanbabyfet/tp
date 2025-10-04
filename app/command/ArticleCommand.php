<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class ArticleCommand extends Command
{
    protected function configure()
    {
        //命令名称及用途描述
        $this->setName('add:article')
            ->setDescription('example command for a schedule task');
    }

    protected function execute(Input $input, Output $output)
    {
        $data = [
            'title'     => '我是标题',
            'content'   => '我是内容',
            'status'    => 1,
        ];
        $status = app('article')->edit($data);
        if($status < 0) {
            $output->error('任务执行失败');
        }
        $output->writeln('任务执行成功');
    }
}
