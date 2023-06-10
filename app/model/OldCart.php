<?php

namespace app\model;

use think\facade\Db;
use think\facade\Log;
use think\facade\Request;
use think\Model;

class OldCart extends Model
{
    protected $table = 'clue_old';

    // 查询手机号是否存在
    function searchPhone($phone)
    {
        return $this->where('phone_number', $phone)->find();
    }

    // 插入标签
    function TagsMap($clue_id, $userTags)
    {
        foreach ($userTags as $item) {
            $upItem = [
                'clue_id' => $clue_id,
                'tags_id' => $item['id'],
            ];
            Db::table('tagsmap')->save($upItem);
        }

    }

    // 获取二手车的线索
    function SelectOldClue($post)
    {

        $pageNum = $post['PageNum'] ?? 1;
        $pageSize = $post['pageSize'] ?? 10;
        $pageCount = (($pageNum - 1) * $pageSize);
        $where = " flag = 1 ";
        $countWhere = ' flag = 1';
        if (isset($post['provinceID'])) {
            if ($post['provinceID'] != 0) {
                $where .= ' and a.provinceID =' . $post['provinceID'];
                $countWhere .= ' and provinceID =' . $post['provinceID'];
            }
        }
        // 是否购买完毕
        if (isset($post['buyNum'])) {
            if ($post['buyNum'] == 1) {
                $countWhere = $where .= ' and  sales > Tosell ';
            } elseif ($post['buyNum'] == 2) {
                $countWhere = $where .= ' and sales <= Tosell';
            }
        }

        $sql = "SELECT a.clue_id,sales,a.cart_type,Tosell,CONCAT(user_name,IF(sex = 1 ,'先生','女士')) as user_name , IF(sex = 1 ,'男','女') as sex ,
                                CONCAT_WS('*********',substring(a.phone_number, 1, 3),
                                substring(a.phone_number, 12, 4)) as Cluephone_number,b.name as cartName,
                                CONCAT(c.`name`,'.',e.`name`) AS provinceCity,
                                ROUND(100 / sales * Tosell) as progress,
                                (UNIX_TIMESTAMP(createtime)*1000) as createtime,
                                (CASE Tosell WHEN 0 THEN unitPrice_1  WHEN 1 THEN unitPrice_2 ELSE unitPrice_3 END) as Price,
                                IFNULL(notes_name,nickname) as nclueName,h.total as upClueNum,h.allTotal,CEIL(((h.total/h.allTotal)*100)) as percentage 
																FROM clue_old a 
                                LEFT JOIN t_car_brand b ON a.CartBrandID = b.id
                                LEFT JOIN t_province c ON  a.provinceID = c.id
                                LEFT JOIN t_city e ON  a.cityID = e.id
																LEFT JOIN (SELECT g.openid, COUNT(CASE WHEN g.flag = 1 THEN 1 END) as total,COUNT(g.openid) as allTotal  FROM clue_old g GROUP BY openid) h ON h.openid = a.openid
                                left JOIN user f ON a.openid = f.openid where $where ORDER BY createtime DESC  LIMIT $pageCount,$pageSize ";
        $res = Db::query($sql);
        $count = $this->where($countWhere)->count();


        return ['data' => $res, 'count' => $count];
    }


    // 更具ID 查询线索 标签
    function SelectTages($id)
    {
        $sql = "SELECT clue_id,tagName FROM tagsmap a LEFT JOIN tags b ON a.tags_id = b.id WHERE clue_id in ('${id}')";
        Log::info($sql);
        return Db::query($sql);
    }


}