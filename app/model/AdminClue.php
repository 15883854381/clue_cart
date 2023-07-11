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

        // 分页 === 开始
        $pageSize = $post['pageSize'] ?? 10;
        $pageNum = $post['pageNumber'] ?? 1;
        $pageCount = ($pageNum - 1) * $pageSize;
        // 分页 === 结束

        $where = $this->SqlWhere($post);

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
                    LEFT JOIN t_car_brand g ON a.CartBrandID = g.id WHERE $where ORDER BY  createtime DESC LIMIT $pageCount , $pageSize ";
            $CountSql = " SELECT COUNT(a.clue_id) as total FROM  
                        (SELECT * FROM clue UNION  SELECT * FROM clue_old) as a
                        WHERE $where;";
        } else if ($token->note->authority == 4) {
            $id = $token->note->id;
            $where .= " AND admin_id = '$id'";
            $sql = "SELECT a.clue_id,a.cart_type,user_name,sex,a.CartBrandID,a.provinceID,a.cityID,a.phone_number,createtime,PhoneBelongingplace,a.flag,sales,Tosell,unitPrice_1,unitPrice_2,unitPrice_3,cart_type,nickname,
                    c.province,c.city,g.name as CartBrand
                    FROM (SELECT * FROM clue union SELECT * FROM clue_old)  a 
                    LEFT JOIN `user` b ON a.openid = b.openid
                    LEFT JOIN (SELECT t_city.id,t_province.name as province,t_city.name as city  FROM t_province LEFT JOIN t_city ON t_province.id = t_city.province_id) as c ON c.id = a.cityID
                    LEFT JOIN admin_customer e ON e.clue_id = a.clue_id 
                    LEFT JOIN t_car_brand g ON a.CartBrandID = g.id
                    WHERE $where  ORDER BY  createtime DESC LIMIT $pageCount , $pageSize";
            $CountSql = "SELECT COUNT(a.clue_id) as total FROM admin_customer b LEFT JOIN (SELECT * FROM clue UNION  SELECT * FROM clue_old) as a
                        ON a.clue_id = b.clue_id WHERE $where";

        } else {
            return error(304, '没有访问权限', null);
        }
        trace($sql);


        $res = Db::query($sql);
        $total = Db::query($CountSql);
        return ['data' => $res, 'total' => (int)$total[0]['total']];
    }

    // sql WHere 条件
    public function SqlWhere($post)
    {
        $where = " 1 ";
        if (!empty($post['phone_number'])) {
            $where .= " AND a.phone_number = '${post['phone_number']}'";
        }

        if (isset($post['flag'])) {
            if ($post['flag'] === "0" || !empty($post['flag'])) {
                $where .= " AND a.flag = '${post['flag']}'";
            }
        }

        if (!empty($post['buyCar'])) {
            $pridrive = "";
            $city = "";
            foreach ($post['buyCar'] as $item) {
                trace($item);
                trace(count($item));
                if (count($item) > 1) {
                    $city .= "'$item[1]'";
                } else {
                    $pridrive .= "'$item[0]'";
                }
            }
            $p = "";
            $c = "";

            if (!empty($pridrive)) {

                $p = "a.provinceID in ($pridrive)";
            }
            if (!empty($city)) {
                $strOrAnd = empty($pridrive) ? ' ' : ' OR ';
                $c .= " $strOrAnd  a.cityID in ($city)";
            }

            $where .= "AND ($p$c) ";
        }
        if (!empty($post['carBrant'])) {
            $strBran = implode("','", $post['carBrant']);
            $where .= " AND a.CartBrandID in ('$strBran')";
        }
        return $where;
    }


}