<?php

namespace app\controller;

use think\cache\driver\Redis;
use think\facade\Config;
use think\facade\Log;
use WpOrg\Requests\Requests as http;

// 不继承 BaseContrpller
class UlitsThree
{
    /**
     * @param $item 用户信息
     * @param $count 品牌数量
     * @param $date 日期
     * @param $brand // 数据
     * @return string
     */
    public function sendWeiXinTempleat_notConter($item, $count, $date, $data)
    {
        $access_token = self::GetAccess_token_notConter();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;

        $weixin = Config::get('WeixinConfig.Weixin');
        // 汽车共享联盟
        $updata = [
            'touser' => $item['openid'],
            'template_id' => '-Jv0Adc4A5cyiCNq0fPmJb8d_qeY_sYUlc4SM_T_-xU',
            'appid' => 'wxf02c02843479d12a',
            "url" => $weixin['Cline_url'],
            "data" => [
                "thing1" => ["value" => $item['nickname']],
                "thing2" => ["value" => "【" . $data . "】"],
                "thing3" => ["value" => "新出线索【${count}】条供你挑选"],
                "time4" => ["value" => $date],
            ]
        ];
        // 汽车共享助手
//        $sex = $data['sex'] == 1 ? '先生' : '女士';
//        $updata = [
//            'touser' => $item['openid'],
//            'template_id' => $weixin['template_id'],
//            'appid' => $weixin['appid'],
//            "url" => $weixin['Cline_url'],
//            "data" => [
//                "first" => '最新线索通知',
//                "name" => ["value" =>$data['user_name'] . $sex],
//                "sex" => ["value" => $data['sex'] == 1 ? '男' : '女'],
//                "tel" => ["value" => substr_replace($data['phone_number'], '*', 3, 4)],
//                "remark" => ["value" => $data['user_name'] . "先生最近有购车意向，请留意此线索。发布于：" . $date],
//            ]
//        ];
        $code_res = http::post($url, [], json_encode($updata));

        return $code_res->body;
    }

    public function GetAccess_token_notConter()
    {
        $redis = new Redis(Config::get('cache.stores.redis'));
        $access_token = $redis->get('access_token');
        if ($access_token) {
            return $access_token;
        }

        $Weixin = \think\facade\Config::get('WeixinConfig.Weixin');
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $Weixin['appid'] . '&secret=' . $Weixin['appsecret'];
        $res = http::get($url);
        Log::info($res);
        $data = json_decode($res->body, true);
        if (isset($data['errcode'])) {
            Log::info('获取Access_token出错==={errcode}', ['errcode' => json_encode($data)]);
            return false;
        }
        Log::info('这个是access_token====' . $data['access_token']);
        $redis->set('access_token', $data['access_token'], 7000);
        return $data['access_token'];
    }

}