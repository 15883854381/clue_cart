<?php

namespace app\model;

use think\facade\Db;
use think\facade\Log;
use think\Model;

class Clue extends Model
{

    // 可以查看 手机号码的 订单详情
    public function CluePhone($id, $type)
    {
        $dataB = $type == 1 ? '`clue`' : 'clue_old';

        $sql = "SELECT a.clue_id,a.flag,sales,Tosell,CONCAT(user_name,IF(sex = 1 ,'先生','女士')) as user_name , IF(sex = 1 ,'男','女') as sex ,
                 a.phone_number as Cluephone_number,b.name as cartName,
                 CONCAT(c.`name`,'.',e.`name`) AS provinceCity,PhoneBelongingplace,
                 ROUND(100 / sales * Tosell) as progress,
                 (UNIX_TIMESTAMP(createtime)*1000) as createtime,
                 (CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END) as Price,
                 IF(type=4,nickname,companyName) as nclueName ,h.total as upClueNum,h.allTotal,CEIL(((h.total/h.allTotal)*100)) as percentage 
                 FROM ${dataB} a 
                 LEFT JOIN t_car_brand b ON a.CartBrandID = b.id
                 LEFT JOIN t_province c ON  a.provinceID = c.id
                 LEFT JOIN t_city e ON  a.cityID = e.id
								 LEFT JOIN (SELECT g.openid, COUNT(CASE WHEN g.flag = 1 THEN 1 END) as total,COUNT(g.openid) as allTotal  FROM clue g GROUP BY openid) h ON h.openid = a.openid
                 left JOIN user f ON a.openid = f.openid  WHERE clue_id ='${id}'";

        return Db::query($sql);

    }

    // 不可以查看 手机号码的 订单详情
    public function ClueNotPhone($id, $type)
    {
        $dataB = $type == 1 ? '`clue`' : 'clue_old';
        $sql = "SELECT a.clue_id,a.flag,
                CONCAT(user_name,IF(sex = 1 ,'先生','女士')) as user_name ,
                CONCAT_WS('*********',substring( a.phone_number, 1, 3 ),substring( a.phone_number, 12, 4 )) AS Cluephone_number,
                PhoneBelongingplace,sales,Tosell,
                CONCAT(b.`name`,'.',c.`name`) AS provinceCity,
                ROUND((100 / sales) * Tosell) as progress,d.`name` as brandname ,releaseNum,
                upClueNum,IF(type=4,nickname,companyName) as clueName,h.total as upClueNum,h.allTotal,CEIL(((h.total/h.allTotal)*100)) as percentage, 
                (CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END) as Price
                FROM ${dataB} a 
                LEFT JOIN t_province b ON a.provinceID = b.id  	
                LEFT JOIN t_city c ON a.provinceID = c.id  
                Left JOIN t_car_brand d ON a.CartBrandID = d.id
                LEFT JOIN (SELECT g.openid, COUNT(CASE WHEN g.flag = 1 THEN 1 END) as total,COUNT(g.openid) as allTotal  FROM clue g GROUP BY openid) h ON h.openid = a.openid
                LEFT JOIN `user` e ON a.openid =  	e.openid WHERE a.clue_id ='${id}'";
        return Db::query($sql);
    }


}