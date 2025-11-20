<?php

use app\common\lib\cls_paginator;
use app\common\service\AdminLoginService;
use app\common\service\AdminOplogService;
use app\common\service\ApiReqLogService;
use app\common\service\ArticleService;
use app\common\service\MailService;
use app\common\service\PushService;
use app\common\service\SendCodeService;
use app\common\service\TgService;
use app\common\service\UserIncreaseStatService;
use app\common\service\UserLoginService;
use app\common\service\WalletService;
use app\ExceptionHandle;
use app\Request;

// 容器Provider定义文件(容器中所有的对象实例都可以通过容器标识单例调用)
return [
    'think\Request'             => Request::class,
    'think\exception\Handle'    => ExceptionHandle::class,
    'article'                   => ArticleService::class, //article为容器对象标识
    'user_login'                => UserLoginService::class,
    'admin_login'               => AdminLoginService::class,
    'admin_oplog'               => AdminOplogService::class,
    'mail'                      => MailService::class,
    'push'                      => PushService::class,
    'tg'                        => TgService::class,
    'send_code'                 => SendCodeService::class,
    'wallet'                    => WalletService::class,
    'api_req_log'               => ApiReqLogService::class,
    'think\Paginator'           => cls_paginator::class,  //自定义分页驱动
    'user_increase_stat'        => UserIncreaseStatService::class,
];
