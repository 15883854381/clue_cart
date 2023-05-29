<?php

namespace app\controller;

use think\facade\Db;
use think\facade\Log;
use WpOrg\Requests\Requests as http;

class WeiXinUlits
{
    // 定时发送批量发送模板消息
    public function sendTemplate()
    {
        $date = date('Y年m月d');
        $sql = "SELECT  IFNULL(companyName,nickname) as nickname , openid FROM `user`";
        $brand = Db::query('SELECT name as brandName, CartBrandID  FROM clue a  LEFT JOIN t_car_brand b ON a.CartBrandID = b.id WHERE name is not null  GROUP BY name,CartBrandID ORDER BY RAND() LIMIT 1');
        $userdata = Db::query($sql);
        Log::info($brand);
        $clue = new \app\model\Clue();
        $count = $clue->where('CartBrandID', $brand[0]['CartBrandID'])->count();
        $ulitsThree = new UlitsThree();
        foreach ($userdata as $item) {
            $ulitsThree->sendWeiXinTempleat_notConter($item, $count, $date, $brand[0]['brandName']);
        }
    }

    public function pushInfo()
    {
        $url = 'https://smssh1.253.com/msg/v1/send/json';

        $startTime = date("Y-m-d", strtotime("-7 day"));
        $endStart = date("Y-m-d");
        $sql = "SELECT nickname,phone_number,area,
                (SELECT COUNT(id)  as total FROM clue where PhoneBelongingplace LIKE  concat('%', area, '%')) as total
                FROM `user` WHERE  enroll_time >= '$startTime' and enroll_time <= '$endStart'";
        $res = Db::query($sql);

        if (!$res) {
            return false;
        }

        $testConfig = \think\facade\Config::get('WeixinConfig.Code');

        foreach ($res as $item) {
            $postArr = array(
                'account' => $testConfig['Marke_account'],
                'password' => $testConfig['Marke_password'],
                'msg' => "亲爱的联盟会员客户【${item['nickname']}】，今日您有最新共享【${item['area']}】线索${item['total']}条上线，请前往公众号查看详情。退订回复TD",
                'phone' => $item['phone_number'],
                'report' => true
            );
            $res = http::post($url, ['Content-Type' => 'application/json;charset=utf-8'], json_encode($postArr));
            Log::info($res->body);
        }
    }


}