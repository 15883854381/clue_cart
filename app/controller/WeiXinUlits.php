<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Log;
use WpOrg\Requests\Requests as http;

class WeiXinUlits
{
    // 定时发送批量发送模板消息
    public function sendTemplate()
    {
        $date = date('Y年m月d');
        $sql = "SELECT  IFNULL(companyName,nickname) as nickname , openid FROM `user`"; // 用户信息
        // 品牌和
        $brand = Db::query('SELECT name as brandName, CartBrandID  FROM clue a  LEFT JOIN t_car_brand b ON a.CartBrandID = b.id WHERE name is not null  GROUP BY name,CartBrandID ORDER BY RAND() LIMIT 1');
        $userdata = Db::query($sql);
        if (!$brand) {
            return error(304, '没有数据', null);
        }
        $clue = new \app\model\Clue();
        $count = $clue->where('CartBrandID', $brand[0]['CartBrandID'])->count();
        $ulitsThree = new UlitsThree();

        $open_data = self::UserOpenid();
        if (!$open_data) {
            return error(304, '数据请求失败', null);
        }

        if ($open_data['total'] !== count($userdata)) {
            $updata = [];
            foreach ($open_data['data']['openid'] as $item) {
                foreach ($userdata as $it) {
                    if ($item == $it['openid']) {
                        $updata[] = $it;
                        continue 2;
                    }
                }
                $updata[] = ["nickname" => "先生/女士", "openid" => $item];
            }
        }

        foreach ($updata as $item) {
            try {
                $ulitsThree->sendWeiXinTempleat_notConter($item, $count, $date, $brand[0]['brandName']);
            } catch (\Exception $e) {
                continue;
            }
        }
    }



//    public function sendTemplate()
//    {
//        $date = date('Y年m月d');
//        $sql = "SELECT  IFNULL(companyName,nickname) as nickname , openid FROM `user`";
//        $userdata = Db::query($sql);// 用户信息
//        // 品牌和
//        $brandSql = 'SELECT user_name,sex,phone_number FROM clue WHERE flag = 1 ORDER BY RAND() LIMIT 1';
//        $brand = Db::query($brandSql);
//        if(!$brand){
//             return error(304,'没有数据',null);
//        }
//
//        $ulitsThree = new UlitsThree();
//
//        foreach ($userdata as $item) {
//            try {
//                $ulitsThree->sendWeiXinTempleat_notConter($item, 0, $date, $brand[0]);
//            } catch (\Exception $e) {
//                continue;
//            }
//
//        }
//    }


    // 发送营销短信
    public function pushInfo()
    {
        $url = 'https://smssh1.253.com/msg/v1/send/json';

        $startTime = date("Y-m-d", strtotime("-7 day"));
        $endStart = date("Y-m-d");
        $sql = "SELECT nickname,phone_number,area,
                (SELECT COUNT(id)  as total FROM clue where PhoneBelongingplace LIKE  concat('%', area, '%')) as total
                FROM `user` WHERE  enroll_time BETWEEN '$startTime 00:00:00' and  '$endStart 23:59:59'";

        $res = Db::query($sql);
        if (!$res) {
            return false;
        }

        $testConfig = \think\facade\Config::get('WeixinConfig.Code');

        foreach ($res as $item) {
            $total = $item['total'] == 0 ? rand(1, 30) : $item['total'];
            try {
                $postArr = array(
                    'account' => $testConfig['Marke_account'],
                    'password' => $testConfig['Marke_password'],
                    'msg' => "亲爱的联盟会员客户【${item['nickname']}】，今日您有最新共享【${item['area']}】线索${total}条上线，请前往公众号查看详情。退订回复TD",
                    'phone' => $item['phone_number'],
                    'report' => true
                );

                http::post($url, ['Content-Type' => 'application/json;charset=utf-8'], json_encode($postArr));
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                continue;
            }


        }
    }

    // 通过接口获取 获取已关注的用户
    public function UserOpenid()
    {
        $ulit = new UlitsThree();
        $access_token = $ulit->GetAccess_token_notConter();
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=$access_token";
        $open_res = http::post($url);
        $open = json_decode($open_res->body, true);
        if (!isset($open['data'])) {
            return false;
        }
        return $open;
    }


}