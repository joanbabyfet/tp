<?php

use app\common\service\AdminLoginService;
use app\common\service\ArticleService;
use app\common\service\MailService;
use app\common\service\PushService;
use app\common\service\TgService;
use app\common\service\UserLoginService;
use app\ExceptionHandle;
use app\Request;

// 容器Provider定义文件(容器中所有的对象实例都可以通过容器标识单例调用)
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
    'article'                => ArticleService::class, //article为容器对象标识
    'user_login'             => UserLoginService::class,
    'admin_login'            => AdminLoginService::class,
    'mail'                   => MailService::class,
    'push'                   => PushService::class,
    'tg'                     => TgService::class,
];
