<?php

namespace app;

use think\worker\Server;

class worker extends Server
{
    protected $socket = 'websocket://127.0.0.1:8000';

    public $con;

    public function onConnect($connection)
    {
        $connection->send('链接成功！');
    }

    public function onMessage($connection,$data)
    {

        $connection->send("\n".'服务器接收成功!'."\n");
        $this->con = $connection;
        $this->qianwen($data);
    }

    public function onClose($connection)
    {

    }

    public function onError($connection,$code,$msg)
    {
        echo 'error' . $code  . $msg;

    }

    public function onWorkerStart($worker)
    {

    }

    public function qianwen($question='长恨歌')
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');
        set_time_limit(0);

        $openai_api_key = 'sk-6666666666666666666666666666';//子空间key
        $url = 'https://dashscope.aliyuncs.com/api/v1/services/aigc/text-generation/generation';
        $headers = [
            'Authorization: Bearer ' . $openai_api_key,
            'Content-Type: application/json',
            'X-DashScope-SSE: enable'
        ];
        $params =  [
            'model' => 'farui-plus',
            'input' => ['messages' => []],
            'parameters' => [
                'result_format'=>'message',
                'incremental_output'=>true
            ],
        ];
        $params['input']['messages'][] = [
            'role' => 'user',
            'content' => $question
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//有时候希望返回的内容作为变量储存，而不是直接输出。这个时候就必需设置curl的CURLOPT_RETURNTRANSFER选项为1或true。
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);//设置这个选项为一个非零值(象 “Location: “)的头，服务器会把它当做HTTP头的一部分发送(注意这是递归的，PHP将发送形如 “Location: “的头)。
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//curl_exec()获取的信息以文件流的形式返回，而不是直接输出
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
            $res = substr(explode("\n",$data)[3],5);
            $res = json_decode($res,true);
            if($res['output']){
                $this->dayin($res['output']['choices'][0]['message']['content']);
            }else{
                echo $res['message'];
            }
            return strlen($data);
        });
        curl_exec($ch);
        curl_close($ch);

    }

    public function dayin($msg){
        $this->con->send($msg);
    }
}