<?php

namespace app\job;

use think\queue\Job;

class ApiReqLogJob
{
    public function fire(Job $job, $data)
    {
        echo "正在处理写入api请求日志的任务\n";
        $status = $this->login_log($data);
        if($status == 1) {
            $job->delete(); //任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
            //$job->release(10); //第1种处理方式：重新发布任务,该任务延迟10秒后再执行, 10为延迟时间
            echo "写入api请求日志的任务完成\n";
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
        echo "写入api请求日志的任务失败\n";
    }

    /**
     * 写入api请求日志
     * @param $data
     * @return bool|int
     */
    private function login_log($data)
    {
        $status = app('api_req_log')->save($data);
        return $status;
    }
}
