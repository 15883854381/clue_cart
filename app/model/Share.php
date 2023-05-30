<?php

namespace app\model;

use think\facade\Db;
use think\Log;
use think\Model;

class Share extends Model
{
    protected $table = 'user_map';

    // 获取单个用户 自己分享成功的下级
    function getUserMapSingle()
    {
        $token = decodeToken();
        $sql = "SELECT nickname,headimgurl,x_openid as userid,
       (SELECT COUNT(id) as total FROM clue WHERE openid  = a.x_openid AND flag = 1) AS total 
        from `user_map` a 
        LEFT JOIN `user` b ON a.x_openid = b.openid  WHERE z_openid='$token->id'";
        \think\facade\Log::info($sql);
        return Db::query($sql);

    }

    // 获取单个用户订单列表
    function getUserDetail($openid)
    {
        $sql = "SELECT
	a.clue_id,
	a.cart_type,
	CONCAT(
		user_name,
	IF
	( sex = 1, '先生', '女士' )) AS user_name,
	CONCAT_WS(
		'*********',
		substring( a.phone_number, 1, 3 ),
	substring( a.phone_number, 12, 4 )) AS Cluephone_number,
	PhoneBelongingplace,
	CONCAT( b.`name`, '.', c.`name` ) AS provinceCity,
	d.`name` AS brandname,
	( CASE Tosell WHEN 0 THEN unitPrice_1 WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END ) AS Price 
FROM
	(
	SELECT
		user_name,
		Tosell,
		phone_number,
		flag,
		unitPrice_1,
		unitPrice_2,
		unitPrice_3,
		createtime,
		cart_type,
		clue_id,
		PhoneBelongingplace,
		sex,
		openid,
		provinceID,
		CartBrandID 
	FROM
		clue UNION
	SELECT
		user_name,
		Tosell,
		phone_number,
		flag,
		unitPrice_1,
		unitPrice_2,
		unitPrice_3,
		createtime,
		cart_type,
		clue_id,
		PhoneBelongingplace,
		sex,
		openid,
		provinceID,
		CartBrandID 
	FROM
		clue_old 
	) a
	LEFT JOIN t_province b ON a.provinceID = b.id
	LEFT JOIN t_city c ON a.provinceID = c.id
	LEFT JOIN t_car_brand d ON a.CartBrandID = d.id
WHERE a.openid='$openid' AND flag = 1";
        \think\facade\Log::info($sql);
        return Db::query($sql);

    }


}