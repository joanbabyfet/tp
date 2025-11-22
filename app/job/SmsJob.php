<?php

namespace app\job;

use app\common\service\sms\SmsContext;
use app\common\service\sms\SmsFactory;
use app\event\SendCodeEvent;
use app\model\SendCodeModel;
use think\queue\Job;

class SmsJob
{
    public function fire(Job $job, $data)
    {
        $phone = $data['phone'];
        echo "正在处理手机号为 {$phone} 的任务\n";
        $status = $this->send($data);
        if($status == 1) {
            $job->delete(); //任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
            //$job->release(10); //第1种处理方式：重新发布任务,该任务延迟10秒后再执行, 10为延迟时间
            echo "手机号为 {$phone} 的任务完成\n";
        }
        else {
            //重试超过3次
            if ($job->attempts() > 3) {
                $job->delete();
            }
        }
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
        $type = $data['type'] ?? 'unimtx';
        $strategy = SmsFactory::strategy($type); //选择策略
        $smsContext = new SmsContext($strategy);
        $status = $smsContext->send($data);

        //写入发送验证码日志(通过队列来写)
        $log_data = [
            'to'        => $data['phone'],
            'content'   => '您的验证码是'.$data['code'].'，10分钟内有效，请勿泄露。',
            'type'      => 1, //消息类型，1表示短信验证码，2表示邮箱验证码
            'source'    => SendCodeModel::SOURCE_MAP[$type], //来源 1=spug 2=unimtx 3=gmail
            'status'    => $status,
        ];
        event(new SendCodeEvent($log_data));

        return $status;
    }
}
