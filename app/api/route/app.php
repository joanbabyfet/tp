<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;
use think\middleware\Throttle;

Route::group(function() {
    Route::get('/', 'Index/index');
    Route::post('upload', 'Common/upload');
    Route::get('demo', 'Test/demo');
    Route::post('login', 'Index/login');

    Route::group('article', function () {
        Route::get('/', 'Article/index');
        Route::get('detail', 'Article/detail');
    });
})->middleware(Throttle::class, [
    'visit_rate' => '60/m',
    'key' => '__CONTROLLER__/__ACTION__/__IP__',
]);
