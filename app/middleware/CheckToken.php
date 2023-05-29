<?php

declare(strict_types=1);

namespace app\middleware;

// 数据库
use Firebase\JWT\JWT;
use  Firebase\JWT\key;

class CheckToken
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {

        //  不需要效验的控制器
        $exceptController = ['/User/index',
            '/User/getcode',
            '/Ulits/sendcode',
            '/Clue/getClueList',
            '/Clue/getClueDetail',
            '/Clue/getClueCount',
            '/Payment/notify',
            '/notify',
            '/Order/notify',// 付款成功回调
            '/AdminOrder/verify_refund',// 退款回调
            '/Phone/NotifyUrl', // 录音回调
            '/Admincustomer/Usercustomer',// 客服分配
            '/WXMenu/sendImage', // 向用户发送图文消息
            '/public/index.php', // 向用户发送图文消息
            '/Success/SelectSucessCase', // 成交案例
            '/OldCart/SelectCart',// 二手车的线索数据
            '/Ulits/city',
            '/Ulits/CarBrand',
            '/Clue/SearchClueBuyNUm'
        ];
        $controller = $request->controller(); // controller
        $action = $request->action(); // action
        $url = '/' . $controller . '/' . $action;

        if (in_array($url, $exceptController)) {
            return $next($request);
        }
        // 获取token
        $token = $request->header('token');
        // return $token;
        // 验证是否存在token
        if (empty($token)) {
            return error(305, '请登录后访问', null);
        } else {
            try {
                JWT::decode($token, new Key(md5('admin'), 'HS256'));
            } catch (\Exception $e) {
                return error(305, '登录过期，请重新登录', null);
            }
        }
        return $next($request);
    }


    // public function end(\think\Response $response){
    //     return parse_name(request()->controller(true));

    // }
}