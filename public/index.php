<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\App;

// [ 应用入口文件 ]

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$app_env = empty($_SERVER['APP_ENV']) ? '' : $_SERVER['APP_ENV']; //$_SERVER['APP_ENV']来自nginx自定义参数 test:测试 prod:生产(默认)
$http = (new App())->setEnvName($app_env)->http;

$response = $http->run();

$response->send();

$http->end($response);
