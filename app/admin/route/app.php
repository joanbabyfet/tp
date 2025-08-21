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
    Route::get('/', 'index/index')->middleware(['auth', 'lang', 'country_filter']);
    Route::get('login', 'index/login');
    Route::get('demo', 'test/demo');
    Route::post('send_verify_code', 'common/send_verify_code');
    Route::post('upload', 'upload/upload');
})->middleware(Throttle::class, [
    'visit_rate' => '60/m',
    'key' => '__CONTROLLER__/__ACTION__/__IP__',
]);

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');
