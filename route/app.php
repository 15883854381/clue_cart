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

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::get('hello/:name', 'index/hello');
Route::post('upClue', 'Clue/upNewCartClue');
Route::post('orderSelect', 'Order/orderSelect');// 查询订单 当前用户的 点订单数据

Route::post('CallingPhone', 'Phone/CallingPhone');// 查询订单 当前用户的 点订单数据
Route::any('notify/:name', 'Order/notify');// 查询订单 当前用户的 点订单数据
Route::post('NotifyUrl/:outTradeNo', 'Phone/NotifyUrl');// 查询订单 当前用户的 点订单数据