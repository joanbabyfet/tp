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

/**
 * 示例 Route::post('/', '控制器/方法')
 */

Route::group(function() {
    Route::get('/', 'Index/index')->middleware(['lang', 'country_filter']);
    Route::get('login', 'Index/login');
    Route::get('demo', 'Test/demo');
    Route::post('send_verify_code', 'Common/send_verify_code');
    Route::post('upload', 'Common/upload');

    Route::group('adset', function () {
        Route::get('/', 'AdSet/index');
        Route::get('detail', 'AdSet/detail');
        Route::post('delete', 'AdSet/delete');
        Route::post('add', 'AdSet/add');
        Route::post('edit', 'AdSet/edit');
        Route::post('enable', 'AdSet/enable');
        Route::post('disable', 'AdSet/disable');
    });

    Route::group('ad', function () {
        Route::get('/', 'Ad/index');
        Route::get('detail', 'Ad/detail');
        Route::post('delete', 'Ad/delete');
        Route::post('add', 'Ad/add');
        Route::post('edit', 'Ad/edit');
        Route::post('enable', 'Ad/enable');
        Route::post('disable', 'Ad/disable');
    });
})->middleware(Throttle::class, [
    'visit_rate' => '60/m',
    'key' => '__CONTROLLER__/__ACTION__/__IP__',
]);

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');
