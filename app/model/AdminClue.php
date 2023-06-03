<?php

namespace app\model;

use think\facade\Db;
use think\Model;

class AdminClue extends Model
{
    protected $table = 'clue_old';

    function outbound_clue()
    {
        $token = decodeToken();

        // 小于三则表示 是 管理员得到权限
        if ($token->note->authority < 3) {
            $sql = "SELECT a.clue_id,user_name,sex,a.phone_number,createtime,PhoneBelongingplace,a.flag,sales,Tosell,unitPrice_1,unitPrice_2,unitPrice_3,cart_type,nickname,
                    c.province,c.city,f.username
                    FROM clue a 
                    LEFT JOIN `user` b ON a.openid = b.openid
                    LEFT JOIN (SELECT t_city.id,t_province.name as province,t_city.name as city  FROM t_province LEFT JOIN t_city ON t_province.id = t_city.province_id) as c ON c.id = a.cityID
                    LEFT JOIN admin_customer e ON e.clue_id = a.clue_id 
                    LEFT JOIN admin f ON f.id = e.admin_id";
        } else if ($token->note->authority == 4) {
            $sql = "SELECT a.clue_id,user_name,sex,a.phone_number,createtime,PhoneBelongingplace,a.flag,sales,Tosell,unitPrice_1,unitPrice_2,unitPrice_3,cart_type,nickname,
                    c.province,c.city
                    FROM clue a 
                    LEFT JOIN `user` b ON a.openid = b.openid
                    LEFT JOIN (SELECT t_city.id,t_province.name as province,t_city.name as city  FROM t_province LEFT JOIN t_city ON t_province.id = t_city.province_id) as c ON c.id = a.cityID
                    LEFT JOIN admin_customer e ON e.clue_id = a.clue_id 
                    WHERE e.admin_id =".$token->note->id;
        } else {
            return error(304, '没有访问权限', null);
        }

        return Db::query($sql);


    }


}