<?php
// 事件定义文件
return [
    'bind'      => [
        'AdminOplogEvent' => 'app\event\AdminOplogEvent',
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        //注册监听类
        'AdminOplogEvent' => ['app\listener\AdminOplogListener'],
    ],

    'subscribe' => [
    ],
];
