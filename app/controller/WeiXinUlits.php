<?php

namespace app\controller;

use think\facade\Db;
use think\facade\Log;

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
}