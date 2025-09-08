<?php
declare (strict_types = 1);

namespace app\command;

use app\model\AdModel;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class ExampleCommand extends Command
{
    protected function configure()
    {
        //命令名称及用途描述
        $this->setName('example:command')
            ->setDescription('example command for a schedule task');
    }

    protected function execute(Input $input, Output $output)
    {
        $data = [
            'title' => '我是广告11'
        ];
        $ad_model = new AdModel;
        $ad_model->save($data, ['id' => 1]);

        //业务
        $output->writeln('任务执行完成');
    }
}
