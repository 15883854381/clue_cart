<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Request;

class AdminClue extends BaseController
{

    // 查询线索
    public function Clue_list()
    {
        $post = Request::instance()->post();
        $pageSize = @$post['pageSize'] ?? 10;
        $pageNumber = @$post['pageNumber'] ?? 1;
        $pageCount = ($pageNumber - 1) * $pageSize;
        $where = '1';
        if (isset($post['flag'])) {
            $where .= " AND flag = " . $post['flag'];
        }

        $sql = "SELECT a.clue_id,a.cart_type,CONCAT(user_name,IF(sex = 1 ,'先生','女士')) as user_name ,a.phone_number,PhoneBelongingplace,createtime,sales,Tosell,flag,b.`name` as brand,
                unitPrice_1,unitPrice_2,unitPrice_3,f.nickname,
                CONCAT(c.`name`,'.',e.`name`) as address,
                (unitPrice_1 + unitPrice_2+unitPrice_3) as amount 
                FROM  (SELECT * FROM clue UNION SELECT * FROM clue_old) a 
                    LEFT JOIN `user` f ON a.openid = f.openid
                LEFT JOIN t_car_brand b ON a.CartBrandID = b.id
                LEFT JOIN t_province c ON a.provinceID = c.id
                LEFT JOIN t_city e ON a.cityID = e.id WHERE $where
                 ORDER BY createtime DESC LIMIT $pageCount,$pageSize ";

        $clue = new \app\model\Clue();
        $oldClue = new \app\model\OldCart();
        $newCartcount = $clue->count();
        $oldCartCount = $oldClue->count();

        $res = DB::query($sql);
        return success(200, '查询成功', ['count' => ($newCartcount + $oldCartCount), 'data' => $res]);

    }


    // 获取线索总数量
    function ClueCount()
    {
        $clue = new \app\model\Clue();
        $count = $clue->count();
        return success(200, '获取成功', ['count' => $count]);
    }

    // 修改线索状态
    public function EditClueFlag()
    {
        $post = Request::instance()->post();

        if (!isset($post['clue_id']) || !isset($post['type'])) {
            return error('304', '参数错误', null);
        }

        try {
            if (intval($post['flag']) > 3 || intval($post['flag']) < 0) {
                return error('304', '参数异常，不要乱搞', null);
            }
        } catch (\Exception $e) {
            return error('305', '参数异常，不要乱搞', null);
        }

        $clue = new \app\model\Clue();
        $oldclue = new \app\model\OldCart();
        $DataB = $post['type'] == 1 ? $clue : $oldclue;

        $res = $DataB->where('clue_id', $post['clue_id'])->find();
        $res->flag = $post['flag'];
        $des = $res->save();
        if (!$des) {
            return error('304', '修改失败', null);
        }

        return success(200, '修改成功', null);


    }

}