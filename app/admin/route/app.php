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
    Route::get('/', 'Index/index')->middleware(['auth', 'lang', 'country_filter']);
    Route::get('login', 'Index/login');
    Route::get('demo', 'Test/demo');
    Route::post('send_verify_code', 'Common/send_verify_code');
    Route::post('upload', 'Upload/upload');
    Route::get('adset', 'AdSet/index');
    Route::get('adset_detail', 'AdSet/detail');
    Route::post('adset_delete', 'AdSet/delete');
    Route::post('adset_add', 'AdSet/add');
    Route::post('adset_edit', 'AdSet/edit');
    Route::get('ad', 'Ad/index');
    Route::get('ad_detail', 'Ad/detail');
    Route::post('ad_delete', 'Ad/delete');
    Route::post('ad_add', 'Ad/add');
    Route::post('ad_edit', 'Ad/edit');
})->middleware(Throttle::class, [
    'visit_rate' => '60/m',
    'key' => '__CONTROLLER__/__ACTION__/__IP__',
]);

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');
