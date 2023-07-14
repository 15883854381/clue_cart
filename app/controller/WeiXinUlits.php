<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Log;

use WpOrg\Requests\Requests as http;

class WeiXinUlits extends BaseController
{
    // 定时发送批量发送模板消息
    public function sendTemplate()
    {
        $open_data = self::UserOpenid();
        if (!$open_data) {
            return error(304, '数据请求失败', null);
        }
        foreach ($open_data['data']['openid'] as $item) {
            $this->UserCity($item);
        }
    }

    // openid
    function UserCity($openid)
    {
        $ulit = new Templatelibrary($this->app);
        $sql = "SELECT city_id,province_id,area FROM `user` WHERE openid = '${openid}' ";
        $user = Db::query($sql);
        $wheres = " WHERE  name is not null  ";
        if (!empty($user[0]['province_id'])) {
            $province_id = (string)$user[0]['province_id'];
            $wheres .= "  AND provinceID = ${province_id}  ";
        }
        $sql = "SELECT CONCAT_WS('****',substring(phone_number, 1, 3),substring(phone_number, 8, 4)) as phone_number,user_name,sex,name as car,openid,city,province,a.clue_id,a.cart_type
                FROM clue a
                LEFT JOIN t_car_brand ON a.CartBrandID = t_car_brand.id 
                LEFT JOIN (SELECT t_city.id,t_province.name as province,t_city.name as city  FROM t_province LEFT JOIN t_city ON t_province.id = t_city.province_id) as c ON c.id = a.cityID
                $wheres  ORDER BY RAND() LIMIT 1 ";
        $res = Db::query($sql);
        $res[0]['openid'] = $openid;
        if(mt_rand(1, 10)>5){
            $ulit->template_one($res[0]);
        }else{
            $ulit->template_two($res[0]);
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