<?php

namespace app\job;

use app\service\sms\SmsContext;
use app\service\sms\SmsFactory;
use think\queue\Job;

class SmsJob
{
    public function fire(Job $job, $data)
    {
        $phone = $data['phone'];
        echo "正在处理手机号为 {$phone} 的任务\n";
        $status = $this->send($data);
        if($status == 1) {
            echo "手机号为 {$phone} 的任务完成\n";
        }

        $job->delete(); //任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        //$job->release(10); //第1种处理方式：重新发布任务,该任务延迟10秒后再执行, 10为延迟时间
    }
    public function failed($data)
    {
        // 任务失败后的处理
        $phone = $data['phone'];
        echo "手机号为 {$phone} 的任务失败\n";
    }

    /**
     * 发送短信
     * @param $data
     * @return bool|int
     */
    private function send($data)
    {
        $phone  = $data['phone'];
        $code   = $data['code'];
        $strategy = SmsFactory::strategy('unimtx'); //选择策略
        $smsContext = new SmsContext($strategy);
        $status = $smsContext->send($phone, $code);
        return $status;
    }
}
