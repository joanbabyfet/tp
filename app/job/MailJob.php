<?php

namespace app\job;

use app\service\MailService;
use think\queue\Job;

class MailJob
{
    public function fire(Job $job, $data)
    {
        $to = $data['to'];
        echo "正在处理收件人为 {$to} 的任务\n";
        $status = $this->send($data);
        if($status == 1) {
            echo "收件人为 {$to} 的任务完成\n";
        }

        $job->delete(); //任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        //$job->release(10); //第1种处理方式：重新发布任务,该任务延迟10秒后再执行, 10为延迟时间
    }
    public function failed($data)
    {
        // 任务失败后的处理
        $to = $data['to'];
        echo "收件人为 {$to} 的任务失败\n";
    }

    /**
     * 发送邮件
     * @param $data
     * @return bool|int
     */
    private function send($data)
    {
        $to         = $data['to'];
        $subject    = $data['subject'];
        $body       = $data['body'];
        $mail_service = new MailService();
        $status = $mail_service->send($to, $subject, $body);
        return $status;
    }
}
