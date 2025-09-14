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

    Route::group('adset', function () {
        Route::get('/', 'AdSet/index');
        Route::get('detail', 'AdSet/detail');
    });

    Route::group('ad', function () {
        Route::get('/', 'Ad/index');
        Route::get('detail', 'Ad/detail');
    });
})->middleware(Throttle::class, [
    'visit_rate' => '60/m',
    'key' => '__CONTROLLER__/__ACTION__/__IP__',
]);
