<?php

namespace app\job;

use think\queue\Job;

class UserLoginJob
{
    public function fire(Job $job, $data)
    {
        $uid            = $data['uid'];
        echo "正在处理用户为 {$uid} 的任务\n";
        $status = $this->login_log($data);
        if($status == 1) {
            echo "用户为 {$uid} 的任务完成\n";
        }

        $job->delete(); //任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        //$job->release(10); //第1种处理方式：重新发布任务,该任务延迟10秒后再执行, 10为延迟时间
    }
    public function failed($data)
    {
        // 任务失败后的处理
        $uid = $data['uid'];
        echo "用户为 {$uid} 的任务失败\n";
    }

    /**
     * 写入登录日志
     * @param $data
     * @return bool|int
     */
    private function login_log($data)
    {
        //写入登录失败日志
        $status = app('user_login')->save($data);
        return $status;
    }
}
