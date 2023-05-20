<?php

namespace app\controller;

use app\BaseController;
use think\facade\Log;
use WpOrg\Requests\Requests as http;

class UlitsTwo extends BaseController
{
    /**
     *短信平台 检测手机号码 有效
     * @param $mobiles
     * @return false|mixed
     */
    public function batchUcheck($mobiles)
    {
        $Code = \think\facade\Config::get('WeixinConfig.Code');
        $params = [
            'appId' => $Code['appId'], // appId,登录万数平台查看
            'appKey' => $Code['appKey'], // appKey,登录万数平台查看
            'mobiles' => $mobiles, // 要检测的手机号，多个手机号码用英文半角逗号隔开
        ];
        $response = http::post('https://api.253.com/open/unn/batch-ucheck', [], $params);

        $data = json_decode($response->body, true);
        if (!isset($data['code']) or $data['code'] != '200000') {
            Log::error($data);
            return false;
        }
        // 手机状态
        if ($data['data'][0]['status'] != 1 && $data['data'][0]['status'] != 4) {
            return false;
        }
        return $data['data'][0];

    }

}