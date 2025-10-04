<?php
declare (strict_types = 1);

namespace app\command;

use app\model\AdModel;
use think\console\Command;
use think\console\Input;
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
        $this->service->edit($data);

        $data = [
            'title' => '我是标题'
        ];
        $ad_model = new AdModel;
        $ad_model->save($data, ['id' => 1]);

        //业务
        $output->writeln('任务执行完成');
    }
}
