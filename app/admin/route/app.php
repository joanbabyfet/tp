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
    Route::post('login', 'Admin/login');
    Route::post('edit_pwd', 'Admin/edit_pwd');
    Route::post('upload', 'Common/upload');
    Route::get('demo', 'Test/demo');
    Route::get('captcha', 'Common/captcha');

    Route::group('article', function () {
        Route::get('/', 'Article/index');
        Route::get('detail', 'Article/detail');
        Route::post('delete', 'Article/delete');
        Route::post('add', 'Article/add');
        Route::post('edit', 'Article/edit');
        Route::post('enable', 'Article/enable');
        Route::post('disable', 'Article/disable');
    })->middleware(['auth']);
})->middleware(Throttle::class, [
    'visit_rate' => '60/m',
    'key' => '__CONTROLLER__/__ACTION__/__IP__',
]);
