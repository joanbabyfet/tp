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
    Route::post('login', 'User/login');
    Route::post('register', 'User/register');
    Route::post('userinfo', 'User/userinfo')->middleware(['auth']);
    Route::post('edit_pwd', 'User/edit_pwd')->middleware(['auth']);
    //Route::get('captcha/:id', '\think\captcha\CaptchaController@index');
    Route::get('captcha', 'Common/captcha');
    Route::post('send_sms_code', 'Common/send_sms_code');
    Route::post('check_sms_code', 'Common/check_sms_code');
    Route::post('send_email_code', 'Common/send_email_code');
    Route::post('check_email_code', 'Common/check_email_code');

    Route::group('article', function () {
        Route::get('/', 'Article/index');
        Route::get('detail', 'Article/detail');
    });
})->middleware(Throttle::class, [
    'visit_rate' => '60/m',
    'key' => '__CONTROLLER__/__ACTION__/__IP__',
]);
