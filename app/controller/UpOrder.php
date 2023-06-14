<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Log;
use think\facade\Request;

class UpOrder extends BaseController
{

    // 获取上传者本人的订单
    public function getUpOrder()
    {
        $token = decodeToken();

        $post = Request::post();
        $pageData = pageData($post); // 分页的封装

        $sql = "SELECT
                    a.clue_id,
                    CONCAT(
                        user_name,
                    IF
                    ( sex = 1, '先生', '女士' )) AS user_name,
                    createtime AS creat_time,
                    ( unitPrice_1 + unitPrice_2 + unitPrice_3 ) AS price,
                    b.`name` AS brand,
                    c.`name` AS province,
                    e.`name` AS city,
                    flag,
                    cart_type
                FROM 
                (
                SELECT user_name,openid,flag,cart_type,cityID,unitPrice_1,unitPrice_2,unitPrice_3, createtime,clue_id, sex, provinceID, CartBrandID FROM clue 
                UNION 
                SELECT user_name,openid,flag,cart_type,cityID,unitPrice_1,unitPrice_2,unitPrice_3, createtime,clue_id, sex, provinceID, CartBrandID FROM clue_old 
                )	a
                LEFT JOIN t_car_brand b ON a.CartBrandID = b.id
                LEFT JOIN t_province c ON a.provinceID = c.id
                LEFT JOIN t_city e ON a.cityID = e.id
                WHERE a.openid='$token->id' AND  flag != 3 ORDER BY createtime DESC  LIMIT ${pageData['pageCount']} ,${pageData['pageSize']}";
        $res = DB::query($sql);
        if (!$res) {
            return error('304', '你还没有上传线索,快去上传吧', null);
        }
        $where = " openid='$token->id' AND  flag != 3";
        $totalSql = "SELECT SUM(total) as total FROM  (SELECT COUNT(openid) as total FROM clue WHERE $where UNION  SELECT COUNT(openid) as total FROM clue_old WHERE $where ) as tb";
        $total = DB::query($totalSql);

        return success(200, '查询成功', ['data' => $res, 'pageCount' => $total[0]['total']]);
    }
}