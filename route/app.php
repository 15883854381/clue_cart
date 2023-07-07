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
Route::any('notify/:name', 'Order/notify');// 支付成功反馈
Route::any('errorNotifyBath/:name', 'Clue/errorNotifyBath');// 支付成功反馈
Route::any('notifyBatch/:name', 'Clue/notifyBatch');// 支付成功反馈
Route::post('NotifyUrl/:outTradeNo', 'Phone/NotifyUrl');// 查询订单 当前用户的 点订单数据

Route::post('/CluePhoneNotifyUrl/:clueId', 'AdminClue/Clue_Phone_NotifyUrl');// 外呼审核同话录音