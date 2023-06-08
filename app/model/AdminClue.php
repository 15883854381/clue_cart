<?php

namespace app\model;

use think\facade\Db;
use think\facade\Request;
use think\Log;
use think\Model;

class AdminClue extends Model
{
    protected $table = 'clue_old';

    function outbound_clue()
    {
        $post = Request::instance()->post();

        $pageSize = $post['pageSize'] ?? 10;
        $pageNum = $post['pageNumber'] ?? 1;

        $pageCount = ($pageNum - 1) * $pageSize;

        $token = decodeToken();
        // 小于三则表示 是 管理员得到权限
        if ($token->note->authority < 3) {
            $sql = "SELECT a.clue_id,a.cart_type,user_name,sex,a.CartBrandID,a.provinceID,a.cityID,a.phone_number,createtime,PhoneBelongingplace,a.flag,sales,Tosell,unitPrice_1,unitPrice_2,unitPrice_3,cart_type,nickname,
                    c.province,c.city,f.username,g.name as CartBrand
                    FROM (SELECT * FROM clue union SELECT * FROM clue_old)  a 
                    LEFT JOIN `user` b ON a.openid = b.openid
                    LEFT JOIN (SELECT t_city.id,t_province.name as province,t_city.name as city  FROM t_province LEFT JOIN t_city ON t_province.id = t_city.province_id) as c ON c.id = a.cityID
                    LEFT JOIN admin_customer e ON e.clue_id = a.clue_id 
                    LEFT JOIN admin f ON f.id = e.admin_id
                    LEFT JOIN t_car_brand g ON a.CartBrandID = g.id ORDER BY  createtime DESC LIMIT $pageCount , $pageSize ";
            $CountSql = "SELECT SUM(c.total) as total FROM (SELECT COUNT(clue_id) as total FROM clue UNION SELECT COUNT(clue_id) as total FROM clue_old) c";
        } else if ($token->note->authority == 4) {
            $id = $token->note->id;
            $sql = "SELECT a.clue_id,a.cart_type,user_name,sex,a.CartBrandID,a.provinceID,a.cityID,a.phone_number,createtime,PhoneBelongingplace,a.flag,sales,Tosell,unitPrice_1,unitPrice_2,unitPrice_3,cart_type,nickname,
                    c.province,c.city,g.name as CartBrand
                    FROM (SELECT * FROM clue union SELECT * FROM clue_old)  a 
                    LEFT JOIN `user` b ON a.openid = b.openid
                    LEFT JOIN (SELECT t_city.id,t_province.name as province,t_city.name as city  FROM t_province LEFT JOIN t_city ON t_province.id = t_city.province_id) as c ON c.id = a.cityID
                    LEFT JOIN admin_customer e ON e.clue_id = a.clue_id 
                    LEFT JOIN t_car_brand g ON a.CartBrandID = g.id
                    WHERE e.admin_id = '$id' ORDER BY  createtime DESC LIMIT $pageCount , $pageSize";
            $CountSql = "SELECT COUNT(id) as total FROM admin_customer WHERE admin_id = '$id'";

        } else {
            return error(304, '没有访问权限', null);
        }


        $res = Db::query($sql);
        $total = Db::query($CountSql);
        return ['data' => $res, 'total' => (int)$total[0]['total']];


    }


}